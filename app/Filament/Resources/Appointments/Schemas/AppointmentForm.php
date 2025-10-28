<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\AppointmentStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\Patients\PatientResource;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->disabled(),
                Select::make('patient_id')
                    ->label('Pasien')
                    ->relationship('patient', 'name')
                    ->required()
                    ->searchable()
                    ->createOptionModalHeading('Pasien Baru')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('nik'),
                        DatePicker::make('birth_date'),
                        TextInput::make('phone')
                            ->tel(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                    ])
                    ->preload(),
                Select::make('service_id')
                    ->label('Pelayanan')
                    ->relationship('service', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit')
                    ->reactive() // Penting agar bisa trigger perubahan
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Ambil waktu mulai & service terpilih
                        $start = $get('scheduled_start');
                        if ($start && $state) {
                            $service = Service::find($state);
                            if ($service && $service->duration_minutes) {
                                $end = Carbon::parse($start)->addMinutes($service->duration_minutes)->format('H:i:s');
                                $set('scheduled_end', $end); // update otomatis tanpa reload
                            }
                        }
                    }),
                Select::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->disabled()
                    ->searchable()
                    ->preload(),
                Select::make('room_id')
                    ->label('Ruangan')
                    ->relationship('room', 'name')
                    ->disabled()
                    ->searchable()
                    ->preload(),
                DatePicker::make('scheduled_date')
                    ->label('Tanggal')
                    ->default(now())
                    ->native(false)
                    ->minDate(now())
                    ->required(),
                TimePicker::make('scheduled_start')
                    ->label('Waktu Mulai')
                    ->default('08:00:00')
                    ->native(false)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $serviceId = $get('service_id');
                        if ($state && $serviceId) {
                            $service = Service::find($serviceId);
                            if ($service && $service->duration_minutes) {
                                $end = Carbon::parse($state)->addMinutes($service->duration_minutes)->format('H:i:s');
                                $set('scheduled_end', $end);
                            }
                        }
                    }),
                TimePicker::make('scheduled_end')
                    ->label('Waktu Selesai')
                    ->native(false)
                    ->disabled()
                    ->dehydrated(true), // tetap dikirim ke DB walau disabled
                Select::make('status')
                    ->label('Status')
                    ->native(false)
                    ->default(AppointmentStatus::PENDING)
                    ->options(AppointmentStatus::class)
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
