<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Room;
use App\Models\Appointment;

class CodeGenerator
{
    /**
     * Generate Appointment Code
     * Format: AP-YYYYMMDD-XXXX
     */
    public static function appointment(): string
    {
        $today = Carbon::now()->format('Ymd');
        $count = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        $threeRandomLetter = strtoupper(Str::random(3));
        return "AP-{$today}-{$sequence}-{$threeRandomLetter}";
    }

    /**
     * Generate Patient Code
     * Format: PT-YYYYMMDD-XXXX
     */
    public static function patient(): string
    {
        $today = Carbon::now()->format('Ymd');
        $count = Patient::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "PT-{$today}-{$sequence}";
    }

    /**
     * Generate Doctor/User Code
     * Format: DR-YYYYMMDD-XXXX
     */
    public static function user(string $role = 'doctor'): string
    {
        $prefix = match ($role) {
            'doctor' => 'DR',
            'receptionist' => 'RC',
            'admin' => 'AD',
            default => 'US',
        };

        $today = Carbon::now()->format('Ymd');
        $count = User::where('role', $role)->whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$today}-{$sequence}";
    }

    /**
     * Generate Service Code
     * Format: SV-YYYYMMDD-XXXX
     */
    public static function service(): string
    {
        $today = Carbon::now()->format('Ymd');
        $count = Service::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "SV-{$today}-{$sequence}";
    }

    /**
     * Generate Room Code
     * Format: RM-YYYYMMDD-XXXX
     */
    public static function room(): string
    {
        $today = Carbon::now()->format('Ymd');
        $count = Room::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "RM-{$today}-{$sequence}";
    }

    /**
     * Generate Random Backup Code (for generic use)
     * Example: XX-20251026-AB12
     */
    public static function random(string $prefix = 'XX'): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$random}";
    }
}
