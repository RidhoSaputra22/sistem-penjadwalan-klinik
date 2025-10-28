<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\RrPointer;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use Carbon\Traits\Timestamp;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class DoctorAvailabilityGenerator
{
    public function generateSchedule(int $doctorId, int $hariAwal, int $hariAkhir, string $start, string $end)
    {
        try {
            $data = DoctorAvailability::where('user_id', $doctorId);

            if ($data->count() > 0) {
                $data->delete();
            }

            for ($i = $hariAwal; $i <= $hariAkhir; $i++) {
                $data = new DoctorAvailability;
                $data->user_id = $doctorId;
                $data->weekday = $i;
                $data->start_time = $start;
                $data->end_time = $end;
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
