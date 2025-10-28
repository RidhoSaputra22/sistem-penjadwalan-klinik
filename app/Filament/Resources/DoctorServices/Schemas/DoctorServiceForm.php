<?php

namespace App\Filament\Resources\DoctorServices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;

class DoctorServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->required()
                    ->relationship('doctor', 'name'),
                Select::make('service_id')
                    ->required()
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Pelayanan')
                            ->required(),
                        TextInput::make('duration_minutes')
                            ->label('Durasi (menit)')
                            ->required()
                            ->numeric()
                            ->default(30),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        ColorPicker::make('color')
                            ->label('Warna')
                    ])->columns(3)
                    ->createOptionModalHeading('Pasien Baru'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
