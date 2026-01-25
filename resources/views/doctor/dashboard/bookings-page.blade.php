<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?int $selectedBookingId = null;

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function selectBooking(int $bookingId): void
    {
        $this->selectedBookingId = $bookingId;
    }

    private function ensureDoctor(): int
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->redirectRoute('doctor.login');

            return 0;
        }

        return (int) $userId;
    }

    public function setStatus(int $bookingId, string $status): void
    {
        $doctorId = $this->ensureDoctor();
        if (! $doctorId) {
            return;
        }

        $this->selectedBookingId = $bookingId;

        $targetStatus = AppointmentStatus::tryFrom($status);
        if (! $targetStatus) {
            session()->flash('error', 'Status tidak valid.');

            return;
        }

        try {
            DB::transaction(function () use ($doctorId, $bookingId, $targetStatus) {
                $booking = Appointment::query()
                    ->where('doctor_id', $doctorId)
                    ->lockForUpdate()
                    ->findOrFail($bookingId);

                if (in_array($booking->status?->value, [AppointmentStatus::CANCELLED->value, AppointmentStatus::DONE->value], true)) {
                    throw new RuntimeException('Booking sudah selesai/dibatalkan.');
                }

                $payload = ['status' => $targetStatus];

                if ($targetStatus === AppointmentStatus::ONGOING && ! $booking->checked_in_at) {
                    $payload['checked_in_at'] = now();
                }

                if ($targetStatus === AppointmentStatus::DONE && ! $booking->service_ended_at) {
                    $payload['service_ended_at'] = now();
                }

                if ($targetStatus === AppointmentStatus::CANCELLED && ! $booking->no_show_at) {
                    $payload['no_show_at'] = now();
                }

                $booking->update($payload);
            });

            session()->flash('success', 'Status booking berhasil diperbarui.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', $e->getMessage() ?: 'Gagal memperbarui status.');
        }
    }

    public function visitAction(int $bookingId, string $action): void
    {
        $doctorId = $this->ensureDoctor();
        if (! $doctorId) {
            return;
        }

        $this->selectedBookingId = $bookingId;

        $allowed = ['check_in', 'call', 'start', 'finish', 'no_show'];
        if (! in_array($action, $allowed, true)) {
            session()->flash('error', 'Aksi tidak valid.');

            return;
        }

        try {
            DB::transaction(function () use ($doctorId, $bookingId, $action) {
                $booking = Appointment::query()
                    ->where('doctor_id', $doctorId)
                    ->lockForUpdate()
                    ->findOrFail($bookingId);

                if (in_array($booking->status?->value, [AppointmentStatus::CANCELLED->value, AppointmentStatus::DONE->value], true)) {
                    throw new RuntimeException('Booking sudah selesai/dibatalkan.');
                }

                $payload = [];
                $now = now();

                if ($action === 'check_in') {
                    $payload['checked_in_at'] = $booking->checked_in_at ?: $now;
                    if ($booking->status?->value === AppointmentStatus::CONFIRMED->value) {
                        $payload['status'] = AppointmentStatus::ONGOING;
                    }
                }

                if ($action === 'call') {
                    $payload['called_at'] = $booking->called_at ?: $now;
                }

                if ($action === 'start') {
                    $payload['service_started_at'] = $booking->service_started_at ?: $now;
                    $payload['status'] = AppointmentStatus::ONGOING;
                }

                if ($action === 'finish') {
                    $payload['service_ended_at'] = $booking->service_ended_at ?: $now;
                    $payload['status'] = AppointmentStatus::DONE;
                }

                if ($action === 'no_show') {
                    $payload['no_show_at'] = $booking->no_show_at ?: $now;
                    $payload['status'] = AppointmentStatus::CANCELLED;
                }

                if ($payload === []) {
                    return;
                }

                $booking->update($payload);
            });

            session()->flash('success', 'Status kunjungan berhasil diperbarui.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', $e->getMessage() ?: 'Gagal memperbarui status kunjungan.');
        }
    }

    public function with(): array
    {
        $doctorId = $this->ensureDoctor();

        $availableStatus = AppointmentStatus::asArray();

        $query = Appointment::query()
            ->with(['patient.user', 'service', 'room'])
            ->where('doctor_id', $doctorId)
            ->when($this->selectedStatus && $this->selectedStatus !== 'all', function ($q) {
                $q->where('status', $this->selectedStatus);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('scheduled_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('scheduled_date', '<=', $this->dateTo);
            })
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_start');

        $bookings = $query->paginate(10);

        $selectedBooking = null;
        if ($this->selectedBookingId) {
            $selectedBooking = Appointment::query()
                ->with(['patient.user', 'service', 'room'])
                ->where('doctor_id', $doctorId)
                ->find($this->selectedBookingId);
        }

        return [
            'availableStatus' => $availableStatus,
            'bookings' => $bookings,
            'selectedBooking' => $selectedBooking,
        ];
    }
}; ?>

<div class="p-6 rounded-xl">
    @if (session()->has('success'))
    <div class="mb-4 p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-4 p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
        {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-wrap gap-3 items-end justify-between">
        <div class="mb-2">
            <h2 class="text-xl font-semibold">Booking Saya</h2>
            <p class="text-sm text-gray-500">Kelola booking & status kunjungan pasien.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="input-form">
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" wire:model.live="dateFrom"
                    />
            </div>
            <div class="input-form">
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" wire:model.live="dateTo"
                    />
            </div>


            @component('components.form.select', [
            'label' => 'Status',
            'wireModel' => 'selectedStatus',
            'options' => $availableStatus,
            'default' => ['label' => 'Semua Status', 'value' => 'all'],
            ])
            @endcomponent
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-4" wire:loading.class="opacity-50 pointer-events-none">
        <div class="lg:col-span-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 sticky top-24 ">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm text-gray-500">Detail Pasien</div>
                        <div class="text-lg font-semibold">
                            {{ $selectedBooking?->patient?->user?->name ?? 'Pilih booking' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $selectedBooking?->code ? 'Booking: '.$selectedBooking->code : 'Klik salah satu booking di panel kanan.' }}
                        </div>
                    </div>

                    @if ($selectedBooking?->status)
                    <span class="inline-flex px-2 py-1 rounded-md text-xs bg-gray-100 text-gray-700">
                        {{ $selectedBooking->status?->getLabel() ?? $selectedBooking->status?->value }}
                    </span>
                    @endif
                </div>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">MRN</div>
                        <div class="col-span-2 font-medium">
                            {{ $selectedBooking?->patient?->medical_record_number ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">Email</div>
                        <div class="col-span-2 font-medium">{{ $selectedBooking?->patient?->user?->email ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">Telepon</div>
                        <div class="col-span-2 font-medium">
                            {{ $selectedBooking?->patient?->user?->phone ?? ($selectedBooking?->patient?->user?->hp ?? '-') }}
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">NIK</div>
                        <div class="col-span-2 font-medium">{{ $selectedBooking?->patient?->nik ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">Lahir</div>
                        <div class="col-span-2 font-medium">
                            {{ $selectedBooking?->patient?->birth_date ? $selectedBooking->patient->birth_date->format('d M Y') : '-' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-gray-500">Alamat</div>
                        <div class="col-span-2 font-medium">{{ $selectedBooking?->patient?->address ?? '-' }}</div>
                    </div>
                </div>

                <div class="mt-5 border-t pt-4">
                    <div class="text-sm font-semibold">Ringkasan Appointment</div>
                    <div class="mt-2 text-sm text-gray-700 space-y-1">
                        <div>Layanan: <span class="font-medium">{{ $selectedBooking?->service?->name ?? '-' }}</span>
                        </div>
                        <div>Ruangan: <span class="font-medium">{{ $selectedBooking?->room?->name ?? '-' }}</span></div>
                        <div>
                            Jadwal:
                            <span class="font-medium">
                                @php
                                $selectedSchedule = '-';
                                if ($selectedBooking?->scheduled_date && $selectedBooking?->scheduled_start) {
                                $selectedSchedule =
                                Carbon\Carbon::parse($selectedBooking->scheduled_date->toDateString().'
                                '.$selectedBooking->scheduled_start, 'Asia/Makassar')->format('d M Y H:i');
                                }
                                @endphp
                                {{ $selectedSchedule }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm text-gray-500">List Appointment & Aksi</div>
                    <div>{{ $bookings->links() }}</div>
                </div>

                <div class=" mt-3 ">
                    <table class="w-full  text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-3 pr-4">Kode</th>
                                <th class="py-3 pr-4">Pasien</th>
                                <th class="py-3 pr-4">Layanan</th>
                                <th class="py-3 pr-4">Jadwal</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                            @php
                            $isSelected = (int) ($this->selectedBookingId ?? 0) === (int) $booking->id;
                            @endphp
                            <tr class="border-b align-top cursor-pointer {{ $isSelected ? 'bg-primary/5' : '' }}"
                                wire:click="selectBooking({{ (int) $booking->id }})">
                                <td class="py-3 pr-4 font-medium">{{ $booking->code ?? '-' }}</td>
                                <td class="py-3 pr-4">{{ $booking->patient?->user?->name ?? '-' }}</td>
                                <td class="py-3 pr-4">{{ $booking->service?->name ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    @php
                                    $scheduledLabel = '-';
                                    if ($booking->scheduled_date && $booking->scheduled_start) {
                                    $scheduledLabel = Carbon\Carbon::parse($booking->scheduled_date->toDateString().'
                                    '.$booking->scheduled_start, 'Asia/Makassar')->format('d M Y H:i');
                                    }
                                    @endphp
                                    {{ $scheduledLabel }}
                                    <div class="text-xs text-gray-500">Ruangan: {{ $booking->room?->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 pr-4">
                                    @php
                                    $status = $booking->status;
                                    $label = $status?->getLabel() ?? ($status?->value ?? '-');
                                    $color = match ($status?->value) {
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'confirmed' => 'bg-blue-100 text-blue-700',
                                    'ongoing' => 'bg-yellow-100 text-yellow-800',
                                    'done' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                    };
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-1 rounded-md text-xs {{ $color }}">{{ $label }}</span>


                                </td>
                                <td class="py-3" wire:click.stop>
                                    <div class="flex items-center gap-2">
                                        @component('components.dropdown', [
                                        'align' => 'right',
                                        'width' => 'min-w-48',
                                        'useDownArrow' => true,
                                        'contentClasses' => 'bg-white border border-gray-200 rounded-md shadow-md',
                                        ])
                                        @slot('trigger')
                                        <span
                                            class="px-3 py-1 rounded-md text-xs bg-gray-100 hover:bg-gray-200">Aksi</span>
                                        @endslot

                                        @slot('content')
                                        <div class="py-1">
                                            <button type="button"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50"
                                                x-on:click="open = false"
                                                wire:click="visitAction({{ (int) $booking->id }}, 'check_in')">
                                                Check-in
                                            </button>
                                            <button type="button"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50"
                                                x-on:click="open = false"
                                                wire:click="visitAction({{ (int) $booking->id }}, 'call')">
                                                Panggil
                                            </button>
                                            <button type="button"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-yellow-50 text-yellow-800"
                                                x-on:click="open = false"
                                                wire:click="visitAction({{ (int) $booking->id }}, 'start')">
                                                Mulai Layanan
                                            </button>
                                            <button type="button"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-green-50 text-green-800"
                                                x-on:click="open = false"
                                                wire:click="visitAction({{ (int) $booking->id }}, 'finish')">
                                                Selesaikan
                                            </button>
                                            <button type="button"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-700"
                                                x-on:click="open = false"
                                                wire:click="visitAction({{ (int) $booking->id }}, 'no_show')">
                                                No-show
                                            </button>
                                        </div>
                                        @endslot
                                        @endcomponent
                                    </div>


                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500">Belum ada booking.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
