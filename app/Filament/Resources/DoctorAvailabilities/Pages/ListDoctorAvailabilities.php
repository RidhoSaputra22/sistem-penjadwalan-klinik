<?php

namespace App\Filament\Resources\DoctorAvailabilities\Pages;

use App\Enums\WeekdayEnum;
use App\Filament\Forms\Components\FieldRangePicker;
use App\Filament\Resources\DoctorAvailabilities\DoctorAvailabilityResource;
use App\Filament\Resources\DoctorAvailabilities\Widgets\DoctorAvailabilityCalendar;
use App\Models\Doctor;
use App\Models\SesiPertemuan;
use App\Services\DoctorAvailabilityGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

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
                        ->options(Doctor::with('user')->get()->mapWithKeys(fn (Doctor $doctor) => [
                            $doctor->user->id => $doctor->user->name,
                        ])->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),
                    FieldRangePicker::make('jadwal_range')
                        ->label('Pilih Hari Kerja')
                        ->options(WeekdayEnum::class)

                        ->required(),
                    FieldRangePicker::make('time_range')
                        ->label('Pilih Jam Kerja')
                        ->options(SesiPertemuan::pluck('session_time', 'session_time')->toArray())
                        ->required()
                        ->columns(4),

                ])
                ->action(function (array $data) {

                    $generator = new DoctorAvailabilityGenerator;

                    $response = $generator->generateSchedule($data['user_id'], $data['jadwal_range'], $data['time_range']);

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

                    // refresh the page to show new data
                    $this->redirect(static::getUrl());
                })
                ->modalHeading('Buat Jadwal Dokter')
                ->modalDescription('Isi data dibawah untuk membuat jadwal'),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DoctorAvailabilityCalendar::class,
        ];
    }
}
