<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RrPointer>
 */
class RrPointerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'last_assigned_doctor_id' => fake()->boolean(70)
                ? User::factory()->state(fn () => ['role' => UserRole::DOCTOR])
                : null,
        ];
    }
}
