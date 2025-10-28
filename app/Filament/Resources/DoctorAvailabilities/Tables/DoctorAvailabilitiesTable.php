<?php

namespace App\Filament\Resources\DoctorAvailabilities\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Grouping\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class DoctorAvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('weekday')
                    ->label('Hari')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('start_time')
                    ->label('Waktu Mulai')
                    ->time()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('end_time')
                    ->label('Waktu Selesai')
                    ->time()
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('doctor.name')
                    ->label('Dokter')
                    ->collapsible(),
            ])
            ->defaultGroup('doctor.name')
            ->defaultSort('weekday', 'asc');
    }
}
