<?php

namespace App\Filament\Resources\Holidays\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Holidays\HolidayResource;
use App\Filament\Resources\Holidays\Widgets\HolydayCalendarWidget;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('Ambil Hari Libur')
                ->label('Ambil Hari Libur')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->color('primary')
                ->action(function () {
                    try {
                        // Jalankan command
                        $exitCode = Artisan::call('holidays:fetch');
                        $output = trim(Artisan::output());

                        if ($exitCode === 0) {
                            Notification::make()
                                ->title('Berhasil Mengambil Hari Libur')
                                ->body($output ?: 'Data hari libur berhasil diperbarui dari API.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal Mengambil Hari Libur')
                                ->body($output ?: 'Command gagal dijalankan. Cek log untuk detail.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HolydayCalendarWidget::class,
        ];
    }
}
