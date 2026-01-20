<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\ReservationService;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
        $reservationService = new ReservationService();

        $userId = isset($data['patient_id']) ? (int) $data['patient_id'] : 0;
        $user = User::query()->findOrFail($userId);

        $rawScheduledDate = $data['scheduled_date'] ?? '';
        $scheduledDate = $rawScheduledDate instanceof \DateTimeInterface
            ? Carbon::instance($rawScheduledDate)->toDateString()
            : (string) $rawScheduledDate;
        $scheduledStart = (string) ($data['scheduled_start'] ?? '');

        $scheduledAt = Carbon::parse(trim($scheduledDate . ' ' . $scheduledStart), 'Asia/Makassar');

        $result = $reservationService->createReservation([
            'user_id' => $user->id,
            'name' => (string) $user->name,
            'email' => $user->email,
            'phone' => (string) ($user->phone ?? ''),
            'service_id' => (int) $data['service_id'],
            'scheduled_date' => $scheduledAt,
        ]);

        $booking = $result['booking'] ?? null;

        $reservationService->processReservation($booking);

        if (! $booking instanceof Model) {
            throw new \RuntimeException('Gagal membuat reservasi.');
        }

        if (! empty($data['notes'])) {
            $booking->update(['notes' => $data['notes']]);
            $booking->refresh();
        }



        return $booking;

        }catch (\Exception $e) {
            Notification::make()
                ->title('Error creating appointment: ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
