<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Enums\SettingKey;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('key')
                    ->native(false)
                    ->options(SettingKey::class)
                    ->required(),
                Textarea::make('value')
                    ->columnSpanFull(),
            ]);
    }
}
