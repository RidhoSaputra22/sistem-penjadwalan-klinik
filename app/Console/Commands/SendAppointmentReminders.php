<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Notifications\GenericDatabaseNotification;
use App\Services\Helper\ReservationServiceHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
        {--window=5 : Minutes tolerance before/after 1 hour}
        {--dry-run : Do not send notifications or update database}';

    protected $description = 'Send reminder to users 1 hour before their appointment.';

    public function handle(): int
    {
        $tz = ReservationServiceHelper::TZ;

        $windowMinutes = (int) $this->option('window');
        if ($windowMinutes <= 0) {
            $windowMinutes = 5;
        }

        $now = Carbon::now($tz);
        $target = $now->copy()->addHour();
        $startWindow = $target->copy()->subMinutes($windowMinutes);
        $endWindow = $target->copy()->addMinutes($windowMinutes);

        $appointments = Appointment::query()
            ->with(['patient.user', 'service', 'doctor', 'room'])
            ->where('status', AppointmentStatus::CONFIRMED->value)
            ->whereNull('reminder_sent_at')
            ->whereDate('scheduled_date', '>=', $startWindow->toDateString())
            ->whereDate('scheduled_date', '<=', $endWindow->toDateString())
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($appointments as $appointment) {
            $scheduledDate = $appointment->scheduled_date?->toDateString();
            $scheduledStart = is_string($appointment->scheduled_start) ? $appointment->scheduled_start : null;

            if (! $scheduledDate || ! $scheduledStart) {
                $skipped++;
                continue;
            }

            $scheduledAt = Carbon::parse($scheduledDate.' '.$scheduledStart, $tz);

            if ($scheduledAt->lt($startWindow) || $scheduledAt->gt($endWindow)) {
                $skipped++;
                continue;
            }

            $user = $appointment->patient?->user;
            if (! $user) {
                $skipped++;
                continue;
            }

            $formatted = $scheduledAt->format('d-m-Y H:i');

            $doctorName = $appointment->doctor?->name ?? 'Dokter';
            $serviceName = $appointment->service?->name ?? 'Layanan';
            $roomName = $appointment->room?->name ?? 'Ruang';

            $message = "Pengingat: appointment {$appointment->code} Anda akan dimulai dalam 1 jam.\n\n";
            $message .= "Jadwal: {$formatted}\n";
            $message .= "Layanan: {$serviceName}\n";
            $message .= "Dokter: {$doctorName}\n";
            $message .= "Ruangan: {$roomName}\n\n";
            $message .= 'Mohon datang 10-15 menit lebih awal.';

            if ((bool) $this->option('dry-run')) {
                $this->line("[DRY RUN] Would remind {$user->email} for {$appointment->code} ({$formatted})");
                continue;
            }

            $user->notify(new GenericDatabaseNotification(
                message: $message,
                kind: NotificationType::Reminder->value,
                extra: [
                    'booking_id' => $appointment->id,
                    'code' => $appointment->code,
                ],
            ));

            $appointment->forceFill([
                'reminder_sent_at' => $now,
            ])->save();

            $sent++;
        }

        $this->info("Done. Sent: {$sent}, skipped: {$skipped}.");

        return Command::SUCCESS;
    }
}
