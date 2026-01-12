<?php

namespace App\Enums;

enum NotificationType: string
{
    case BookingCreated = 'booking_created';
    case BookingConfirmed = 'booking_confirmed';
    case Reminder = 'reminder';
    case Completed = 'booking_completed';
    case Cancelled = 'booking_cancelled';

    public function label(): string
    {
        return match ($this) {
            self::BookingCreated => 'Pemesanan Baru',
            self::BookingConfirmed => 'Konfirmasi Jadwal',
            self::Reminder => 'Pengingat Jadwal',
            self::Completed => 'Sesi Selesai',
            self::Cancelled => 'Pemesanan Dibatalkan',
        };
    }
}
