<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Slot waktu dari 09:00 sampai 20:00 dengan interval 30 menit
        DB::table('sesi_pertemuans')->delete();

        $newSlots = [
            ['name' => '1', 'session_time' => '09:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2', 'session_time' => '09:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3', 'session_time' => '10:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4', 'session_time' => '10:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '5', 'session_time' => '11:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '6', 'session_time' => '11:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '7', 'session_time' => '12:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '8', 'session_time' => '12:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '9', 'session_time' => '13:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '10', 'session_time' => '13:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '11', 'session_time' => '14:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '12', 'session_time' => '14:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '13', 'session_time' => '15:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '14', 'session_time' => '15:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '15', 'session_time' => '16:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '16', 'session_time' => '16:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '17', 'session_time' => '17:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '18', 'session_time' => '17:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '19', 'session_time' => '18:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '20', 'session_time' => '18:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '21', 'session_time' => '19:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '22', 'session_time' => '19:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '23', 'session_time' => '20:00:00', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($newSlots as $slot) {
            DB::table('sesi_pertemuans')->insert($slot);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus semua slot
        DB::table('sesi_pertemuans')->delete();
    }
};
