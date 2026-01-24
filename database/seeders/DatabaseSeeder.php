<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\WeekdayEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Patient;
use App\Models\Room;
use App\Models\SesiPertemuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(ServiceSeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role' => UserRole::ADMIN,

        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('user'),
            'role' => UserRole::PATIENT,
        ]);

        User::factory()->create([
            'name' => 'Ridho',
            'email' => 'saputra22022@gmail.com',
            'password' => Hash::make('ridho123123'),
            'role' => UserRole::PATIENT,
        ]);

        Room::factory()
            ->count(5)
            ->create();

        $doctor = Doctor::factory()
            ->count(1)

            ->create()
            ->first();

        $rangeWeekDay = WeekdayEnum::cases();
        $rangeSession = SesiPertemuan::all()->toArray();
        foreach ($rangeWeekDay as $day) {
            DoctorAvailability::create([
                'user_id' => $doctor->user_id,
                'weekday' => $day->value,
                'start_time' => $rangeSession[0]['session_time'],
                'end_time' => $rangeSession[count($rangeSession) - 1]['session_time'],

            ]);
        }

        Patient::factory()
            ->count(20)
            ->create();

        // Appointment::factory()
        //     ->count(50)
        //     ->hasService(1)
        //     ->create(
        //         [
        //             'scheduled_date' => Carbon::now(),

        //         ]
        //     );

        // $this->call(DoctorSeeder::class);
    }
}
