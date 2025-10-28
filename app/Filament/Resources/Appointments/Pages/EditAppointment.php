<?php

namespace App\Filament\Resources\Appointments\Pages;

use Filament\Actions\DeleteAction;
use App\Services\RoundRobinScheduler;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Appointments\AppointmentResource;

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
        $scheduler = new RoundRobinScheduler;

        // casting room_id dengan benar
        $roomId = isset($data['room_id']) ? (int) $data['room_id'] : null;

        // panggil updateSchedule sesuai urutan parameter
        $response = $scheduler->updateSchedule(
            $record->id,               // appointmentId (bukan service_id)
            $data['scheduled_date'],   // date
            $data['scheduled_start'],  // startTime
            $roomId                    // roomId (nullable int)
        );


        if ($response['status'] === 'success') {
            $record = $response['data'];
        } else {
            Notification::make()
                ->warning()
                ->title($response['message'])
                ->send();
            $this->halt();
        }
        return $record;
    }
}
