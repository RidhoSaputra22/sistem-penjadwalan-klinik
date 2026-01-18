<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;


    protected function handleRecordCreation(array $data): Model
    {


        $record = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'role' => UserRole::RECEPTIONIST->value,
            'title' => $data['title'] ?? '-',
            'notes' => $data['notes'],
        ]);

        return $record;
    }
}
