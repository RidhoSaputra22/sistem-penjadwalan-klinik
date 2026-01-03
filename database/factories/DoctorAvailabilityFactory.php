<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\WeekdayEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorAvailability>
 */
class DoctorAvailabilityFactory extends Factory
{
    public function definition(): array
    {
        $start = Carbon::createFromTime(fake()->numberBetween(7, 14), fake()->randomElement([0, 30]), 0);
        $durationMinutes = fake()->randomElement([60, 90, 120, 180, 240]);
        $end = (clone $start)->addMinutes($durationMinutes);

        return [
            'user_id' => User::factory()->state(fn () => ['role' => UserRole::DOCTOR]),
            'weekday' => fake()->randomElement([
                WeekdayEnum::SUNDAY,
                WeekdayEnum::MONDAY,
                WeekdayEnum::TUESDAY,
                WeekdayEnum::WEDNESDAY,
                WeekdayEnum::THURSDAY,
                WeekdayEnum::FRIDAY,
                WeekdayEnum::SATURDAY,
            ]),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'is_active' => fake()->boolean(90),
        ];
    }
}
