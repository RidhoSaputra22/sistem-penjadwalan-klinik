<?php

namespace App\Enums;

enum HolidayType: string
{
    case FULL_DAY = 'full_day';
    case HALF_DAY = 'half_day';

    public function label(): string
    {
        return match ($this) {
            self::FULL_DAY => 'Libur Sehari Penuh',
            self::HALF_DAY => 'Libur Setengah Hari',
        };
    }
}
