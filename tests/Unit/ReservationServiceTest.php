<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeServiceStub(array $overrides = []): ReservationService
    {
        return new class($overrides) extends ReservationService
        {
            public bool $processReservationCalled = false;

            /** @var array<string,mixed> */
            private array $overrides;

            public function __construct(array $overrides)
            {
                $this->overrides = $overrides;
                parent::__construct();
            }

            public function processReservation(\App\Models\Appointment $booking)
            {
                $this->processReservationCalled = true;
            }

            protected function availableTimeSlots(?string $date, int $durationMinutes, ?int $roomId = null, ?int $doctorId = null, ?int $excludeAppointmentId = null): array
            {
                return $this->overrides['slots'] ?? parent::availableTimeSlots($date, $durationMinutes, $roomId, $doctorId, $excludeAppointmentId);
            }

            protected function findAvailableAssignment(Carbon $scheduledDate, int $durationMinutes, ?int $excludeAppointmentId = null): ?array
            {
                if (array_key_exists('assignment', $this->overrides)) {
                    return $this->overrides['assignment'];
                }

                return parent::findAvailableAssignment($scheduledDate, $durationMinutes, $excludeAppointmentId);
            }
        };
    }

    public function test_process_payment_result_returns_400_on_invalid_order_id(): void
    {
        $service = $this->makeServiceStub();

        $result = $service->processPaymentResult(['transaction_status' => 'settlement']);

        $this->assertFalse($result['ok']);
        $this->assertSame(400, $result['status']);
        $this->assertSame('Invalid order_id', $result['message']);
        $this->assertNull($result['booking']);
    }

    public function test_process_payment_result_returns_404_when_appointment_not_found(): void
    {
        $service = $this->makeServiceStub();

        $result = $service->processPaymentResult([
            'order_id' => 'ORD-404',
            'transaction_status' => 'settlement',
        ]);

        $this->assertFalse($result['ok']);
        $this->assertSame(404, $result['status']);
        $this->assertSame('Appointment not found', $result['message']);
        $this->assertNull($result['booking']);
    }

    public function test_process_payment_result_confirms_and_calls_process_reservation_on_settlement(): void
    {
        $service = $this->makeServiceStub();

        $appointment = Appointment::factory()->create([
            'code' => 'ORD-SETTLE',
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->processPaymentResult([
            'order_id' => 'ORD-SETTLE',
            'transaction_status' => 'settlement',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Payment confirmed', $result['message']);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->status->value);
    }

    public function test_process_payment_result_cancels_on_cancel_or_expire_or_deny(): void
    {
        $service = $this->makeServiceStub();

        $appointment = Appointment::factory()->create([
            'code' => 'ORD-CANCEL',
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->processPaymentResult([
            'order_id' => 'ORD-CANCEL',
            'transaction_status' => 'cancel',
        ]);

        $this->assertFalse($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Payment cancelled', $result['message']);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::CANCELLED->value, $appointment->status->value);
    }

    public function test_process_payment_result_returns_pending_for_other_statuses(): void
    {
        $service = $this->makeServiceStub();

        $appointment = Appointment::factory()->create([
            'code' => 'ORD-PENDING',
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->processPaymentResult([
            'order_id' => 'ORD-PENDING',
            'transaction_status' => 'pending',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Payment pending', $result['message']);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->status->value);
    }

    public function test_reschedule_returns_400_on_invalid_datetime(): void
    {
        $service = $this->makeServiceStub();

        $result = $service->rescheduleBookingByUser(1, 1, 'not-a-date');

        $this->assertFalse($result['ok']);
        $this->assertSame(400, $result['status']);
        $this->assertSame('Tanggal / jam tidak valid.', $result['message']);
        $this->assertNull($result['booking']);
    }

    public function test_reschedule_returns_404_when_booking_not_found(): void
    {
        $service = $this->makeServiceStub();

        $result = $service->rescheduleBookingByUser(99999, 1, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(404, $result['status']);
        $this->assertSame('Booking tidak ditemukan.', $result['message']);
        $this->assertNull($result['booking']);
    }

    public function test_reschedule_returns_403_when_not_owner(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $notOwner = User::factory()->create();

        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $notOwner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(403, $result['status']);
        $this->assertSame('Anda tidak berhak mengubah booking ini.', $result['message']);
        $this->assertNotNull($result['booking']);
    }

    public function test_reschedule_returns_422_when_booking_done_or_cancelled(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::DONE,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Booking sudah selesai atau dibatalkan dan tidak bisa dijadwal ulang.', $result['message']);
    }

    public function test_reschedule_returns_422_when_booking_ongoing(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::ONGOING,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Booking sedang berlangsung dan tidak bisa dijadwal ulang.', $result['message']);
    }

    public function test_reschedule_returns_422_when_holiday(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        Holiday::factory()->create(['date' => '2026-01-24']);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Tanggal tersebut adalah hari libur. Silakan pilih tanggal lain.', $result['message']);
    }

    public function test_reschedule_returns_422_when_slot_unavailable(): void
    {
        $service = $this->makeServiceStub([
            'slots' => [
                ['time' => '10:00', 'available' => false],
            ],
        ]);

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Jam yang dipilih sudah tidak tersedia. Silakan pilih jam lain.', $result['message']);
    }

    public function test_reschedule_returns_422_when_no_assignment_found(): void
    {
        $service = $this->makeServiceStub([
            'slots' => [
                ['time' => '10:00', 'available' => true],
            ],
            'assignment' => null,
        ]);

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Tidak ada dokter/ruangan yang tersedia pada jam tersebut.', $result['message']);
    }

    public function test_reschedule_success_updates_booking_and_sends_notifications(): void
    {
        Notification::fake();

        $room = Room::factory()->create();
        $doctor = User::factory()->create(['role' => UserRole::DOCTOR]);

        $service = $this->makeServiceStub([
            'slots' => [
                ['time' => '10:00', 'available' => true],
            ],
            'assignment' => [
                'doctor_user_id' => $doctor->id,
                'room_id' => $room->id,
                'scheduled_date' => '2026-01-24',
                'scheduled_start' => '10:00:00',
                'scheduled_end' => '11:00:00',
            ],
        ]);

        $owner = User::factory()->create();
        $svc = Service::factory()->create(['duration_minutes' => 60]);

        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'service_id' => $svc->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->rescheduleBookingByUser($booking->id, $owner->id, '2026-01-24 10:00');

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Reschedule berhasil.', $result['message']);

        $booking->refresh();
        $this->assertSame($doctor->id, (int) $booking->doctor_id);
        $this->assertSame($room->id, (int) $booking->room_id);
        $this->assertSame('2026-01-24', $booking->scheduled_date->format('Y-m-d'));
        $this->assertSame('10:00:00', $booking->scheduled_start);
        $this->assertSame('11:00:00', $booking->scheduled_end);

        Notification::assertSentTo($owner, GenericDatabaseNotification::class);
        Notification::assertSentTo($doctor, GenericDatabaseNotification::class);
    }

    public function test_cancel_returns_404_when_booking_not_found(): void
    {
        $service = $this->makeServiceStub();

        $result = $service->cancelBookingByUser(99999, 1);

        $this->assertFalse($result['ok']);
        $this->assertSame(404, $result['status']);
        $this->assertSame('Booking tidak ditemukan.', $result['message']);
        $this->assertNull($result['booking']);
    }

    public function test_cancel_returns_403_when_not_owner(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $notOwner = User::factory()->create();

        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->cancelBookingByUser($booking->id, $notOwner->id);

        $this->assertFalse($result['ok']);
        $this->assertSame(403, $result['status']);
        $this->assertSame('Anda tidak berhak membatalkan booking ini.', $result['message']);
    }

    public function test_cancel_returns_200_when_already_cancelled(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::CANCELLED,
        ]);

        $result = $service->cancelBookingByUser($booking->id, $owner->id);

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Booking sudah dibatalkan.', $result['message']);
    }

    public function test_cancel_returns_422_when_ongoing_or_done(): void
    {
        $service = $this->makeServiceStub();

        $owner = User::factory()->create();
        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'status' => AppointmentStatus::ONGOING,
        ]);

        $result = $service->cancelBookingByUser($booking->id, $owner->id);

        $this->assertFalse($result['ok']);
        $this->assertSame(422, $result['status']);
        $this->assertSame('Booking yang sedang berlangsung / selesai tidak bisa dibatalkan.', $result['message']);
    }

    public function test_cancel_success_sets_status_and_sends_notifications(): void
    {
        Notification::fake();

        $service = $this->makeServiceStub();

        $owner = User::factory()->create(
            [
                'email' => 'saputra22022@gmail.com',
            ]
        );
        $doctor = User::factory()->create(['role' => UserRole::DOCTOR]);

        $booking = Appointment::factory()->create([
            'patient_id' => $owner->patient->id,
            'doctor_id' => $doctor->id,
            'status' => AppointmentStatus::PENDING,
        ]);

        $result = $service->cancelBookingByUser($booking->id, $owner->id);

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Booking dibatalkan.', $result['message']);

        $booking->refresh();
        $this->assertSame(AppointmentStatus::CANCELLED->value, $booking->status->value);

        Notification::assertSentTo($owner, GenericDatabaseNotification::class);
        Notification::assertSentTo($doctor, GenericDatabaseNotification::class);
    }
}
