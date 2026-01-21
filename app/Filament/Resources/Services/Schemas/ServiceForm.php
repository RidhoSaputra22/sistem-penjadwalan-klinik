<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                Select::make('priority_id')
                    ->label('Prioritas')
                    ->relationship('priority', 'name')
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Prioritas')
                            ->required(),
                        TextInput::make('level')
                            ->label('Level Prioritas')
                            ->required(),
                    ])
                    ->editOptionForm([
                        TextInput::make('name')
                            ->label('Nama Prioritas')
                            ->required(),
                        TextInput::make('level')
                            ->label('Level Prioritas')
                            ->required(),
                    ])

                    ->preload(),
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
                    ->label('Warna'),
            ]);
    }
}
