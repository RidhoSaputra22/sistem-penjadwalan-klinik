<?php

namespace App\Services;

use App\Enums\WeekdayEnum;
use App\Models\DoctorAvailability;

class DoctorAvailabilityGenerator
{
    public function generateSchedule(int $doctorId, array $hariRange, array $timeRange): array
    {
        try {
            $data = DoctorAvailability::where('user_id', $doctorId);

            if ($data->count() > 0) {
                $data->delete();
            }

            foreach ($hariRange as $hari) {
                // dd($hari, WeekdayEnum::from($key)->value);

                $data = new DoctorAvailability;
                $data->user_id = $doctorId;
                $data->weekday = WeekdayEnum::from($hari)->value;
                $data->start_time = $timeRange[0];
                $data->end_time = $timeRange[count($timeRange) - 1];
                $data->is_active = true;
                $data->save();
            }

            return $this->success('Jadwal Dokter berhasil dijadwalkan');
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        }
    }

    private function success(string $message, $data = null): array
    {
        return ['status' => 'success', 'message' => $message, 'data' => $data];
    }

    private function error(string $message): array
    {
        return ['status' => 'error', 'message' => $message];
    }
}
