<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Widgets\AppointmentCallendarWidget;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
            ActionGroup::make([
                Action::make('exportAWTReport')
                    ->label('Export Laporan AWT')
                    ->url(route('reports.booking.awt.booking.pdf'))
                    ->openUrlInNewTab(),
                Action::make('exportTATReport')
                    ->label('Export Laporan TAT')
                    ->url(route('reports.booking.tat.booking.pdf'))
                    ->openUrlInNewTab(),
                Action::make('exportALLReport')
                    ->label('Export Laporan ALL')
                    ->url(route('reports.booking.all.booking.pdf'))
                    ->openUrlInNewTab(),
            ])
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->label('Export Laporan')
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
