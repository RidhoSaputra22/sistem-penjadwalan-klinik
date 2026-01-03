<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorService>
 */
class DoctorServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(fn () => ['role' => UserRole::DOCTOR]),
            'service_id' => Service::factory(),
            'priority' => fake()->numberBetween(0, 10),
        ];
    }
}
