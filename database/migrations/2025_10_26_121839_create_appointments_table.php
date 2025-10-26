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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_start');
            $table->time('scheduled_end');
            $table->enum('status', ['pending', 'confirmed', 'ongoing', 'done', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['scheduled_date', 'doctor_id']);
        });

        // Seeder di dalam migrate
        DB::table('appointments')->insert([
            [
                'code' => 'APT-001',
                'patient_id' => 1,
                'service_id' => 1, // Konsultasi Umum
                'doctor_id' => 1,  // Dr. Andi Setiawan
                'room_id' => 1,    // Ruang Pemeriksaan 1
                'scheduled_date' => '2025-10-27',
                'scheduled_start' => '09:00:00',
                'scheduled_end' => '09:30:00',
                'status' => 'confirmed',
                'notes' => 'Pasien datang untuk pemeriksaan awal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'APT-002',
                'patient_id' => 2,
                'service_id' => 2, // Pemeriksaan Kesehatan Jiwa
                'doctor_id' => 1,
                'room_id' => 2,    // Ruang Konseling Psikologi
                'scheduled_date' => '2025-10-27',
                'scheduled_start' => '10:00:00',
                'scheduled_end' => '10:45:00',
                'status' => 'pending',
                'notes' => 'Pertemuan pertama untuk sesi konseling.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'APT-003',
                'patient_id' => 3,
                'service_id' => 3, // Tes Laboratorium Dasar
                'doctor_id' => null, // Tes lab tanpa dokter langsung
                'room_id' => 3,    // Ruang Laboratorium
                'scheduled_date' => '2025-10-28',
                'scheduled_start' => '08:00:00',
                'scheduled_end' => '09:00:00',
                'status' => 'confirmed',
                'notes' => 'Tes darah rutin sebelum konsultasi dokter.',
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
        Schema::dropIfExists('appointments');
    }
};
