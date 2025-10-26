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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name')->nullable();
            $table->boolean('full_day')->default(true);
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('holidays')->insert([
            [
                'date' => '2025-01-01',
                'name' => 'Tahun Baru Masehi',
                'full_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-03-31',
                'name' => 'Hari Raya Nyepi',
                'full_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-04-18',
                'name' => 'Wafat Isa Almasih',
                'full_day' => true,
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
        Schema::dropIfExists('holidays');
    }
};
