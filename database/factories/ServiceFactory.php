<?php

namespace Database\Factories;

use App\Helpers\CodeGenerator;
use App\Models\Category;
use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {

        $serviceNames = [
            'Konsultasi Ortodonti',
            'Pemeriksaan Gigi dan Mulut',
            'Pemasangan Behel',
            'Kontrol dan Penyesuaian Behel',
            'Pencetakan Gigi (Cetakan / Scan)',
            'Pembersihan Karang Gigi',
            'Penambalan Gigi',
            'Pencabutan Gigi',
            'Perawatan Saluran Akar',
            'Pemasangan Retainer',
        ];

        $name = fake()->randomElement($serviceNames);

        return [
            'category_id' => Category::factory(),
            'priority_id' => Priority::query()->inRandomOrder()->value('id') ?? Priority::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'code' => CodeGenerator::service(),
            'duration_minutes' => fake()->randomElement([15, 20, 30, 45, 60, 90]),
            'price' => fake()->numberBetween(25000, 500000),
            'description' => fake()->optional()->sentence(),
            'photo' => null,
            'color' => fake()->optional()->safeHexColor(),
        ];
    }
}
