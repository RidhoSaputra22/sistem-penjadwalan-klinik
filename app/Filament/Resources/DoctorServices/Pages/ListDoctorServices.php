<?php

namespace App\Filament\Resources\DoctorServices\Pages;

use App\Filament\Resources\DoctorServices\DoctorServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDoctorServices extends ListRecords
{
    protected static string $resource = DoctorServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
