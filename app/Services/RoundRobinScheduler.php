<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\RrPointer;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class RoundRobinScheduler
{
    /**
     * Jadwalkan janji (appointment) menggunakan algoritma Round Robin
     */
    public function schedule(int $serviceId, int $patientId, string $date, string $startTime, ?int $roomId = null): array
    {
        try {
            // Validasi input
            $validation = $this->validateScheduleInputs($serviceId, $date, $startTime);
            if ($validation['status'] === 'error') return $validation;

            $service = $validation['service'];
            [$start, $end, $weekday] = $this->prepareTime($date, $startTime, $service->duration_minutes);

            // Periksa hari libur
            if ($this->isHoliday($date)) {
                return $this->error('Tidak dapat menjadwalkan pada hari libur.');
            }

            // Ambil daftar dokter yang tersedia
            $doctors = $this->getAvailableDoctors($serviceId, $weekday, $start, $end);
            if ($doctors->isEmpty()) {
                return $this->error('Tidak ada dokter yang tersedia untuk layanan dan waktu ini.');
            }

            // Pilih dokter dengan Round Robin
            $selectedDoctor = $this->selectDoctor($serviceId, $doctors, $date, $start, $end);
            if (!$selectedDoctor) {
                return $this->error('Tidak ada dokter yang tersedia setelah pengecekan konflik.');
            }

            // Cari ruang yang tersedia jika belum ditentukan
            $roomId = $roomId ?: $this->getAvailableRoom($date, $start, $end);
            if (!$roomId) {
                return $this->error('Tidak ada ruangan yang tersedia untuk waktu ini.');
            }

            // Buat janji temu
            $appointment = $this->createAppointment(
                $patientId,
                $serviceId,
                $selectedDoctor->id,
                $roomId,
                $date,
                $start,
                $end
            );

            if (!$appointment) {
                return $this->error('Gagal membuat janji temu.');
            }

            return $this->success('Janji temu berhasil dijadwalkan.', $appointment);
        } catch (QueryException $e) {
            return $this->error('Kesalahan database: ' . $e->getMessage());
        } catch (\Throwable $e) {
            return $this->error('Terjadi kesalahan tak terduga: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui jadwal janji temu yang sudah ada
     */
    public function updateSchedule(int $appointmentId, string $date, string $startTime, ?int $roomId = null): array
    {
        try {
            $appointment = Appointment::find($appointmentId);
            if (!$appointment) {
                return $this->error('Janji temu tidak ditemukan.');
            }

            // Validasi input
            $service = Service::find($appointment->service_id);
            if (!$service || $service->duration_minutes <= 0) {
                return $this->error('Layanan tidak valid atau durasi layanan tidak sesuai.');
            }

            if (!$this->isValidDate($date)) {
                return $this->error('Format tanggal tidak valid (harus Y-m-d).');
            }

            if (!$this->isValidTime($startTime)) {
                return $this->error('Format waktu tidak valid (harus H:i:s).');
            }

            if ($this->isHoliday($date)) {
                return $this->error('Tidak dapat menjadwalkan ulang pada hari libur.');
            }

            [$start, $end, $weekday] = $this->prepareTime($date, $startTime, $service->duration_minutes);

            // Periksa ketersediaan dokter
            if ($this->hasDoctorConflict($appointment->doctor_id, $date, $start, $end, $appointmentId)) {
                return $this->error('Dokter tidak tersedia pada waktu yang dipilih.');
            }



            // Periksa ketersediaan ruangan
            $roomId = $roomId ?: $appointment->room_id;
            if ($this->hasRoomConflict($roomId, $date, $start, $end, $appointmentId)) {
                $roomId = $this->getAvailableRoom($date, $start, $end);
                if (!$roomId) {
                    return $this->error('Tidak ada ruangan yang tersedia untuk waktu ini.');
                }
            }

            // Lakukan update dengan transaksi
            $updated = DB::transaction(function () use ($appointment, $date, $start, $end, $roomId) {
                $appointment->update([
                    'scheduled_date'  => $date,
                    'scheduled_start' => $start->format('H:i:s'),
                    'scheduled_end'   => $end->format('H:i:s'),
                    'room_id'         => $roomId,
                ]);
                return $appointment;
            });

            return $this->success('Janji temu berhasil diperbarui.', $updated);
        } catch (QueryException $e) {
            return $this->error('Kesalahan database: ' . $e->getMessage());
        } catch (\Throwable $e) {
            return $this->error('Terjadi kesalahan tak terduga: ' . $e->getMessage());
        }
    }

    // ==================== Helper Validation ====================

    private function validateScheduleInputs(int $serviceId, string $date, string $startTime): array
    {
        $service = Service::find($serviceId);
        if (!$service) return $this->error('Layanan tidak ditemukan.');
        if (empty($service->duration_minutes) || $service->duration_minutes <= 0) {
            return $this->error('Durasi layanan tidak valid.');
        }
        if (!$this->isValidDate($date)) {
            return $this->error('Format tanggal tidak valid (harus Y-m-d).');
        }
        if (!$this->isValidTime($startTime)) {
            return $this->error('Format waktu tidak valid (harus H:i:s).');
        }

        return ['status' => 'success', 'service' => $service];
    }

    private function isHoliday(string $date): bool
    {
        return Holiday::where('date', $date)->exists();
    }

    private function isValidDate(string $date): bool
    {
        return Carbon::hasFormat($date, 'Y-m-d');
    }

    private function isValidTime(string $time): bool
    {
        return Carbon::hasFormat($time, 'H:i:s');
    }

    private function prepareTime(string $date, string $startTime, int $durationMinutes): array
    {
        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = (clone $start)->addMinutes($durationMinutes);
        $weekday = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
        return [$start, $end, $weekday];
    }

    private function getAvailableDoctors(int $serviceId, int $weekday, Carbon $start, Carbon $end)
    {
        return User::query()
            ->where('role', 'doctor')
            ->whereHas('services', fn($q) => $q->where('services.id', $serviceId))
            ->whereHas('doctorAvailabilities', function ($q) use ($weekday, $start, $end) {
                $q->where('weekday', $weekday)
                    ->where('is_active', true)
                    ->whereTime('start_time', '<=', $start->format('H:i:s'))
                    ->whereTime('end_time', '>=', $end->format('H:i:s'));
            })
            ->get()
            ->sortBy(fn($doc) => optional($doc->services()->where('service_id', $serviceId)->first()?->pivot)->priority ?? 100)
            ->values();
    }

    private function selectDoctor(int $serviceId, $doctors, string $date, Carbon $start, Carbon $end): ?User
    {
        $pointer = RrPointer::firstOrCreate(['service_id' => $serviceId], ['last_assigned_doctor_id' => null]);
        $ids = $doctors->pluck('id')->all();
        if (empty($ids)) return null;

        $startIndex = 0;
        if ($pointer->last_assigned_doctor_id) {
            $pos = array_search($pointer->last_assigned_doctor_id, $ids);
            $startIndex = $pos === false ? 0 : ($pos + 1) % count($ids);
        }

        foreach (range(0, count($ids) - 1) as $i) {
            $idx = ($startIndex + $i) % count($ids);
            $doctorId = $ids[$idx];
            if (!$this->hasDoctorConflict($doctorId, $date, $start, $end)) {
                $pointer->update(['last_assigned_doctor_id' => $doctorId]);
                return User::find($doctorId);
            }
        }

        return null;
    }

    private function hasDoctorConflict(int $doctorId, string $date, Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->where('scheduled_date', $date)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_start', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhereBetween('scheduled_end', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('scheduled_start', '<=', $start->format('H:i:s'))
                            ->where('scheduled_end', '>=', $end->format('H:i:s'));
                    });
            })
            ->exists();
    }

    private function hasRoomConflict(int $roomId, string $date, Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        return Appointment::where('room_id', $roomId)
            ->where('scheduled_date', $date)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_start', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhereBetween('scheduled_end', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('scheduled_start', '<=', $start->format('H:i:s'))
                            ->where('scheduled_end', '>=', $end->format('H:i:s'));
                    });
            })
            ->exists();
    }

    private function getAvailableRoom(string $date, Carbon $start, Carbon $end): ?int
    {
        return Room::whereDoesntHave('appointments', function ($q) use ($date, $start, $end) {
            $q->where('scheduled_date', $date)
                ->where(function ($q2) use ($start, $end) {
                    $q2->whereBetween('scheduled_start', [$start->format('H:i:s'), $end->format('H:i:s')])
                        ->orWhereBetween('scheduled_end', [$start->format('H:i:s'), $end->format('H:i:s')])
                        ->orWhere(function ($q3) use ($start, $end) {
                            $q3->where('scheduled_start', '<=', $start->format('H:i:s'))
                                ->where('scheduled_end', '>=', $end->format('H:i:s'));
                        });
                });
        })->first()?->id;
    }

    private function createAppointment(int $patientId, int $serviceId, int $doctorId, int $roomId, string $date, Carbon $start, Carbon $end)
    {
        try {
            return DB::transaction(function () use ($patientId, $serviceId, $doctorId, $roomId, $date, $start, $end) {
                return Appointment::create([
                    'code'            => 'APPT-' . strtoupper(uniqid()),
                    'patient_id'      => $patientId,
                    'service_id'      => $serviceId,
                    'doctor_id'       => $doctorId,
                    'room_id'         => $roomId,
                    'scheduled_date'  => $date,
                    'scheduled_start' => $start->format('H:i:s'),
                    'scheduled_end'   => $end->format('H:i:s'),
                    'status'          => 'confirmed',
                ]);
            });
        } catch (QueryException $e) {
            return null;
        }
    }

    // ==================== Response Helpers ====================

    private function success(string $message, $data = null): array
    {
        return ['status' => 'success', 'message' => $message, 'data' => $data];
    }

    private function error(string $message): array
    {
        return ['status' => 'error', 'message' => $message];
    }
}
