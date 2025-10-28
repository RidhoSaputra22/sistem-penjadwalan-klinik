<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class UserForm
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
                    ->unique(ignoreRecord: true)
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
