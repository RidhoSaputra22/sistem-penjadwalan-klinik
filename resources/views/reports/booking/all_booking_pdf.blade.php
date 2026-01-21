<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laporan & Rekap Kinerja Antrean' }}</title>

    <style>
        @page { margin: 18mm 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
        }
        .muted { color: #6b7280; }
        .small { font-size: 10px; }
        .title { font-size: 18px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 12px; margin: 4px 0 0; }
        .divider { height: 1px; background: #e5e7eb; margin: 10px 0 14px; }

        .row { display: flex; gap: 10px; }
        .col { flex: 1; }
        .stack { display: block; }

        .kpi-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .kpi {
            width: calc(25% - 7.5px);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
        }
        .kpi .label { font-size: 10px; color: #6b7280; margin: 0 0 6px; }
        .kpi .value { font-size: 16px; font-weight: 700; margin: 0; }
        .kpi .hint { font-size: 10px; color: #6b7280; margin: 6px 0 0; }
        .kpi.wide { width: calc(50% - 5px); }

        h2 { font-size: 13px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 7px 8px; vertical-align: top; }
        th { background: #f9fafb; font-weight: 700; text-align: left; }
        td.num, th.num { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .section { page-break-inside: avoid; }
        .page-break { page-break-after: always; }
        .footer {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .note { border: 1px dashed #d1d5db; border-radius: 8px; padding: 10px; }

        /* DomPDF/print friendly: avoid flex collapse */
        .kpi-grid, .row { width: 100%; }
    </style>
</head>
<body>
@php
    $generatedAt = $generatedAt ?? now();

    $periodStart = $periodStart ?? ($filters['period_start'] ?? null);
    $periodEnd = $periodEnd ?? ($filters['period_end'] ?? null);

    $summary = $summary ?? [];
    $daily = $daily ?? [];
    $priorityBreakdown = $priorityBreakdown ?? [];

    $valueOrDash = function ($value, $suffix = '') {
        if ($value === null || $value === '') return '—';
        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . $suffix;
        }
        return (string) $value;
    };

    $intOrDash = function ($value) {
        if ($value === null || $value === '') return '—';
        if (!is_numeric($value)) return (string) $value;
        return number_format((int) $value, 0, ',', '.');
    };

    $pctOrDash = function ($value) {
        if ($value === null || $value === '') return '—';
        if (!is_numeric($value)) return (string) $value;
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%';
    };

    $durationOrDash = function ($value, $unit = 'menit') {
        if ($value === null || $value === '') return '—';
        if (!is_numeric($value)) return (string) $value;
        // Default: tampilkan dalam menit (biar sederhana, controller boleh kirim string durasi sendiri)
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . ' ' . $unit;
    };

    $renderFilter = function ($label, $val) {
        if ($val === null || $val === '') return null;
        return $label . ': ' . $val;
    };

    $filterChips = array_values(array_filter([
        $renderFilter('Periode', ($periodStart && $periodEnd) ? ($periodStart . ' s/d ' . $periodEnd) : null),
        $renderFilter('Dokter', $filters['doctor'] ?? null),
        $renderFilter('Layanan', $filters['service'] ?? null),
        $renderFilter('Ruangan', $filters['room'] ?? null),
        $renderFilter('Metode', $filters['method'] ?? null),
    ]));
@endphp

<div>
    <div class="row" style="align-items: flex-start;">
        <div class="col">
            <p class="muted small" style="margin: 0;">{{ $clinicName ?? config('app.name') }}</p>
            <h1 class="title">{{ $title ?? 'Laporan & Rekap Kinerja Antrean' }}</h1>
            <p class="subtitle muted">Tanggal cetak: {{ \Carbon\Carbon::parse($generatedAt)->translatedFormat('d F Y H:i') }}</p>
        </div>
        <div class="col" style="text-align: right;">
            @if(!empty($filterChips))
                <div class="stack">
                    @foreach($filterChips as $chip)
                        <span class="badge">{{ $chip }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="divider"></div>

    <div class="section">
        <h2>Ringkasan KPI</h2>
        <div class="kpi-grid">
            <div class="kpi">
                <p class="label">Total Kunjungan/Antrean</p>
                <p class="value">{{ $intOrDash($summary['total'] ?? null) }}</p>
                <p class="hint">Jumlah data dalam periode</p>
            </div>
            <div class="kpi">
                <p class="label">AWT (Average Waiting Time)</p>
                <p class="value">{{ $durationOrDash($summary['awt_minutes'] ?? ($summary['awt'] ?? null), $summary['awt_unit'] ?? 'menit') }}</p>
                <p class="hint">Rata-rata waktu tunggu</p>
            </div>
            <div class="kpi">
                <p class="label">TAT (Turnaround Time)</p>
                <p class="value">{{ $durationOrDash($summary['tat_minutes'] ?? ($summary['tat'] ?? null), $summary['tat_unit'] ?? 'menit') }}</p>
                <p class="hint">Rata-rata waktu layanan selesai</p>
            </div>
            <div class="kpi">
                <p class="label">Panjang Antrean (Rata-rata)</p>
                <p class="value">{{ $valueOrDash($summary['avg_queue_length'] ?? null) }}</p>
                <p class="hint">Estimasi beban antrean</p>
            </div>

            <div class="kpi">
                <p class="label">Panjang Antrean (Maksimum)</p>
                <p class="value">{{ $valueOrDash($summary['max_queue_length'] ?? null) }}</p>
                <p class="hint">Puncak antrean</p>
            </div>
            <div class="kpi">
                <p class="label">Ketepatan Prioritas</p>
                <p class="value">{{ $pctOrDash($summary['priority_accuracy_percent'] ?? null) }}</p>
                <p class="hint">Kesesuaian prioritas vs aturan</p>
            </div>
            <div class="kpi">
                <p class="label">No Show</p>
                <p class="value">{{ $intOrDash($summary['no_show_count'] ?? null) }}</p>
                <p class="hint">{{ $pctOrDash($summary['no_show_rate_percent'] ?? null) }} dari total</p>
            </div>
            <div class="kpi">
                <p class="label">Penjadwalan Ulang</p>
                <p class="value">{{ $intOrDash($summary['rescheduled_count'] ?? null) }}</p>
                <p class="hint">{{ $pctOrDash($summary['rescheduled_rate_percent'] ?? null) }} dari total</p>
            </div>

            <div class="kpi wide">
                <p class="label">Kepatuhan SLA (Opsional)</p>
                <p class="value">{{ $pctOrDash($summary['sla_on_time_percent'] ?? null) }}</p>
                <p class="hint">Contoh: selesai ≤ target waktu (isi bila diterapkan)</p>
            </div>
            <div class="kpi wide">
                <p class="label">Catatan Evaluasi (Opsional)</p>
                <p class="value" style="font-size: 12px; font-weight: 600;">
                    {{ $summary['evaluation_note'] ?? '—' }}
                </p>
                <p class="hint">Misal: jam sibuk, bottleneck poli/ruangan</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Rekap Harian</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Tanggal</th>
                    <th class="num" style="width: 12%">Total</th>
                    <th class="num" style="width: 14%">AWT</th>
                    <th class="num" style="width: 14%">TAT</th>
                    <th class="num" style="width: 14%">Avg Antrean</th>
                    <th class="num" style="width: 14%">No Show</th>
                    <th class="num" style="width: 14%">Reschedule</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daily as $row)
                    @php
                        $date = $row['date'] ?? ($row->date ?? null);
                        $total = $row['total'] ?? ($row->total ?? null);
                        $awt = $row['awt_minutes'] ?? ($row['awt'] ?? ($row->awt_minutes ?? ($row->awt ?? null)));
                        $tat = $row['tat_minutes'] ?? ($row['tat'] ?? ($row->tat_minutes ?? ($row->tat ?? null)));
                        $avgQ = $row['avg_queue_length'] ?? ($row->avg_queue_length ?? null);
                        $noShow = $row['no_show_count'] ?? ($row->no_show_count ?? null);
                        $resch = $row['rescheduled_count'] ?? ($row->rescheduled_count ?? null);
                    @endphp
                    <tr>
                        <td>{{ $date ? \Carbon\Carbon::parse($date)->translatedFormat('d M Y') : '—' }}</td>
                        <td class="num">{{ $intOrDash($total) }}</td>
                        <td class="num">{{ $durationOrDash($awt, $summary['awt_unit'] ?? 'menit') }}</td>
                        <td class="num">{{ $durationOrDash($tat, $summary['tat_unit'] ?? 'menit') }}</td>
                        <td class="num">{{ $valueOrDash($avgQ) }}</td>
                        <td class="num">{{ $intOrDash($noShow) }}</td>
                        <td class="num">{{ $intOrDash($resch) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">Belum ada data rekap harian untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <p class="muted small" style="margin: 6px 0 0;">
            Catatan: AWT/TAT bisa dikirim sebagai angka (menit) atau string terformat dari controller.
        </p>
    </div>

    <div class="section">
        <h2>Ketepatan Prioritas</h2>
        <table>
            <thead>
                <tr>
                    <th>Level Prioritas</th>
                    <th class="num">Total</th>
                    <th class="num">Tepat</th>
                    <th class="num">Tidak Tepat</th>
                    <th class="num">Akurasi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($priorityBreakdown as $row)
                    @php
                        $label = $row['label'] ?? ($row->label ?? '—');
                        $total = $row['total'] ?? ($row->total ?? null);
                        $correct = $row['correct'] ?? ($row->correct ?? null);
                        $incorrect = $row['incorrect'] ?? ($row->incorrect ?? null);
                        $accuracy = $row['accuracy_percent'] ?? ($row->accuracy_percent ?? null);
                        $note = $row['note'] ?? ($row->note ?? '');
                    @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="num">{{ $intOrDash($total) }}</td>
                        <td class="num">{{ $intOrDash($correct) }}</td>
                        <td class="num">{{ $intOrDash($incorrect) }}</td>
                        <td class="num">{{ $pctOrDash($accuracy) }}</td>
                        <td class="muted">{{ $note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada data ketepatan prioritas untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>No Show & Penjadwalan Ulang</h2>
        <div class="row">
            <div class="col">
                <div class="note">
                    <div style="display:flex; justify-content: space-between; gap: 10px;">
                        <strong>No Show</strong>
                        <span class="badge">{{ $intOrDash($summary['no_show_count'] ?? null) }}</span>
                    </div>
                    <div class="muted" style="margin-top: 6px;">
                        Tingkat: {{ $pctOrDash($summary['no_show_rate_percent'] ?? null) }}
                    </div>
                    <div class="muted small" style="margin-top: 8px;">
                        Gunakan metrik ini untuk evaluasi reminder, jam praktik, dan akurasi estimasi waktu.
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="note">
                    <div style="display:flex; justify-content: space-between; gap: 10px;">
                        <strong>Reschedule</strong>
                        <span class="badge">{{ $intOrDash($summary['rescheduled_count'] ?? null) }}</span>
                    </div>
                    <div class="muted" style="margin-top: 6px;">
                        Tingkat: {{ $pctOrDash($summary['rescheduled_rate_percent'] ?? null) }}
                    </div>
                    <div class="muted small" style="margin-top: 8px;">
                        Jika fitur penjadwalan ulang diterapkan, pantau sumber reschedule (pasien/dokter/ruangan).
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="muted small">
            Dicetak oleh sistem: {{ config('app.name') }}
        </div>
        <div class="muted small" style="text-align:right;">
            {{ $reportCode ?? 'QUEUE-PERF' }} • {{ \Carbon\Carbon::parse($generatedAt)->format('Ymd-His') }}
        </div>
    </div>
</div>
</body>
</html>
