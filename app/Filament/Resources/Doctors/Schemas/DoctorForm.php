<?php

namespace App\Filament\Resources\Doctors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->unique(table: 'users', column: 'email', ignorable: fn ($record) => $record?->user)
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->native(false)
                    ->disabled(),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->required()
                    ->tel(),
                TextInput::make('title')
                    ->label('Title'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->hiddenOn('edit')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),

            ])->columns(3);
    }
}
