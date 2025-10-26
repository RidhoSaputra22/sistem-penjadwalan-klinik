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
        Schema::create('rr_pointers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('last_assigned_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('service_id');
        });

        // Seeder di dalam migrate
        DB::table('rr_pointers')->insert([
            [
                'service_id' => 1, // Konsultasi Umum
                'last_assigned_doctor_id' => 1, // Dr. Andi Setiawan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2, // Pemeriksaan Kesehatan Jiwa
                'last_assigned_doctor_id' => 1, // Dr. Andi Setiawan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3, // Tes Laboratorium Dasar
                'last_assigned_doctor_id' => null, // Belum ada dokter terakhir
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
        Schema::dropIfExists('rr_pointers');
    }
};
