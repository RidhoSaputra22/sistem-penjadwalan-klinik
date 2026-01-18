<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\AppointmentStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\SesiPertemuan;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->disabled()
                    ->visibleOn('edit'),
                Select::make('patient_id')
                    ->label('Pasien')
                    ->options(User::where('role', UserRole::PATIENT->value)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->createOptionModalHeading('Pasien Baru')

                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('nik'),
                        TextInput::make('email')
                            ->email(),

                        DatePicker::make('birth_date')
                         ->native(false),
                        TextInput::make('phone')
                            ->tel(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data): User {
                        $user = User::create([
                            'name' => $data['name'],
                            'role' => UserRole::PATIENT,
                            'phone' => $data['phone'] ?? null,
                            'email' => $data['email'] ?? null,
                            'password' => bcrypt('password'), // default password
                        ]);

                        $patient = Patient::create([
                            'user_id' => $user->id,
                            'nik' => $data['nik'] ?? null,
                            'birth_date' => $data['birth_date'] ?? null,
                            'address' => $data['address'] ?? null,
                        ]);

                        return $user;
                    })
                    ->preload(),
                Select::make('service_id')
                    ->label('Nama Layanan')
                    ->relationship('service', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit')
                    ->reactive() // Penting agar bisa trigger perubahan
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Ambil waktu mulai & service terpilih
                        $start = $get('scheduled_start');
                        if ($start && $state) {
                            $service = Service::find($state);
                            if ($service && $service->duration_minutes) {
                                $end = Carbon::parse($start)->addMinutes($service->duration_minutes)->format('H:i:s');
                                $set('scheduled_end', $end); // update otomatis tanpa reload
                            }
                        }
                    }),
                Select::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->disabled()
                    ->searchable()
                    ->visibleOn('edit')
                    ->preload(),
                Select::make('room_id')
                    ->label('Ruangan')
                    ->relationship('room', 'name')
                    ->disabled()
                    ->searchable()
                    ->visibleOn('edit')
                    ->preload(),
                DatePicker::make('scheduled_date')
                    ->label('Tanggal')
                    ->default(now()->addDays(1))
                    ->native(false)
                    ->minDate(now())
                    ->required(),
                Select::make('scheduled_start')
                    ->label('Waktu Mulai')

                    ->options(SesiPertemuan::pluck('session_time', 'session_time'))
                    ->native(false)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $serviceId = $get('service_id');
                        if ($state && $serviceId) {
                            $service = Service::find($serviceId);
                            if ($service && $service->duration_minutes) {
                                $end = Carbon::parse($state)->addMinutes($service->duration_minutes)->format('H:i:s');
                                $set('scheduled_end', $end);
                            }
                        }
                    }),
                TimePicker::make('scheduled_end')
                    ->label('Waktu Selesai')
                    ->native(false)
                    ->disabled()
                    ->visibleOn('edit')
                    ->dehydrated(true), // tetap dikirim ke DB walau disabled
                Select::make('status')
                    ->label('Status')
                    ->native(false)
                    ->visibleOn('edit')
                    ->default(AppointmentStatus::PENDING)
                    ->options(AppointmentStatus::class)
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
