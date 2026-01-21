<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Appointments', Appointment::where('created_at', '>=', now()->subDay())->where('status', AppointmentStatus::CONFIRMED)->count())
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->label('Total reservasi hari ini')
                ->description(now()->format('d F Y')),
            Stat::make('Total Pasien', Patient::count())
                ->icon('heroicon-o-users')
                ->color('primary')
                ->label('Total Pasien')
                ->description('Terakhir di update'.' '.now()->format('d F Y')),
            Stat::make('Total Dokter', Doctor::count())
                ->icon('heroicon-o-users')
                ->color('primary')
                ->label('Total Dokter')
                ->description('Terakhir di update'.' '.now()->format('d F Y')),
        ];
    }
}
