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
                    $record->user->password = $data['password'];
                    $record->user->save();



                    if ($record->user->wasChanged('password')) {
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->user->name;
        $data['email'] = $this->record->user->email;
        $data['email_verified_at'] = $this->record->user->email_verified_at;
        $data['phone'] = $this->record->user->phone;
        $data['title'] = $this->record->user->title;
        $data['notes'] = $this->record->user->notes;

        return parent::mutateFormDataBeforeFill($data);
    }
}
