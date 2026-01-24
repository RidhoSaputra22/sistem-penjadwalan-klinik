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
            [
                'category_id' => 1, // Poli Ortodontik

                'name' => 'Pemasangan Behel Metal',
                'slug' => 'behel-metal',
                'code' => 'ORTHO-METAL',
                'duration_minutes' => 90,
                'price' => 9000000,
                'description' => 'Perawatan ortodonti menggunakan behel metal konvensional. Alur tindakan: konsultasi awal, rontgen gigi, scaling dan tambal bila diperlukan, foto profil dan cetak gigi, kemudian pemasangan behel. Kontrol rutin setiap bulan dengan biaya kontrol Rp200.000. Harga sudah termasuk treatment plan dan foto intra & ekstra.',

                'color' => '#9E9E9E',
            ],
            [
                'category_id' => 1,

                'name' => 'Pemasangan Behel Ceramic',
                'slug' => 'behel-ceramic',
                'code' => 'ORTHO-CERAMIC',
                'duration_minutes' => 90,
                'price' => 14000000,
                'description' => 'Perawatan ortodonti menggunakan behel ceramic yang lebih estetik. Prosedur meliputi konsultasi, rontgen, perawatan awal (scaling/tambal bila perlu), foto gigi, dan pemasangan. Biaya kontrol Rp250.000 per kunjungan.',

                'color' => '#E0E0E0',
            ],
            [
                'category_id' => 1,

                'name' => 'Pemasangan Behel Damon Metal',
                'slug' => 'behel-damon-metal',
                'code' => 'ORTHO-DAMON-METAL',
                'duration_minutes' => 90,
                'price' => 19000000,
                'description' => 'Perawatan ortodonti self-ligating Damon metal. Menggunakan sistem modern dengan gesekan rendah sehingga lebih nyaman. Tahapan: konsultasi, rontgen, perawatan pendukung, foto gigi, dan pemasangan. Biaya kontrol Rp200.000.',

                'color' => '#607D8B',
            ],
            [
                'category_id' => 1,

                'name' => 'Pemasangan Behel Damon Clear',
                'slug' => 'behel-damon-clear',
                'code' => 'ORTHO-DAMON-CLEAR',
                'duration_minutes' => 90,
                'price' => 22000000,
                'description' => 'Perawatan ortodonti Damon Clear berbahan ceramic transparan. Prosedur meliputi konsultasi, rontgen, perawatan awal, foto profil gigi, dan pemasangan. Biaya kontrol Rp250.000 per kunjungan.',

                'color' => '#CFD8DC',
            ],
            [
                'category_id' => 1,

                'name' => 'Pemasangan Behel Damon Ultima',
                'slug' => 'behel-damon-ultima',
                'code' => 'ORTHO-DAMON-ULTIMA',
                'duration_minutes' => 90,
                'price' => 23000000,
                'description' => 'Perawatan ortodonti Damon Ultima dengan teknologi terbaru untuk efisiensi perawatan. Alur tindakan: konsultasi, rontgen, scaling/tambal bila perlu, foto gigi, dan pemasangan. Biaya kontrol Rp250.000.',

                'color' => '#455A64',
            ],
            [
                'category_id' => 1,

                'name' => 'Pemasangan Behel Self Ligating Metal',
                'slug' => 'behel-self-ligating-metal',
                'code' => 'ORTHO-SL-METAL',
                'duration_minutes' => 90,
                'price' => 14000000,
                'description' => 'Perawatan ortodonti self-ligating metal. Mengurangi gesekan kawat dan mempercepat kontrol. Tahapan: konsultasi, rontgen, perawatan awal jika diperlukan, foto gigi, dan pemasangan. Biaya kontrol Rp200.000.',

                'color' => '#B0BEC5',
            ],

            [
                'category_id' => 2, // Konsultasi Ortodontik

                'name' => 'Kontrol Ortodontik',
                'slug' => 'kontrol-ortodontik',
                'code' => 'ORTHO-KONSUL',
                'duration_minutes' => 30,
                'price' => 250000,
                'description' => 'Layanan kontrol awal dengan dokter gigi spesialis ortodonti untuk evaluasi kondisi gigi dan rahang. Meliputi pemeriksaan awal, diskusi keluhan pasien (merapikan gigi), penentuan kebutuhan rontgen, serta penilaian kesiapan pemasangan behel. Konsultasi ini wajib sebelum pemasangan behel.',

                'color' => '#81C784',
            ],

            [
                'category_id' => 2,

                'name' => 'Kontrol Behel Ortodontik',
                'slug' => 'kontrol-behel-ortodontik',
                'code' => 'ORTHO-KONTROL',
                'duration_minutes' => 20,
                'price' => 200000,
                'description' => 'Layanan kontrol rutin behel untuk evaluasi pergerakan gigi dan penyesuaian kawat. Dilakukan secara berkala sesuai jadwal dokter spesialis ortodonti.',

                'color' => '#4DB6AC',
            ],

        ];

        foreach ($services as $serviceData) {
            \App\Models\Service::create($serviceData);
        }

    }
}
