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
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('weekday')->comment('0=Sunday..6=Saturday');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('doctor_availabilities')->insert([
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'weekday' => 1, // Monday
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'weekday' => 3, // Wednesday
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1, // Dr. Andi Setiawan
                'weekday' => 5, // Friday
                'start_time' => '08:30:00',
                'end_time' => '11:30:00',
                'is_active' => true,
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
        Schema::dropIfExists('doctor_availabilities');
    }
};
