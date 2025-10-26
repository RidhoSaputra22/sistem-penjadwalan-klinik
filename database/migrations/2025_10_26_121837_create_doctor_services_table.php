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
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->integer('priority')->default(0); // optional priority for assignment
            $table->unique(['user_id', 'service_id']);
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('doctor_services')->insert([
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'service_id' => 1, // Konsultasi Umum
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'service_id' => 2, // Pemeriksaan Kesehatan Jiwa
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'service_id' => 3, // Tes Laboratorium Dasar
                'priority' => 3,
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
        Schema::dropIfExists('doctor_services');
    }
};
