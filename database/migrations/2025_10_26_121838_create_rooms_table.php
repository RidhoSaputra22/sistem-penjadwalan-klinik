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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('rooms')->insert([
            [
                'name' => 'Ruang Pemeriksaan 1',
                'code' => 'RM-001',
                'notes' => 'Ruang konsultasi umum dengan fasilitas dasar pemeriksaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Konseling Psikologi',
                'code' => 'RM-002',
                'notes' => 'Diperuntukkan untuk sesi konseling dan pemeriksaan kejiwaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Laboratorium',
                'code' => 'RM-003',
                'notes' => 'Ruang tes laboratorium dasar dan lanjutan.',
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
        Schema::dropIfExists('rooms');
    }
};
