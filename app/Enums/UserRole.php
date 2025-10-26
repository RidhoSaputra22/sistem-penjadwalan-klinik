<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case DOCTOR = 'doctor';
    case RECEPTIONIST = 'receptionist';
    case ADMIN = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::DOCTOR => 'Dokter',
            self::RECEPTIONIST => 'Resepsionis',
            self::ADMIN => 'Admin',
        };
    }
}
