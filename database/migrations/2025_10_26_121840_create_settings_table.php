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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('settings')->insert([
            [
                'key' => 'clinic_name',
                'value' => 'Klinik Sehat Sentosa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_appointment_duration',
                'value' => '30', // menit
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'working_days',
                'value' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
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
        Schema::dropIfExists('settings');
    }
};
