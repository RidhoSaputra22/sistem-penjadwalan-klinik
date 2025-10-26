<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->native(false)
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->native(false),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->required()
                    ->tel(),
                Select::make('role')
                    ->label('Role')
                    ->options(UserRole::class)
                    ->default('doctor')
                    ->required(),
                TextInput::make('title')
                    ->label('Title'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
