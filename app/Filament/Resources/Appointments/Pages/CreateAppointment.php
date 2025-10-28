<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Services\RoundRobinScheduler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Appointments\AppointmentResource;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = null;
        $scheduler = new RoundRobinScheduler;
        $response = $scheduler->schedule($data['service_id'], $data['patient_id'], $data['scheduled_date'], $data['scheduled_start'], $data['room_id'] ?? null);

        if ($response['status'] === 'success') {
            $record = $response['data'];;
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
