<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory()->state(fn () => [
                'name' => 'Dr. ' . $name,
                'role' => UserRole::DOCTOR,
                'title' => fake()->optional()->randomElement(['dr.', 'dr., Sp.KJ', 'Sp.A', 'Sp.PD', 'Sp.OG']),
                'notes' => fake()->optional()->sentence(),
            ]),
            'slug' => fake()->unique()->slug(2) . '-' . Str::lower(Str::random(4)),
            'sip_number' => fake()->optional()->bothify('SIP-####-####'),
            'str_number' => fake()->optional()->bothify('STR-####-####'),
            'specialization' => fake()->randomElement([
                'Kedokteran Umum',
                'Psikiatri',
                'Penyakit Dalam',
                'Anak',
                'Kandungan',
            ]),
            'gender' => fake()->optional()->randomElement(['male', 'female']),
            'birth_date' => fake()->optional()->date(),
            'address' => fake()->optional()->address(),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
