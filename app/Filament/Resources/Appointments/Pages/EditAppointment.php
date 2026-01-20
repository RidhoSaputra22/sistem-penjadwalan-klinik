<?php

namespace App\Filament\Resources\Appointments\Pages;

use Filament\Actions\DeleteAction;
use App\Models\Holiday;
use App\Models\Patient;
use App\Models\User;
use App\Services\Helper\ReservationServiceHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Appointments\AppointmentResource;
use Illuminate\Validation\ValidationException;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $tz = ReservationServiceHelper::TZ;

            $record->loadMissing(['service', 'patient']);

            // Normalisasi patient_id: form memilih User, tapi DB butuh patients.id
            if (array_key_exists('patient_id', $data) && is_numeric($data['patient_id'])) {
                $incoming = (int) $data['patient_id'];

                $patient = Patient::query()->find($incoming);
                if (! $patient) {
                    $user = User::query()->with('patient')->find($incoming);
                    $patient = $user?->patient;
                }

                if (! $patient) {
                    throw ValidationException::withMessages([
                        'patient_id' => 'Pasien tidak valid.',
                    ]);
                }

                $data['patient_id'] = (int) $patient->id;
            }

            // Jangan izinkan perubahan field yang seharusnya readonly di edit.
            unset($data['code'], $data['service_id']);

            $durationMinutes = (int) ($record->service?->duration_minutes ?? 60);

            $newDateRaw = $data['scheduled_date'] ?? null;
            $newStartRaw = $data['scheduled_start'] ?? null;

            $newDate = $newDateRaw instanceof \DateTimeInterface
                ? Carbon::instance($newDateRaw)->setTimezone($tz)->toDateString()
                : (is_string($newDateRaw) ? trim($newDateRaw) : null);

            $newStart = is_string($newStartRaw) ? trim($newStartRaw) : null;

            $oldDate = $record->scheduled_date
                ? Carbon::parse((string) $record->scheduled_date, $tz)->toDateString()
                : null;
            $oldStart = $record->scheduled_start ? (string) $record->scheduled_start : null;

            $scheduleChanged = ($newDate !== null && $newStart !== null)
                && ($newDate !== $oldDate || $newStart !== $oldStart);

            if ($scheduleChanged) {
                $scheduledAt = Carbon::parse($newDate . ' ' . $newStart, $tz);

                if (Holiday::query()->whereDate('date', $scheduledAt->toDateString())->exists()) {
                    throw ValidationException::withMessages([
                        'scheduled_date' => 'Tanggal tersebut adalah hari libur. Silakan pilih tanggal lain.',
                    ]);
                }

                $assignment = ReservationServiceHelper::findAvailableAssignment(
                    scheduledDate: $scheduledAt,
                    durationMinutes: $durationMinutes,
                    excludeAppointmentId: (int) $record->getKey(),
                );

                if (! $assignment) {
                    throw ValidationException::withMessages([
                        'scheduled_start' => 'Tidak ada dokter yang tersedia pada jam tersebut.',
                    ]);
                }

                $data['doctor_id'] = $assignment['doctor_user_id'];
                $data['room_id'] = $assignment['room_id'];
                $data['scheduled_date'] = $assignment['scheduled_date'];
                $data['scheduled_start'] = $assignment['scheduled_start'];
                $data['scheduled_end'] = $assignment['scheduled_end'];
            } else {
                // Jika jadwal tidak berubah, jangan sampai overwrite hasil assignment existing.
                unset($data['doctor_id'], $data['room_id'], $data['scheduled_end']);
            }

            $record->update($data);
            $record->refresh();


            return $record;
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Gagal memperbarui appointment')
                ->body('Periksa input Anda, terutama jadwal.')
                ->danger()
                ->send();

            throw $e;
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Terjadi kesalahan')
                ->body('Tidak dapat memperbarui appointment.')
                ->danger()
                ->send();

            throw $e;
        }
    }


}
