<?php

namespace App\Filament\Resources\DoctorServices;

use App\Filament\Resources\DoctorServices\Pages\CreateDoctorService;
use App\Filament\Resources\DoctorServices\Pages\EditDoctorService;
use App\Filament\Resources\DoctorServices\Pages\ListDoctorServices;
use App\Filament\Resources\DoctorServices\Schemas\DoctorServiceForm;
use App\Filament\Resources\DoctorServices\Tables\DoctorServicesTable;
use App\Models\DoctorService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DoctorServiceResource extends Resource
{
    protected static ?string $model = DoctorService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;
    protected static string|\UnitEnum|null $navigationGroup = 'Pelayanan';
    protected static ?string $navigationLabel = 'Pelayanan Dokter';
    protected static ?string $pluralModelLabel = 'Pelayanan Dokter';
    protected static ?string $modelLabel = 'Pelayanan Dokter';

    protected static ?string $recordTitleAttribute = 'priority';

    public static function form(Schema $schema): Schema
    {
        return DoctorServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorServicesTable::configure($table);
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
            'index' => ListDoctorServices::route('/'),
            'create' => CreateDoctorService::route('/create'),
            'edit' => EditDoctorService::route('/{record}/edit'),
        ];
    }
}