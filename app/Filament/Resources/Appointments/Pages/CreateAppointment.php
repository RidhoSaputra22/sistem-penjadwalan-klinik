<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Patient;
use App\Models\Service;
use App\Services\Helper\ReservationServiceHelper;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Appointments\AppointmentResource;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $tz = ReservationServiceHelper::TZ;

            // Form memilih patients.id
            $patientId = isset($data['patient_id']) ? (int) $data['patient_id'] : 0;
            $patient = Patient::query()->with('user')->findOrFail($patientId);

            $service = Service::query()->findOrFail((int) $data['service_id']);

            $rawScheduledDate = $data['scheduled_date'] ?? '';
            $scheduledDate = $rawScheduledDate instanceof \DateTimeInterface
                ? Carbon::instance($rawScheduledDate)->setTimezone($tz)->toDateString()
                : (string) $rawScheduledDate;
            $scheduledStart = (string) ($data['scheduled_start'] ?? '');

            $scheduledAt = Carbon::parse(trim($scheduledDate . ' ' . $scheduledStart), $tz);

            if (Holiday::query()->whereDate('date', $scheduledAt->toDateString())->exists()) {
                throw ValidationException::withMessages([
                    'scheduled_date' => 'Tanggal tersebut adalah hari libur. Silakan pilih tanggal lain.',
                ]);
            }

            $durationMinutes = (int) ($service->duration_minutes ?? 60);
            $date = $scheduledAt->toDateString();
            $time = $scheduledAt->format('H:i');

            $slots = ReservationServiceHelper::getAvailableTimeSlots(
                date: $date,
                durationMinutes: $durationMinutes,
            );
            $slot = collect($slots)->first(fn (array $s) => ($s['time'] ?? null) === $time);
            if (! $slot || ! ($slot['available'] ?? false)) {
                throw ValidationException::withMessages([
                    'scheduled_start' => 'Tidak ada dokter/ruangan yang tersedia pada jam tersebut.',
                ]);
            }

            $assignment = ReservationServiceHelper::findAvailableAssignment(
                scheduledDate: $scheduledAt,
                durationMinutes: $durationMinutes,
                excludeAppointmentId: 0,
            );

            if (! $assignment) {
                throw ValidationException::withMessages([
                    'scheduled_start' => 'Tidak ada dokter/ruangan yang tersedia pada jam tersebut.',
                ]);
            }

            $payload = [
                'patient_id' => (int) $patient->id,
                'service_id' => (int) $service->id,
                'doctor_id' => $assignment['doctor_user_id'],
                'room_id' => $assignment['room_id'],
                'scheduled_date' => $assignment['scheduled_date'],
                'scheduled_start' => $assignment['scheduled_start'],
                'scheduled_end' => $assignment['scheduled_end'],
                'status' => AppointmentStatus::CONFIRMED,
                'notes' => ! empty($data['notes']) ? (string) $data['notes'] : null,
                'snap_token' => null,
            ];

            if (Schema::hasColumn('appointments', 'priority_id')) {
                $payload['priority_id'] = $service->priority_id;
            }
            if (Schema::hasColumn('appointments', 'original_scheduled_date')) {
                $payload['original_scheduled_date'] = $assignment['scheduled_date'];
                $payload['original_scheduled_start'] = $assignment['scheduled_start'];
                $payload['original_scheduled_end'] = $assignment['scheduled_end'];
            }

            return Appointment::query()->create($payload);

        } catch (ValidationException $e) {
            Notification::make()
                ->title('Gagal membuat appointment')
                ->body('Periksa jadwal; pastikan ada dokter & ruangan yang tersedia.')
                ->danger()
                ->send();

            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error creating appointment: ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
