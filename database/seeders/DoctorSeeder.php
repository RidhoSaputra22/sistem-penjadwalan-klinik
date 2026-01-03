<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Doctor::factory()
        ->count(1)
        ->hasServices(3)
        ->hasDoctorAvailabilities(5)


        ->create();
    }
}
