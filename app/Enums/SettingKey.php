<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SettingKey: string implements HasLabel
{
    case CLINIC_NAME = 'clinic_name';
    case DEFAULT_APPOINTMENT_DURATION = 'default_appointment_duration';
    case WORKING_DAYS = 'working_days';

    public function getLabel(): string
    {
        return match ($this) {
            self::CLINIC_NAME => 'Nama Klinik',
            self::DEFAULT_APPOINTMENT_DURATION => 'Durasi Default Janji (menit)',
            self::WORKING_DAYS => 'Hari Kerja Klinik',
        };
    }
}
