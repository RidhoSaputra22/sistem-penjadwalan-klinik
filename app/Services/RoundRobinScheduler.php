<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\RrPointer;
use App\Models\Service;
use App\Models\User;
use App\Models\DoctorAvailability;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoundRobinScheduler
{
    /**
     * Schedule a single appointment using Round Robin
     *
     * @param int $serviceId
     * @param int $patientId
     * @param string $date Y-m-d
     * @param string $start_time H:i:s
     * @param int|null $roomId
     * @return Appointment|null
     */
    public function schedule(int $serviceId, int $patientId, string $date, string $start_time, ?int $roomId = null): ?Appointment
    {
        $service = Service::findOrFail($serviceId);


        // holiday check
        if (Holiday::where('date', $date)->exists()) {
            throw new \Exception('Holiday');
            return null; // cannot schedule on holiday (caller can handle message)
        }

        $start = Carbon::createFromFormat('H:i:s', $start_time);
        $end = (clone $start)->addMinutes($service->duration_minutes);
        $weekday = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek; // 0..6 Sun..Sat

        // get doctors for service with availability on weekday and active
        $candidateQuery = User::query()
            ->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId);
            })->where('role', 'doctor');

        // join doctor_availabilities filter
        $candidateQuery->whereHas('doctorAvailabilities', function ($q) use ($weekday, $start, $end) {
            $q->where('weekday', $weekday)
                ->where('is_active', true)
                ->whereTime('start_time', '<=', $start->format('H:i:s'))
                ->whereTime('end_time', '>=', $end->format('H:i:s'));
        });

        // eager load appointments maybe
        $candidates = $candidateQuery->get();

        // dd($candidates, $candidateQuery, $weekday);

        if ($candidates->isEmpty()) {
            throw new \Exception('No available doctor');
            return null;
        }

        // build ordered list: respect doctor_service.priority if exists
        $doctors = $candidates->sortBy(function ($doc) use ($serviceId) {
            $pivot = $doc->services()->where('service_id', $serviceId)->first()->pivot ?? null;
            return $pivot->priority ?? 100;
        })->values();

        // find last pointer
        $pointer = RrPointer::firstOrCreate(['service_id' => $serviceId], ['last_assigned_doctor_id' => null]);

        // create circular array of doctor ids
        $ids = $doctors->pluck('id')->all();
        if (empty($ids)) {
            throw new \Exception('No available doctor');
            return null;
        };

        // find start index
        $startIndex = 0;
        if ($pointer->last_assigned_doctor_id) {
            $pos = array_search($pointer->last_assigned_doctor_id, $ids);
            $startIndex = $pos === false ? 0 : ($pos + 1) % count($ids);
        }

        $tried = 0;
        $selectedDoctor = null;
        $total = count($ids);

        while ($tried < $total) {
            $idx = ($startIndex + $tried) % $total;
            $doctorId = $ids[$idx];

            // conflict check: doctor has any appointment overlapping on date
            $conflict = Appointment::where('doctor_id', $doctorId)
                ->where('scheduled_date', $date)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('scheduled_start', [$start->format('H:i:s'), $end->format('H:i:s')])
                        ->orWhereBetween('scheduled_end', [$start->format('H:i:s'), $end->format('H:i:s')])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('scheduled_start', '<=', $start->format('H:i:s'))
                                ->where('scheduled_end', '>=', $end->format('H:i:s'));
                        });
                })->exists();

            if (!$conflict) {
                $selectedDoctor = User::find($doctorId);
                break;
            }
            $tried++;
        }

        if (!$selectedDoctor) {
            dd($tried, $startIndex, $ids);
            throw new \Exception('No available doctor');
            return null; // no available doctor this slot
        }

        // create appointment within DB transaction
        $appointment = DB::transaction(function () use ($patientId, $serviceId, $selectedDoctor, $roomId, $date, $start, $end, $pointer) {
            $appt = Appointment::create([
                'code' => 'APPT-' . strtoupper(uniqid()),
                'patient_id' => $patientId,
                'service_id' => $serviceId,
                'doctor_id' => $selectedDoctor->id,
                'room_id' => $roomId,
                'scheduled_date' => $date,
                'scheduled_start' => $start->format('H:i:s'),
                'scheduled_end' => $end->format('H:i:s'),
                'status' => 'confirmed',
            ]);

            // update pointer
            $pointer->last_assigned_doctor_id = $selectedDoctor->id;
            $pointer->save();

            return $appt;
        });

        return $appointment;
    }
}
