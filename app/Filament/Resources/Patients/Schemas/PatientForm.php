<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Hash;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                  FileUpload::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->directory('users')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
                TextInput::make('nik')
                    ->label('NIK')
                    ->required(),
                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->native(false)
                    ->required(),
                TextInput::make('address')
                    ->label('Alamat')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ,
                DateTimePicker::make('email_verified_at')
                    ->native(false)
                    ->disabled(),

                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->required()
                    ->tel(),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->hiddenOn('edit')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])->columns(3);
    }
}
