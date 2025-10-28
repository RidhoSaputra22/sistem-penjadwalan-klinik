<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ColorEnum;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->disabled()
                    ->label('Kode'),
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
            ]);
    }
}
