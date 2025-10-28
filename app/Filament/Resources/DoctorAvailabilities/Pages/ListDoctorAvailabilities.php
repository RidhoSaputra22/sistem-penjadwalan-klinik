<?php

namespace App\Filament\Resources\DoctorAvailabilities\Pages;

use App\Enums\WeekdayEnum;
use Filament\Actions\Action;

use Filament\Forms\Components\Select;

use Filament\Notifications\Notification;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\ListRecords;

use App\Services\DoctorAvailabilityGenerator;
use App\Filament\Resources\DoctorAvailabilities\DoctorAvailabilityResource;
use App\Filament\Resources\DoctorAvailabilities\Widgets\DoctorAvailabilityCalendar;

class ListDoctorAvailabilities extends ListRecords
{
    protected static string $resource = DoctorAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Buat Jadwal Dokter')
                ->label('Buat Jadwal Dokter')
                ->schema([
                    Select::make('user_id')
                        ->label('Dokter')
                        ->relationship('doctor', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('hari_awal')
                        ->label('Hari awal')
                        ->options(WeekdayEnum::class)
                        ->native(false)
                        ->required(),
                    Select::make('hari_akhir')
                        ->label('Hari akhir')
                        ->options(WeekdayEnum::class)
                        ->native(false)
                        ->required(),
                    TimePicker::make('start_time')
                        ->label('Waktu Mulai')
                        ->native(false)
                        ->default('08:00')
                        ->required(),
                    TimePicker::make('end_time')
                        ->label('Waktu Selesai')
                        ->native(false)
                        ->default('08:00')
                        ->required(),

                ])
                ->action(function (array $data) {
                    $generator = new DoctorAvailabilityGenerator;
                    $response = $generator->generateSchedule($data['user_id'], $data['hari_awal']->value, $data['hari_akhir']->value, $data['start_time'], $data['end_time']);

                    if ($response['status'] === 'success') {
                        Notification::make()
                            ->success()
                            ->title($response['message'])
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title($response['message'])
                            ->send();
                        $this->halt();
                    }
                })
                ->modalHeading('Buat Jadwal Dokter')
                ->modalDescription('Isi data dibawah untuk membuat jadwal')

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DoctorAvailabilityCalendar::class,
        ];
    }
}
