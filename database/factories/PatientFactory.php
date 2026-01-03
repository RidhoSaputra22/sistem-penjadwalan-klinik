<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nik' => fake()->optional()->numerify('################'),
            'birth_date' => fake()->optional()->date(),
            'phone' => fake()->optional()->numerify('08##########'),
            'address' => fake()->optional()->address(),
        ];
    }
}
