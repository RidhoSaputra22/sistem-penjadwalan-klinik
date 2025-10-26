<?php

namespace App\Filament\Resources\Appointments\Pages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\RoundRobinScheduler;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        //[
        //   "patient_id" => 1
        //   "service_id" => 1
        //   "doctor_id" => 4
        //   "room_id" => 2
        //   "scheduled_date" => "2025-10-27"
        //   "scheduled_start" => "08:00:00"
        //   "scheduled_end" => "04:04:56"
        //   "status" => "pending"
        //   "notes" => null
        // ]

        // dd($data);
        $scheduler = new RoundRobinScheduler;
        $record = $scheduler->schedule($data['service_id'], $data['patient_id'], $data['scheduled_date'], $data['scheduled_start'], $data['room_id'] ?? null);
        return $record;
    }
}
