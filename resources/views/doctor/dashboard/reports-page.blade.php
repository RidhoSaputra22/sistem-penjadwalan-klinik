<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    public string $dateFrom;

    public string $dateTo;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        // Default to end of month so upcoming bookings are included.
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    private function doctorId(): int
    {
        return (int) (Auth::id() ?? 0);
    }

    private function baseQuery()
    {
        return Appointment::query()
            ->with(['patient.user', 'service', 'room'])
            ->where('doctor_id', $this->doctorId())
            ->whereDate('scheduled_date', '>=', $this->dateFrom)
            ->whereDate('scheduled_date', '<=', $this->dateTo);
    }

    public function downloadCsv()
    {
        $doctorId = $this->doctorId();
        if (! $doctorId) {
            $this->redirectRoute('doctor.login');

            return;
        }

        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        $fileName = 'laporan-appointment-dokter-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Kode',
                'Tanggal',
                'Jam',
                'Pasien',
                'Layanan',
                'Ruangan',
                'Status',
                'Check-in',
                'Dipanggil',
                'Mulai',
                'Selesai',
            ]);

            $this->baseQuery()
                ->orderBy('scheduled_date')
                ->orderBy('scheduled_start')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $booking) {
                        $status = $booking->status?->value ?? '';
                        $statusLabel = $booking->status?->getLabel() ?? $status;

                        fputcsv($out, [
                            $booking->code,
                            $booking->scheduled_date?->toDateString(),
                            $booking->scheduled_start,
                            $booking->patient?->user?->name,
                            $booking->service?->name,
                            $booking->room?->name,
                            $statusLabel,
                            optional($booking->checked_in_at)->format('Y-m-d H:i:s'),
                            optional($booking->called_at)->format('Y-m-d H:i:s'),
                            optional($booking->service_started_at)->format('Y-m-d H:i:s'),
                            optional($booking->service_ended_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function with(): array
    {
        $query = $this->baseQuery();

        $total = (clone $query)->count();

        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusCards = collect(AppointmentStatus::cases())->map(function (AppointmentStatus $status) use ($byStatus) {
            $count = (int) ($byStatus[$status->value] ?? 0);

            return [
                'label' => $status->getLabel(),
                'value' => $status->value,
                'count' => $count,
                'color' => $status->getColor(),
            ];
        })->values();

        $perDay = (clone $query)
            ->selectRaw('scheduled_date, COUNT(*) as total')
            ->groupBy('scheduled_date')
            ->orderBy('scheduled_date')
            ->get()
            ->map(function ($row) {
                $date = is_string($row->scheduled_date) ? $row->scheduled_date : (string) $row->scheduled_date;
                $label = '';

                try {
                    $label = Carbon::parse($date, 'Asia/Makassar')->format('d M Y');
                } catch (Throwable $e) {
                    $label = $date;
                }

                return [
                    'date' => $date,
                    'label' => $label,
                    'total' => (int) ($row->total ?? 0),
                ];
            });

        return [
            'total' => $total,
            'statusCards' => $statusCards,
            'perDay' => $perDay,
        ];
    }
}; ?>

<div class="p-6 rounded-xl">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold">Rekap / Laporan</h2>
            <p class="text-sm text-gray-500">Ringkasan appointment berdasarkan rentang tanggal.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" wire:model.live="dateFrom" class="border border-gray-200 rounded-md px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" wire:model.live="dateTo" class="border border-gray-200 rounded-md px-3 py-2 text-sm" />
            </div>

            <button class="px-4 py-2 rounded-md bg-primary text-white text-sm hover:bg-primary/80"
                wire:click="downloadCsv">
                Download CSV
            </button>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-lg border border-gray-200 bg-white">
            <div class="text-xs text-gray-500">Total Appointment</div>
            <div class="text-2xl font-semibold">{{ $total }}</div>
        </div>

        @foreach ($statusCards as $card)
            @php
                $color = match ($card['color']) {
                    'gray' => 'bg-gray-100 text-gray-700',
                    'blue' => 'bg-blue-100 text-blue-700',
                    'yellow' => 'bg-yellow-100 text-yellow-800',
                    'green' => 'bg-green-100 text-green-700',
                    'red' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <div class="p-4 rounded-lg border border-gray-200 bg-white">
                <div class="text-xs text-gray-500">{{ $card['label'] }}</div>
                <div class="text-2xl font-semibold">{{ $card['count'] }}</div>
                <div class="mt-2 inline-flex px-2 py-1 rounded-md text-xs {{ $color }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <h3 class="font-semibold">Ringkasan per Hari</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-3 pr-4">Tanggal</th>
                        <th class="py-3 pr-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perDay as $row)
                        <tr class="border-b">
                            <td class="py-3 pr-4">{{ $row['label'] }}</td>
                            <td class="py-3 pr-4 font-medium">{{ $row['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-6 text-center text-gray-500">Tidak ada data pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
