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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // Seeder di dalam migrate
        DB::table('patients')->insert([
            [
                'name' => 'Budi Santoso',
                'nik' => '3174091201990001',
                'birth_date' => '1990-01-12',
                'phone' => '081234567001',
                'address' => 'Jl. Melati No. 10, Jakarta Selatan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rina Kartika',
                'nik' => '3174100720000002',
                'birth_date' => '2000-07-10',
                'phone' => '081234567002',
                'address' => 'Jl. Anggrek No. 21, Jakarta Timur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dewi Lestari',
                'nik' => '3174110520050003',
                'birth_date' => '2005-05-11',
                'phone' => '081234567003',
                'address' => 'Jl. Mawar No. 5, Jakarta Barat',
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
        Schema::dropIfExists('patients');
    }
};
