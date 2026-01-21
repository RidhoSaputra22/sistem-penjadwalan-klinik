<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['level' => 1, 'name' => 'Rendah', 'color' => '#6B7280'],
            ['level' => 2, 'name' => 'Normal', 'color' => '#3B82F6'],
            ['level' => 3, 'name' => 'Tinggi', 'color' => '#F59E0B'],
            ['level' => 4, 'name' => 'Darurat', 'color' => '#EF4444'],
        ];

        foreach ($rows as $row) {
            $priority = Priority::query()->updateOrCreate(
                ['level' => $row['level']],
                ['name' => $row['name'], 'color' => $row['color']]
            );

            Service::factory()
                ->count(5)
                ->create(['priority_id' => $priority->id]);
        }
    }
}
