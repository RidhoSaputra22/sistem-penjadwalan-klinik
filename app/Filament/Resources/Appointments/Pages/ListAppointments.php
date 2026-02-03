<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Widgets\AppointmentCallendarWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('exportALLReport')
                ->label('Export Laporan')
                ->url(route('reports.booking.all.booking.pdf'))
                ->openUrlInNewTab()
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->button(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentCallendarWidget::class,
        ];
    }
}
