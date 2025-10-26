<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HolidayType: string implements HasLabel
{
    case FULL_DAY = 'full_day';
    case HALF_DAY = 'half_day';

    public function getLabel(): string
    {
        return match ($this) {
            self::FULL_DAY => 'Libur Sehari Penuh',
            self::HALF_DAY => 'Libur Setengah Hari',
        };
    }
}
