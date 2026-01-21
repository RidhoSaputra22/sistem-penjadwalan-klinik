<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class QueuePerformanceReportService
{
    /**
     * @param array{
     *   period_start?: string|null,
     *   period_end?: string|null,
     *   doctor_id?: int|string|null,
     *   service_id?: int|string|null,
     *   room_id?: int|string|null
     * } $filters
     *
     * @return array{
     *   generatedAt: \Carbon\Carbon,
     *   periodStart: string,
     *   periodEnd: string,
     *   summary: array<string, mixed>,
     *   daily: array<int, array<string, mixed>>,
     *   priorityBreakdown: array<int, array<string, mixed>>
     * }
     */
    public function build(array $filters = []): array
    {
        $tz = config('app.timezone') ?: 'UTC';

        $periodStart = $this->normalizeDate($filters['period_start'] ?? null) ?? Carbon::now($tz)->subDays(6)->toDateString();
        $periodEnd = $this->normalizeDate($filters['period_end'] ?? null) ?? Carbon::now($tz)->toDateString();

        if ($periodStart > $periodEnd) {
            [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
        }

        $query = Appointment::query()
            ->with(['service.priority'])
            ->whereBetween('scheduled_date', [$periodStart, $periodEnd]);

        $this->applyFilters($query, $filters);

        $appointments = $query
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_start')
            ->get($this->selectColumns());

        $daily = [];
        $grouped = $appointments->groupBy(fn ($a) => (string) $a->scheduled_date);

        foreach ($grouped as $date => $items) {
            [$awtAvg, $awtCount] = $this->avgMinutes($items, 'checked_in_at', 'service_started_at');
            [$tatAvg, $tatCount] = $this->avgMinutes($items, 'checked_in_at', 'service_ended_at');

            $queueStats = $this->queueStatsForDay($items, $date, $tz);

            $noShowCount = (int) $items->filter(fn ($a) => ! empty($a->no_show_at))->count();
            $rescheduledCount = (int) $items->filter(fn ($a) => (int) ($a->rescheduled_count ?? 0) > 0)->count();

            $daily[] = [
                'date' => $date,
                'total' => $items->count(),
                'awt_minutes' => $awtCount > 0 ? $awtAvg : null,
                'tat_minutes' => $tatCount > 0 ? $tatAvg : null,
                'avg_queue_length' => $queueStats['avg'] ?? null,
                'max_queue_length' => $queueStats['max'] ?? null,
                'no_show_count' => $noShowCount,
                'rescheduled_count' => $rescheduledCount,
            ];
        }

        [$awtOverall, $awtOverallCount] = $this->avgMinutes($appointments, 'checked_in_at', 'service_started_at');
        [$tatOverall, $tatOverallCount] = $this->avgMinutes($appointments, 'checked_in_at', 'service_ended_at');

        $queueOverall = $this->queueStatsOverall($appointments, $tz);

        $total = $appointments->count();
        $noShowCount = (int) $appointments->filter(fn ($a) => ! empty($a->no_show_at))->count();
        $rescheduledCount = (int) $appointments->filter(fn ($a) => (int) ($a->rescheduled_count ?? 0) > 0)->count();

        $priorityAccuracy = $this->priorityAccuracy($appointments);

        $summary = [
            'total' => $total,
            'awt_minutes' => $awtOverallCount > 0 ? $awtOverall : null,
            'tat_minutes' => $tatOverallCount > 0 ? $tatOverall : null,
            'avg_queue_length' => $queueOverall['avg'] ?? null,
            'max_queue_length' => $queueOverall['max'] ?? null,
            'no_show_count' => $noShowCount,
            'no_show_rate_percent' => $total > 0 ? ($noShowCount / $total) * 100 : null,
            'rescheduled_count' => $rescheduledCount,
            'rescheduled_rate_percent' => $total > 0 ? ($rescheduledCount / $total) * 100 : null,
            'priority_accuracy_percent' => $priorityAccuracy['overall_percent'],
            'awt_unit' => 'menit',
            'tat_unit' => 'menit',
        ];

        return [
            'generatedAt' => now(),
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'summary' => $summary,
            'daily' => $daily,
            'priorityBreakdown' => $priorityAccuracy['breakdown'],
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<int, string> */
    private function selectColumns(): array
    {
        $columns = [
            'id',
            'service_id',
            'doctor_id',
            'room_id',
            'scheduled_date',
            'scheduled_start',
            'scheduled_end',
            'status',
        ];

        // Optional columns (depends on whether the migration has been run)
        foreach (['priority_id', 'checked_in_at', 'service_started_at', 'service_ended_at', 'no_show_at', 'rescheduled_count'] as $col) {
            if (Schema::hasColumn('appointments', $col)) {
                $columns[] = $col;
            }
        }

        return $columns;
    }

    /** @param Builder<Appointment> $query */
    private function applyFilters(Builder $query, array $filters): void
    {
        $doctorId = $filters['doctor_id'] ?? null;
        if ($doctorId !== null && $doctorId !== '') {
            $query->where('doctor_id', (int) $doctorId);
        }

        $serviceId = $filters['service_id'] ?? null;
        if ($serviceId !== null && $serviceId !== '') {
            $query->where('service_id', (int) $serviceId);
        }

        $roomId = $filters['room_id'] ?? null;
        if ($roomId !== null && $roomId !== '') {
            $query->where('room_id', (int) $roomId);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, Appointment> $items
     * @return array{0: float, 1: int}
     */
    private function avgMinutes($items, string $startColumn, string $endColumn): array
    {
        $totalMinutes = 0.0;
        $count = 0;

        foreach ($items as $a) {
            $start = $a->{$startColumn} ?? null;
            $end = $a->{$endColumn} ?? null;

            if (! $start || ! $end) {
                continue;
            }

            try {
                $startAt = Carbon::parse($start);
                $endAt = Carbon::parse($end);
            } catch (\Throwable) {
                continue;
            }

            $diff = $startAt->diffInMinutes($endAt, false);
            if ($diff < 0) {
                continue;
            }

            $totalMinutes += $diff;
            $count++;
        }

        return [$count > 0 ? $totalMinutes / $count : 0.0, $count];
    }

    /**
     * Queue length proxy based on waiting window: checked_in_at (+1) until service_started_at (-1).
     *
     * @param \Illuminate\Support\Collection<int, Appointment> $items
     * @return array{avg: ?float, max: ?int}
     */
    private function queueStatsForDay($items, string $date, string $tz): array
    {
        $events = [];

        foreach ($items as $a) {
            if (! $a->checked_in_at || ! $a->service_started_at) {
                continue;
            }

            try {
                $in = Carbon::parse($a->checked_in_at, $tz);
                $start = Carbon::parse($a->service_started_at, $tz);
            } catch (\Throwable) {
                continue;
            }

            if ($in->toDateString() !== $date) {
                continue;
            }

            if ($start->lessThanOrEqualTo($in)) {
                continue;
            }

            $events[] = ['t' => $in, 'd' => +1];
            $events[] = ['t' => $start, 'd' => -1];
        }

        if (count($events) < 2) {
            return ['avg' => null, 'max' => null];
        }

        usort($events, fn ($a, $b) => $a['t']->getTimestamp() <=> $b['t']->getTimestamp());

        $count = 0;
        $max = 0;
        $area = 0.0;

        $prev = $events[0]['t'];
        foreach ($events as $e) {
            $t = $e['t'];
            $delta = max(0, $prev->diffInMinutes($t, false));
            $area += $count * $delta;

            $count += (int) $e['d'];
            $max = max($max, $count);
            $prev = $t;
        }

        $span = $events[0]['t']->diffInMinutes($events[count($events) - 1]['t'], false);
        if ($span <= 0) {
            return ['avg' => null, 'max' => $max];
        }

        return [
            'avg' => $area / $span,
            'max' => $max,
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Appointment> $appointments
     * @return array{avg: ?float, max: ?int}
     */
    private function queueStatsOverall($appointments, string $tz): array
    {
        // Aggregate by day, then take weighted average of avgs by span.
        $grouped = $appointments->groupBy(fn ($a) => (string) $a->scheduled_date);

        $max = null;
        $sumAvg = 0.0;
        $daysWithData = 0;

        foreach ($grouped as $date => $items) {
            $stats = $this->queueStatsForDay($items, $date, $tz);
            if ($stats['max'] !== null) {
                $max = $max === null ? $stats['max'] : max($max, $stats['max']);
            }
            if ($stats['avg'] !== null) {
                $sumAvg += (float) $stats['avg'];
                $daysWithData++;
            }
        }

        return [
            'avg' => $daysWithData > 0 ? $sumAvg / $daysWithData : null,
            'max' => $max,
        ];
    }

    /**
     * Accuracy definition (current implementation): appointment.priority_id should match service.priority_id.
     * If either side is missing, it's excluded from accuracy.
     *
     * @param \Illuminate\Support\Collection<int, Appointment> $appointments
     * @return array{overall_percent: ?float, breakdown: array<int, array<string, mixed>>}
     */
    private function priorityAccuracy($appointments): array
    {
        $eligible = 0;
        $correct = 0;

        $byPriority = [];

        foreach ($appointments as $a) {
            $servicePriorityId = (int) ($a->service?->priority_id ?? 0);
            $actualPriorityId = (int) ($a->priority_id ?? 0);

            if ($servicePriorityId <= 0 || $actualPriorityId <= 0) {
                continue;
            }

            $eligible++;
            $isCorrect = $servicePriorityId === $actualPriorityId;
            if ($isCorrect) {
                $correct++;
            }

            $label = $a->service?->priority?->name ?? 'Prioritas';
            $key = $actualPriorityId;

            if (! isset($byPriority[$key])) {
                $byPriority[$key] = [
                    'label' => $label,
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'accuracy_percent' => null,
                    'note' => 'Akurasi = priority appointment vs priority layanan',
                ];
            }

            $byPriority[$key]['total']++;
            if ($isCorrect) {
                $byPriority[$key]['correct']++;
            } else {
                $byPriority[$key]['incorrect']++;
            }
        }

        foreach ($byPriority as $k => $row) {
            $total = (int) ($row['total'] ?? 0);
            $byPriority[$k]['accuracy_percent'] = $total > 0
                ? ((int) $row['correct'] / $total) * 100
                : null;
        }

        return [
            'overall_percent' => $eligible > 0 ? ($correct / $eligible) * 100 : null,
            'breakdown' => array_values($byPriority),
        ];
    }
}
