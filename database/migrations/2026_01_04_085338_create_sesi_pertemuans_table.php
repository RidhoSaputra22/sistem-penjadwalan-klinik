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
        Schema::create('sesi_pertemuans', function (Blueprint $table) {
             $table->id();
            $table->string('name');
            $table->time('session_time');
            $table->timestamps();
        });

         DB::table('sesi_pertemuans')->insert([
            ['name' => '1', 'session_time' => '09:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2', 'session_time' => '10:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3', 'session_time' => '11:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4', 'session_time' => '13:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '5', 'session_time' => '14:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '6', 'session_time' => '15:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '7', 'session_time' => '16:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '8', 'session_time' => '17:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '9', 'session_time' => '18:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '10', 'session_time' => '18:20:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '11', 'session_time' => '19:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '12', 'session_time' => '20:00:00', 'created_at' => now(), 'updated_at' => now()],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_pertemuans');
    }
};
