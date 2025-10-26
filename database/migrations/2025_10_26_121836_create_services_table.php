<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('services')->insert([
            [
                'name' => 'Konsultasi Umum',
                'code' => 'SRV-001',
                'duration_minutes' => 30,
                'description' => 'Layanan konsultasi umum dengan dokter untuk pemeriksaan awal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pemeriksaan Kesehatan Jiwa',
                'code' => 'SRV-002',
                'duration_minutes' => 45,
                'description' => 'Pemeriksaan dan konseling untuk kesehatan mental dan kejiwaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tes Laboratorium Dasar',
                'code' => 'SRV-003',
                'duration_minutes' => 60,
                'description' => 'Paket tes laboratorium dasar seperti darah dan urin.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
