<?php

namespace App\Filament\Resources\DoctorAvailabilities;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\DoctorAvailability;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\DoctorAvailabilities\Pages\EditDoctorAvailability;
use App\Filament\Resources\DoctorAvailabilities\Pages\CreateDoctorAvailability;
use App\Filament\Resources\DoctorAvailabilities\Pages\ListDoctorAvailabilities;
use App\Filament\Resources\DoctorAvailabilities\Schemas\DoctorAvailabilityForm;
use App\Filament\Resources\DoctorAvailabilities\Tables\DoctorAvailabilitiesTable;
use App\Filament\Resources\DoctorAvailabilities\Widgets\DoctorAvailabilityCalendar;

class DoctorAvailabilityResource extends Resource
{


    protected static ?string $model = DoctorAvailability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;
    protected static string|\UnitEnum|null $navigationGroup = 'Dokter';
    protected static ?string $navigationLabel = 'Jadwal Dokter';
    protected static ?string $pluralModelLabel = 'Jadwal Dokter';
    protected static ?string $modelLabel = 'Jadwal Dokter';


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
