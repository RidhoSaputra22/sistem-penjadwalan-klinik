<?php

namespace App\Filament\Resources\DoctorAvailabilities;

use App\Filament\Resources\DoctorAvailabilities\Pages\CreateDoctorAvailability;
use App\Filament\Resources\DoctorAvailabilities\Pages\EditDoctorAvailability;
use App\Filament\Resources\DoctorAvailabilities\Pages\ListDoctorAvailabilities;
use App\Filament\Resources\DoctorAvailabilities\Schemas\DoctorAvailabilityForm;
use App\Filament\Resources\DoctorAvailabilities\Tables\DoctorAvailabilitiesTable;
use App\Models\DoctorAvailability;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DoctorAvailabilityResource extends Resource
{
    protected static ?string $model = DoctorAvailability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static string|\UnitEnum|null $navigationGroup = 'Penjadwalan';
    protected static ?string $navigationLabel = 'Dokter Tersedia';
    protected static ?string $pluralModelLabel = 'Dokter Tersedia';
    protected static ?string $modelLabel = 'Dokter Tersedia';


    public static function form(Schema $schema): Schema
    {
        return DoctorAvailabilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorAvailabilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctorAvailabilities::route('/'),
            'create' => CreateDoctorAvailability::route('/create'),
            'edit' => EditDoctorAvailability::route('/{record}/edit'),
        ];
    }
}