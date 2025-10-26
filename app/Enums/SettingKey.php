<?php

namespace App\Enums;

enum SettingKey: string
{
    case CLINIC_NAME = 'clinic_name';
    case DEFAULT_APPOINTMENT_DURATION = 'default_appointment_duration';
    case WORKING_DAYS = 'working_days';

    public function label(): string
    {
        return match ($this) {
            self::CLINIC_NAME => 'Nama Klinik',
            self::DEFAULT_APPOINTMENT_DURATION => 'Durasi Default Janji (menit)',
            self::WORKING_DAYS => 'Hari Kerja Klinik',
        };
    }
}
