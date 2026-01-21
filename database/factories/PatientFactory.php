<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nik' => fake()->optional()->numerify('################'),
            'medical_record_number' => fake()->numerify('################'),
            'birth_date' => fake()->optional()->date(),
            'address' => fake()->optional()->address(),

        ];
    }
}
