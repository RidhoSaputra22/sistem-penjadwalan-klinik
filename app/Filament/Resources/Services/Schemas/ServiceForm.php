<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ColorEnum;
use App\Models\Category;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
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
                   FileUpload::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->directory('services')
                    ->image()
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required(),
                    ]),
                TextInput::make('name')
                    ->label('Nama Layanan')
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label('Durasi (menit)')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('price')
                    ->label('Harga')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                ColorPicker::make('color')
                    ->label('Warna')
            ]);
    }
}
