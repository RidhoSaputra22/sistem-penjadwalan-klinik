<?php

namespace App\Filament\Resources\DoctorServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->relationship('service', 'name'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
