<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Patient;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\AppointmentStatus;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

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
                    ->preload(),
                Select::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('room_id')
                    ->label('Ruangan')
                    ->relationship('room', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('scheduled_date')
                    ->label('Tanggal')
                    ->default(now())
                    ->native(false)
                    ->required(),
                TimePicker::make('scheduled_start')
                    ->label('Waktu Mulai')
                    ->default(now())
                    ->native(false)
                    ->required(),
                TimePicker::make('scheduled_end')
                    ->label('Waktu Selesai')
                    ->default(now())
                    ->native(false)
                    ->required(),
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
