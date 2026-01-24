<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Category::factory()->create([
            'name' => 'Poli Ortodontik',
        ]);

        Category::factory()->create([
            'name' => 'Konsultasi Ortodontik',
        ]);

        $services = [
            // =======================
            // PEMASANGAN BEHEL
            // =======================
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Metal',
                'slug' => 'behel-metal',
                'code' => 'ORTHO-METAL',
                'duration_minutes' => 90,
                'price' => 9000000,
                'description' => 'Perawatan ortodonti menggunakan behel metal konvensional.',
                'color' => '#9E9E9E',
            ],
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Ceramic',
                'slug' => 'behel-ceramic',
                'code' => 'ORTHO-CERAMIC',
                'duration_minutes' => 90,
                'price' => 14000000,
                'description' => 'Perawatan ortodonti menggunakan behel ceramic yang lebih estetik.',
                'color' => '#E0E0E0',
            ],
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Damon Metal',
                'slug' => 'behel-damon-metal',
                'code' => 'ORTHO-DAMON-METAL',
                'duration_minutes' => 90,
                'price' => 19000000,
                'description' => 'Perawatan ortodonti self-ligating Damon metal.',
                'color' => '#607D8B',
            ],
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Damon Clear',
                'slug' => 'behel-damon-clear',
                'code' => 'ORTHO-DAMON-CLEAR',
                'duration_minutes' => 90,
                'price' => 22000000,
                'description' => 'Perawatan ortodonti Damon Clear berbahan ceramic transparan.',
                'color' => '#CFD8DC',
            ],
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Damon Ultima',
                'slug' => 'behel-damon-ultima',
                'code' => 'ORTHO-DAMON-ULTIMA',
                'duration_minutes' => 90,
                'price' => 23000000,
                'description' => 'Perawatan ortodonti Damon Ultima dengan teknologi terbaru.',
                'color' => '#455A64',
            ],
            [
                'category_id' => 1,
                'name' => 'Pemasangan Behel Self Ligating Metal',
                'slug' => 'behel-self-ligating-metal',
                'code' => 'ORTHO-SL-METAL',
                'duration_minutes' => 90,
                'price' => 14000000,
                'description' => 'Perawatan ortodonti self-ligating metal.',
                'color' => '#B0BEC5',
            ],

            // =======================
            // KONTROL BEHEL
            // =======================
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Metal',
                'slug' => 'kontrol-behel-metal',
                'code' => 'ORTHO-KONTROL-METAL',
                'duration_minutes' => 20,
                'price' => 200000,
                'description' => 'Kontrol rutin behel metal: evaluasi dan penyesuaian kawat.',
                'color' => '#9E9E9E',
            ],
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Ceramic',
                'slug' => 'kontrol-behel-ceramic',
                'code' => 'ORTHO-KONTROL-CERAMIC',
                'duration_minutes' => 20,
                'price' => 250000,
                'description' => 'Kontrol rutin behel ceramic.',
                'color' => '#E0E0E0',
            ],
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Damon Metal',
                'slug' => 'kontrol-behel-damon-metal',
                'code' => 'ORTHO-KONTROL-DAMON-METAL',
                'duration_minutes' => 20,
                'price' => 200000,
                'description' => 'Kontrol rutin behel Damon metal.',
                'color' => '#607D8B',
            ],
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Damon Clear',
                'slug' => 'kontrol-behel-damon-clear',
                'code' => 'ORTHO-KONTROL-DAMON-CLEAR',
                'duration_minutes' => 20,
                'price' => 250000,
                'description' => 'Kontrol rutin behel Damon Clear.',
                'color' => '#CFD8DC',
            ],
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Damon Ultima',
                'slug' => 'kontrol-behel-damon-ultima',
                'code' => 'ORTHO-KONTROL-DAMON-ULTIMA',
                'duration_minutes' => 20,
                'price' => 250000,
                'description' => 'Kontrol rutin behel Damon Ultima.',
                'color' => '#455A64',
            ],
            [
                'category_id' => 1,
                'name' => 'Kontrol Behel Self Ligating Metal',
                'slug' => 'kontrol-behel-self-ligating-metal',
                'code' => 'ORTHO-KONTROL-SL-METAL',
                'duration_minutes' => 20,
                'price' => 200000,
                'description' => 'Kontrol rutin behel self-ligating metal.',
                'color' => '#B0BEC5',
            ],
        ];

        foreach ($services as $serviceData) {
            \App\Models\Service::create($serviceData);
        }

    }
}
