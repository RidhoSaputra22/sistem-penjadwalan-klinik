<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Schema $schema): Schema
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

                ColorPicker::make('color')
                    ->label('Warna'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->allowDuplicates()
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Layanan Baru'),
                AttachAction::make()
                    ->label('Kaitkan')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query, $livewire) => $query->whereNotExists(function ($subquery) use ($livewire) {
                        $subquery->from('doctor_services')
                            ->whereColumn('doctor_services.service_id', 'services.id')
                            ->where('doctor_services.user_id', $livewire->getOwnerRecord()->user_id);
                    })
                    )
                    ->multiple(),

            ])
            ->recordActions([
                DetachAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
