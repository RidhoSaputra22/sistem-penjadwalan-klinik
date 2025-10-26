<?php

namespace App\Filament\Resources\DoctorAvailabilities\Pages;

use App\Filament\Resources\DoctorAvailabilities\DoctorAvailabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDoctorAvailability extends EditRecord
{
    protected static string $resource = DoctorAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
