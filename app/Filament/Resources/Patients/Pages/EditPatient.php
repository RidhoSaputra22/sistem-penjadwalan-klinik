<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

use Illuminate\Support\Facades\Hash;


class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;

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

                    if ($record->user->wasChanged()) {
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
        $user = User::findOrFail($data['user_id']);
        $data['photo'] = $user->photo;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone;
        $data['title'] = $user->title;
        $data['notes'] = $user->notes;



        return parent::mutateFormDataBeforeFill($data);

    }
}
