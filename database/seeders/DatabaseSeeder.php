<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
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

        $this->call(PrioritySeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),

        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('user'),
        ]);

        // Category::factory()
        //     ->count(1)
        //     ->hasServices(1)
        //     ->create();

        Room::factory()
            ->count(5)
            ->create();

        Doctor::factory()
            ->count(1)
            ->hasServices(1)
            ->create();

        Patient::factory()
            ->count(20)
            ->create();

        // Appointment::factory()
        //     ->count(50)
        //     ->hasService(1)
        //     ->create();

        // $this->call(DoctorSeeder::class);
    }
}
