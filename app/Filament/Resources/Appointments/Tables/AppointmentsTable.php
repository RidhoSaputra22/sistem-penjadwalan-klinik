<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('patient.name')
                    ->label('Pasien')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Pelayanan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('room.name')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('scheduled_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('scheduled_start')
                    ->label('Waktu Mulai')
                    ->time()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('scheduled_end')
                    ->label('Waktu Selesai')
                    ->time()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statu')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
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
            ]);
    }
}
