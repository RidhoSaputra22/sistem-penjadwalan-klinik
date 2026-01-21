<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Priority>
 */
class PriorityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'color' => fake()->hexColor(),
            'level' => fake()->unique()->numberBetween(1, 200),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
