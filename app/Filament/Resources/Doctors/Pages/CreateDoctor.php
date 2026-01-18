<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Models\Doctor;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);

        $record = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'role' => UserRole::DOCTOR->value,
            'title' => $data['title'],
            'notes' => $data['notes'],
        ]);

        $doctor = Doctor::create([
            'user_id' => $record->id,
        ]);

        // dd($doctor);

        return $doctor;
    }
}
