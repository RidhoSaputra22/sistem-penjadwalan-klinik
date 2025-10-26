<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->native(false)
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                Toggle::make('full_day')
                    ->label('Full Day')
                    ->required(),
            ]);
    }
}
