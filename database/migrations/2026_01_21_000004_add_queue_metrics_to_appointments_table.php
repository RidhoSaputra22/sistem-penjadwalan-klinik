<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('priority_id')
                ->nullable()
                ->after('service_id')
                ->constrained('priorities')
                ->nullOnDelete();

            $table->date('original_scheduled_date')->nullable()->after('scheduled_end');
            $table->time('original_scheduled_start')->nullable()->after('original_scheduled_date');
            $table->time('original_scheduled_end')->nullable()->after('original_scheduled_start');

            $table->dateTime('checked_in_at')->nullable()->after('original_scheduled_end');
            $table->dateTime('called_at')->nullable()->after('checked_in_at');
            $table->dateTime('service_started_at')->nullable()->after('called_at');
            $table->dateTime('service_ended_at')->nullable()->after('service_started_at');

            $table->dateTime('no_show_at')->nullable()->after('service_ended_at');

            $table->unsignedSmallInteger('rescheduled_count')->default(0)->after('no_show_at');
            $table->dateTime('last_rescheduled_at')->nullable()->after('rescheduled_count');

            // NOTE: index (scheduled_date, doctor_id) sudah dibuat di migration create_appointments_table.
            $table->index(['checked_in_at']);
            $table->index(['service_started_at']);
            $table->index(['service_ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['checked_in_at']);
            $table->dropIndex(['service_started_at']);
            $table->dropIndex(['service_ended_at']);

            $table->dropConstrainedForeignId('priority_id');

            $table->dropColumn([
                'original_scheduled_date',
                'original_scheduled_start',
                'original_scheduled_end',
                'checked_in_at',
                'called_at',
                'service_started_at',
                'service_ended_at',
                'no_show_at',
                'rescheduled_count',
                'last_rescheduled_at',
            ]);
        });
    }
};
