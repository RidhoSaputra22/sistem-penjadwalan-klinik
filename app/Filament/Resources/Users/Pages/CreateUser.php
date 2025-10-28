<?php

namespace App\Filament\Resources\Users\Pages;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'role' => UserRole::RECEPTIONIST->value,
            'title' => $data['title'],
            'notes' => $data['notes'],
        ]);

        return $record;
    }
}
