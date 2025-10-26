<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ONGOING = 'ongoing';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Konfirmasi',
            self::CONFIRMED => 'Terkonfirmasi',
            self::ONGOING => 'Sedang Berlangsung',
            self::DONE => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::CONFIRMED => 'blue',
            self::ONGOING => 'yellow',
            self::DONE => 'green',
            self::CANCELLED => 'red',
        };
    }
}
