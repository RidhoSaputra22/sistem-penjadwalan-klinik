<?php

namespace App\Filament\Resources\DoctorServices\Pages;

use App\Filament\Resources\DoctorServices\DoctorServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDoctorService extends EditRecord
{
    protected static string $resource = DoctorServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
