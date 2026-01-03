<?php

namespace Database\Factories;

use App\Enums\SettingKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        $key = fake()->randomElement([
            SettingKey::CLINIC_NAME,
            SettingKey::DEFAULT_APPOINTMENT_DURATION,
            SettingKey::WORKING_DAYS,
        ]);

        $value = match ($key) {
            SettingKey::CLINIC_NAME => fake()->company(),
            SettingKey::DEFAULT_APPOINTMENT_DURATION => (string) fake()->randomElement([15, 20, 30, 45, 60]),
            SettingKey::WORKING_DAYS => json_encode(fake()->randomElements(
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                fake()->numberBetween(4, 6)
            )),
        };

        return [
            'key' => $key,
            'value' => $value,
        ];
    }
}
