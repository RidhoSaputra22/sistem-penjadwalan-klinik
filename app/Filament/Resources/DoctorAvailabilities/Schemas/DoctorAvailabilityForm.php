<?php

namespace App\Filament\Resources\DoctorAvailabilities\Schemas;

use App\Enums\WeekdayEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DoctorAvailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('weekday')
                    ->options(WeekdayEnum::class)
                    ->native(false)
                    ->required(),
                TimePicker::make('start_time')
                    ->native(false)
                    ->default('08:00')
                    ->required(),
                TimePicker::make('end_time')
                    ->native(false)
                    ->default('08:00')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
