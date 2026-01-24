<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatusEnum;
use App\Models\Appointment;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?string $selectedBookingStatus = null;

    public ?string $selectedPaymentStatus = null;

    public ?int $rescheduleBookingId = null;

    public bool $isRescheduleOpen = false;

    public ?string $reschedule_date = null;

    public ?string $reschedule_time = null;

    #[\Livewire\Attributes\On('date-time-selected')]
    public function setRescheduleDateTime($data): void
    {
        $this->reschedule_date = is_array($data) ? ($data['date'] ?? null) : null;
        $this->reschedule_time = is_array($data) ? ($data['time'] ?? null) : null;
    }

    public function openReschedule(int $bookingId): void
    {
        $this->rescheduleBookingId = $bookingId;
        $this->isRescheduleOpen = true;
        $this->reset(['reschedule_date', 'reschedule_time']);
        $this->dispatch('reset-booking-calendar');
    }

    public function closeReschedule(): void
    {
        $this->isRescheduleOpen = false;
        $this->rescheduleBookingId = null;
        $this->reset(['reschedule_date', 'reschedule_time']);
        // $this->dispatch('reset-booking-calendar');
    }

    public function applyReschedule(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->validate([
            'rescheduleBookingId' => 'required|integer',
            'reschedule_date' => 'required|date_format:Y-m-d',
            'reschedule_time' => 'required|date_format:H:i',
        ], [
            'reschedule_date.required' => 'Tanggal wajib dipilih.',
            'reschedule_time.required' => 'Jam wajib dipilih.',
        ]);

        try {
            $tz = 'Asia/Makassar';
            $scheduledAt = Carbon::parse("{$this->reschedule_date} {$this->reschedule_time}", $tz);

            $service = new ReservationService;
            $result = $service->rescheduleBookingByUser(
                bookingId: (int) $this->rescheduleBookingId,
                userId: (int) $userId,
                newScheduledAt: $scheduledAt->toDateTimeString(),
            );

            if (($result['ok'] ?? false) === true) {
                session()->flash('success', 'Jadwal booking berhasil diubah.');
                $this->closeReschedule();

                return;
            }

            $message = (string) ($result['message'] ?? 'Gagal mengubah jadwal.');
            session()->flash('error', $message);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan saat menjadwal ulang.');
        }
    }

    public function cancelBooking(int $bookingId): void
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->redirectRoute('user.login');

            return;
        }

        try {
            $service = new ReservationService;
            $result = $service->cancelBookingByUser(
                bookingId: $bookingId,
                userId: (int) $userId,
            );

            if (($result['ok'] ?? false) === true) {
                session()->flash('success', 'Booking berhasil dibatalkan.');
                if ($this->rescheduleBookingId === $bookingId) {
                    $this->closeReschedule();
                }

                return;
            }

            $message = (string) ($result['message'] ?? 'Gagal membatalkan booking.');
            session()->flash('error', $message);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan saat membatalkan booking.');
        }
    }

    public function updatedSelectedBookingStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $patientId = Auth::user()->patient?->id ?? null;

        // if (!$patientId) {
        //     $this->redirectRoute('user.login');
        //     return [];
        // }

        $availableBookingStatus = AppointmentStatus::asArray();
        $availablePaymentStatus = PaymentStatusEnum::asArray();

        $bookings = Appointment::query()
            ->with(['service', 'doctor', 'room'])
            ->where('patient_id', $patientId)
            ->when($this->selectedBookingStatus && $this->selectedBookingStatus !== 'all', function ($query) {
                $query->where('status', $this->selectedBookingStatus);
            })
            ->when($this->selectedPaymentStatus && $this->selectedPaymentStatus !== 'all', function ($query) {
                $query->where('payment_status', $this->selectedPaymentStatus);
            })
            ->latest()
            ->paginate(10);

        $activeRescheduleBooking = null;
        if ($this->rescheduleBookingId) {
            $activeRescheduleBooking = Appointment::query()
                ->with(['service'])
                ->where('patient_id', $patientId)
                ->find($this->rescheduleBookingId);
        }

        return [
            'bookings' => $bookings,
            'availableBookingStatus' => $availableBookingStatus,
            'availablePaymentStatus' => $availablePaymentStatus,
            'activeRescheduleBooking' => $activeRescheduleBooking,
        ];
    }
}; ?>


<div class="p-6 rounded-xl ">
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

    <div class="flex">
        <div class="mb-6 flex-1">
            <h2 class="text-xl font-semibold">Riwayat Pesanan</h2>
            <p class="text-sm text-gray-500">Daftar pesanan yang pernah Anda buat.</p>
        </div>
        <div class="flex items-center gap-3">
            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedPaymentStatus',
            'options' => $availablePaymentStatus,
            'default' => ['label' => 'Semua Pembayaran', 'value' => 'all']
            ])

            @endcomponent

            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedBookingStatus',
            'options' => $availableBookingStatus,
            'default' => ['label' => 'Semua Status', 'value' => 'all']
            ])

            @endcomponent
        </div>
    </div>


    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        {{ $bookings->links() }}
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-3 pr-4">Kode Booking</th>
                    <th class="py-3 pr-4">Layanan</th>
                    <th class="py-3 pr-4">Jadwal</th>
                    <th class="py-3 pr-4">Ruangan</th>
                    <th class="py-3 pr-4">Dokter</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3 pr-4">Pembayaran</th>
                    <th class="py-3 pr-4">DP</th>
                    <th class="py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                <tr class="border-b">
                    <td class="py-3 pr-4 font-medium">{{ $booking->code ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $booking->service?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">
                        @php
                        $scheduledLabel = '-';
                        if ($booking->scheduled_date && $booking->scheduled_start) {
                        $scheduledLabel = Carbon::parse($booking->scheduled_date->toDateString() . ' ' .
                        $booking->scheduled_start, 'Asia/Makassar')
                        ->format('d M Y H:i');
                        }
                        @endphp
                        {{ $scheduledLabel }}
                    </td>
                    <td class="py-3 pr-4">{{ $booking->room?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $booking->doctor?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">
                        @php
                        $status = $booking->status;
                        $label = $status?->getLabel() ?? ($status?->value ?? '-');
                        $color = match ($status?->value) {
                        'pending' => 'bg-amber-100 text-amber-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'ongoing' => 'bg-yellow-100 text-yellow-700',
                        'done' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                        };
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $label }}
                        </span>
                    </td>

                    <td class="py-3 pr-4">
                        @php
                        $payment = $booking->payment_status;
                        $paymentLabel = $payment?->getLabel() ?? ($payment?->value ?? '-');
                        $paymentColor = match ($payment?->value) {
                        'unpaid' => 'bg-gray-100 text-gray-700',
                        'dp' => 'bg-amber-100 text-amber-700',
                        'paid' => 'bg-green-100 text-green-700',
                        'refunded' => 'bg-blue-100 text-blue-700',
                        'failed' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                        };
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $paymentColor }}">
                            {{ $paymentLabel }}
                        </span>
                    </td>

                    <td class="py-3 pr-4">
                        @php
                        $dpAmount = $booking->dp_amount;
                        $dpPercentage = $booking->dp_percentage;

                        $dpLabel = '-';
                        if ((float) ($dpAmount ?? 0) > 0 || (float) ($dpPercentage ?? 0) > 0) {
                        $parts = [];
                        if ((float) ($dpAmount ?? 0) > 0) {
                        $parts[] = 'Rp '.number_format((float) $dpAmount, 0, ',', '.');
                        }
                        if ((float) ($dpPercentage ?? 0) > 0) {
                        $parts[] = rtrim(rtrim(number_format((float) $dpPercentage, 2, '.', ''), '0'), '.').'%';
                        }
                        $dpLabel = implode(' • ', $parts);
                        }
                        @endphp
                        <span class="text-xs text-gray-700">{{ $dpLabel }}</span>
                    </td>

                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            @if (in_array($booking->status?->value, [AppointmentStatus::CONFIRMED], true))
                            <button wire:click="openReschedule({{ (int) $booking->id }})"
                                class="px-3 py-2 bg-gray-900 rounded-sm text-white text-xs">
                                Jadwal Ulang
                            </button>
                            <button wire:click="cancelBooking({{ (int) $booking->id }})"
                                class="px-3 py-2 bg-red-600 rounded-sm text-white text-xs">
                                Batalkan
                            </button>
                            @elseif(in_array($booking->status?->value, [AppointmentStatus::PENDING], true))
                            <button wire:click="paidBooking({{ (int) $booking->id }})"
                                class="px-3 py-2 bg-gray-900 rounded-sm text-white text-xs">
                                Bayar Sekarang
                            </button>

                            @else

                            <span class="text-xs text-gray-500">-</span>
                            @endif
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="9" class="py-6 text-center text-gray-500">Belum ada riwayat pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>

    @if ($isRescheduleOpen && $activeRescheduleBooking)
    <div x-data="{isOpen: @entangle('isRescheduleOpen')}" x-show="isOpen" x-cloak>
        @component('components.modal', [
        'maxWidth' => 'max-w-4xl',
        ])
        @slot('title')
        Jadwal Ulang Booking {{ $activeRescheduleBooking->code ?? '' }}
        @endslot

        <div class="space-y-4 p-6">
            <div class="text-sm text-gray-600">
                Pilih tanggal & jam baru, lalu klik simpan.
            </div>

            @livewire('guest.booking.components.booking-callendar', [
            'service' => $activeRescheduleBooking->service,
            'excludeAppointmentId' => $activeRescheduleBooking->id,
            ])

            @if ($errors->has('reschedule_date') || $errors->has('reschedule_time'))
            <p class="text-sm font-light text-red-500">Silakan pilih tanggal dan jam.</p>
            @endif

            <div class="flex justify-end gap-2">
                <button wire:click="closeReschedule" class="px-4 py-2 border rounded">
                    Batal
                </button>
                <button wire:click="applyReschedule" class="px-4 py-2 bg-primary text-white rounded">
                    Simpan Jadwal
                </button>
            </div>
        </div>
        @endcomponent
    </div>
    @endif
</div>
