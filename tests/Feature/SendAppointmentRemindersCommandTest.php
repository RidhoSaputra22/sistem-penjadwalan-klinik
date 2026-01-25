<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use App\Services\Helper\ReservationServiceHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendAppointmentRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_reminder_one_hour_before_appointment(): void
    {
        Notification::fake();

        $tz = ReservationServiceHelper::TZ;
        Carbon::setTestNow(Carbon::create(2026, 1, 26, 10, 0, 0, $tz));

        $user = User::factory()->create([
            'phone' => '081344968521',
        ]);

        $patient = $user->patient;
        $this->assertNotNull($patient);

        $scheduledAt = Carbon::now($tz)->addHour();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::CONFIRMED,
            'scheduled_date' => $scheduledAt->toDateString(),
            'scheduled_start' => $scheduledAt->format('H:i:s'),
            'scheduled_end' => $scheduledAt->copy()->addMinutes(30)->format('H:i:s'),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('appointments:send-reminders --window=5')
            ->assertExitCode(0);

        $appointment->refresh();
        $this->assertNotNull($appointment->reminder_sent_at);

        Notification::assertSentTo(
            $user,
            GenericDatabaseNotification::class,
            function (GenericDatabaseNotification $notification) use ($user): bool {
                $payload = $notification->toArray($user);

                return ($payload['kind'] ?? null) === NotificationType::Reminder->value;
            }
        );
    }
}
