<?php

namespace App\Enums;

enum UserRole: string
{
    case DOCTOR = 'doctor';
    case RECEPTIONIST = 'receptionist';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::DOCTOR => 'Dokter',
            self::RECEPTIONIST => 'Resepsionis',
            self::ADMIN => 'Admin',
        };
    }
}
