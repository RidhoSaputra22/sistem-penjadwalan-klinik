<?php

namespace App\Filament\Resources\Doctors\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Doctors\DoctorResource;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('Buat password baru')
                ->label('Buat password baru')
                ->icon(Heroicon::OutlinedKey)
                ->modalHeading('Buat password baru')
                ->modalDescription('Buat password baru dengan mengisi password baru')
                ->schema([
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->hiddenOn('edit')
                        ->suffixIcon('heroicon-s-lock-closed')
                        ->columnSpanFull(),
                ])
                ->action(function ($record, array $data): void {
                    $record->password = $data['password'];
                    $record->save();

                    if ($record->wasChanged()) {
                        Notification::make()
                            ->success()
                            ->title('Password berhasil diubah')
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('Password gagal diubah')
                            ->send();
                    }
                })


        ];
    }
}
