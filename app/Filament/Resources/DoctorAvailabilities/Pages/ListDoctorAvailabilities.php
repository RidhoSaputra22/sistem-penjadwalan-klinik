<?php

namespace App\Filament\Resources\DoctorAvailabilities\Pages;

use App\Filament\Resources\DoctorAvailabilities\DoctorAvailabilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDoctorAvailabilities extends ListRecords
{
    protected static string $resource = DoctorAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
