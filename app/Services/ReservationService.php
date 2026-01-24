<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use App\Services\Helper\ReservationServiceHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class ReservationService
{
    private const TZ = 'Asia/Makassar';

    public function __construct()
    {
        // Midtrans configuration is done lazily (only when needed)
    }

    private function ensureMidtransConfigured(): void
    {
        $serverKey = (string) config('services.midtrans.server_key', '');
        if (trim($serverKey) === '') {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diset. Pastikan MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY ada di .env, lalu jalankan `php artisan config:clear`.');
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Wrapper methods to make helper-dependent logic testable.
     */
    protected function availableTimeSlots(
        ?string $date,
        int $durationMinutes,
        ?int $roomId = null,
        ?int $doctorId = null,
        ?int $excludeAppointmentId = null,
    ): array {
        return ReservationServiceHelper::getAvailableTimeSlots(
            date: $date,
            durationMinutes: $durationMinutes,
            roomId: $roomId,
            doctorId: $doctorId,
            excludeAppointmentId: $excludeAppointmentId,
        );
    }

    protected function findAvailableAssignment(
        Carbon $scheduledDate,
        int $durationMinutes,
        ?int $excludeAppointmentId = null,
    ): ?array {
        return ReservationServiceHelper::findAvailableAssignment(
            scheduledDate: $scheduledDate,
            durationMinutes: $durationMinutes,
            excludeAppointmentId: $excludeAppointmentId,
        );
    }

    protected function findNextAvailableAssignment(
        Carbon $startDate,
        int $durationMinutes,
        ?int $excludeAppointmentId = null,
        int $maxDays = 30,
    ): ?array {
        return ReservationServiceHelper::findNextAvailableAssignment(
            startDate: $startDate,
            durationMinutes: $durationMinutes,
            excludeAppointmentId: $excludeAppointmentId,
            maxDays: $maxDays,
        );
    }

    public static function getAvailableTimeSlots(
        ?string $date,
        int $durationMinutes,
        ?int $roomId = null,
        ?int $doctorId = null,
        ?int $excludeAppointmentId = null,
    ): array {
        return ReservationServiceHelper::getAvailableTimeSlots(
            date: $date,
            durationMinutes: $durationMinutes,
            roomId: $roomId,
            doctorId: $doctorId,
            excludeAppointmentId: $excludeAppointmentId,
        );
    }

    /**
     * Membuat reservasi baru dan menginisialisasi pembayaran Midtrans.
     *
     * @param array{
     *     name?: string,
     *     email?: string|null,
     *     phone?: string,
     *     service_id: int,
     *     scheduled_date?: string|null
     * } $data
     * @return array{
     *     booking: \App\Models\Appointment,
     *     snap_token: string
     * }
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function createReservation(array $data)
    {
        $this->ensureMidtransConfigured();

        return DB::transaction(function () use ($data) {
            $name = (string) ($data['name'] ?? 'Customer');
            $email = $data['email'] ?? null;
            $phone = (string) ($data['phone'] ?? '');

            $user = ReservationServiceHelper::resolveUserFromRequestData(
                data: $data,
                fallbackName: $name,
                fallbackEmail: is_string($email) ? $email : null,
                fallbackPhone: $phone,
            );

            // Ambil Layanan
            $service = Service::findOrFail($data['service_id']);

            $scheduledAt = ReservationServiceHelper::parseScheduledAt($data['scheduled_date'] ?? null);

            if (! $scheduledAt) {
                throw ValidationException::withMessages([
                    'scheduled_date' => 'Tanggal / jam reservasi tidak valid.',
                ]);
            }

            if (Holiday::query()->whereDate('date', $scheduledAt->toDateString())->exists()) {
                throw ValidationException::withMessages([
                    'scheduled_date' => 'Tanggal tersebut adalah hari libur. Silakan pilih tanggal lain.',
                ]);
            }

            $durationMinutes = (int) ($service->duration_minutes ?? 60);
            $date = $scheduledAt->toDateString();
            $time = $scheduledAt->format('H:i');

            $slots = $this->availableTimeSlots(
                date: $date,
                durationMinutes: $durationMinutes,
            );

            $slot = collect($slots)->first(fn (array $s) => ($s['time'] ?? null) === $time);
            if (! $slot || ! ($slot['available'] ?? false)) {
                throw ValidationException::withMessages([
                    'scheduled_date' => 'Tidak ada dokter/ruangan yang tersedia pada jam tersebut.',
                ]);
            }

            [$scheduleStart, $scheduleEnd] = ReservationServiceHelper::buildScheduleWindow($scheduledAt, $durationMinutes);

            $patient = ReservationServiceHelper::resolveOrCreatePatient($user);

            $booking = ReservationServiceHelper::createPendingAppointment(
                patient: $patient,
                service: $service,
                scheduledAt: $scheduledAt,
                scheduleStart: $scheduleStart,
                scheduleEnd: $scheduleEnd,
            );

            $params = ReservationServiceHelper::buildMidtransSnapParams(user: $user, booking: $booking, service: $service);

            // Dapatkan Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Simpan Snap Token ke booking
            $booking->update(['snap_token' => $snapToken]);
            $booking->refresh();

            ReservationServiceHelper::notifyBookingCreated($user, $booking);

            return [
                'booking' => $booking,
                'snap_token' => $snapToken,
            ];
        });
    }

    /**
     * Callback Midtrans → update booking status & jalankan FCFS
     */
    public function handlePaymentCallback(array $payload)
    {
        $result = $this->processPaymentResult($payload);

        return response()->json(
            ['message' => $result['message']],
            $result['status'],
        );
    }

    /**
     * Proses hasil pembayaran (bisa dipanggil dari Livewire atau callback HTTP)
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Appointment}
     */
    public function processPaymentResult(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Invalid order_id',
                'booking' => null,
            ];
        }

        $booking = Appointment::query()->where('code', $orderId)->first();

        if (! $booking) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Appointment not found',
                'booking' => null,
            ];
        }

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            $booking->update(['status' => AppointmentStatus::CONFIRMED]);
            $this->processReservation($booking);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Payment confirmed',
                'booking' => $booking,
            ];
        }

        if (in_array($transactionStatus, ['cancel', 'expire', 'deny'], true)) {
            $booking->update(['status' => AppointmentStatus::CANCELLED]);

            ReservationServiceHelper::notifyBookingCancelledByPayment($booking);

            return [
                'ok' => false,
                'status' => 200,
                'message' => 'Payment cancelled',
                'booking' => $booking,
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Payment pending',
            'booking' => $booking,
        ];
    }

    /**
     * User action: reschedule booking (appointment) by owner.
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Appointment}
     */
    public function rescheduleBookingByUser(int $bookingId, int $userId, string $newScheduledAt): array
    {
        $tz = self::TZ;

        try {
            $scheduledAt = Carbon::parse($newScheduledAt, $tz);
        } catch (\Throwable) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Tanggal / jam tidak valid.',
                'booking' => null,
            ];
        }

        $booking = Appointment::query()
            ->with(['patient.user', 'service'])
            ->find($bookingId);

        if (! $booking) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Booking tidak ditemukan.',
                'booking' => null,
            ];
        }

        $ownerUserId = (int) ($booking->patient?->user_id ?? 0);
        if ($ownerUserId !== (int) $userId) {
            return [
                'ok' => false,
                'status' => 403,
                'message' => 'Anda tidak berhak mengubah booking ini.',
                'booking' => $booking,
            ];
        }

        if (in_array($booking->status?->value, [AppointmentStatus::CANCELLED->value, AppointmentStatus::DONE->value], true)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Booking sudah selesai atau dibatalkan dan tidak bisa dijadwal ulang.',
                'booking' => $booking,
            ];
        }

        if ($booking->status?->value === AppointmentStatus::ONGOING->value) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Booking sedang berlangsung dan tidak bisa dijadwal ulang.',
                'booking' => $booking,
            ];
        }

        if (Holiday::query()->whereDate('date', $scheduledAt->toDateString())->exists()) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Tanggal tersebut adalah hari libur. Silakan pilih tanggal lain.',
                'booking' => $booking,
            ];
        }

        $durationMinutes = (int) ($booking->service?->duration_minutes ?? 60);
        $date = $scheduledAt->toDateString();
        $time = $scheduledAt->format('H:i');

        $slots = $this->availableTimeSlots(
            date: $date,
            durationMinutes: $durationMinutes,
            excludeAppointmentId: (int) $booking->id,
        );

        $slot = collect($slots)->first(fn (array $s) => ($s['time'] ?? null) === $time);
        if (! $slot || ! ($slot['available'] ?? false)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Jam yang dipilih sudah tidak tersedia. Silakan pilih jam lain.',
                'booking' => $booking,
            ];
        }

        $assignment = $this->findAvailableAssignment(
            scheduledDate: $scheduledAt,
            durationMinutes: $durationMinutes,
            excludeAppointmentId: (int) $booking->id,
        );

        if (! $assignment) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Tidak ada dokter/ruangan yang tersedia pada jam tersebut.',
                'booking' => $booking,
            ];
        }

        $update = [
            'doctor_id' => $assignment['doctor_user_id'],
            'room_id' => $assignment['room_id'],
            'scheduled_date' => $assignment['scheduled_date'],
            'scheduled_start' => $assignment['scheduled_start'],
            'scheduled_end' => $assignment['scheduled_end'],
            // status tidak diubah (tetap pending/confirmed sesuai kondisi sebelumnya)
        ];

        // Rekap reschedule hanya aktif jika kolom tersedia (migration sudah dijalankan)
        if (Schema::hasColumn('appointments', 'rescheduled_count')) {
            $update['rescheduled_count'] = ((int) ($booking->rescheduled_count ?? 0)) + 1;
        }
        if (Schema::hasColumn('appointments', 'last_rescheduled_at')) {
            $update['last_rescheduled_at'] = now();
        }
        if (Schema::hasColumn('appointments', 'original_scheduled_date')) {
            $update['original_scheduled_date'] = $booking->original_scheduled_date ?? $booking->scheduled_date;
        }
        if (Schema::hasColumn('appointments', 'original_scheduled_start')) {
            $update['original_scheduled_start'] = $booking->original_scheduled_start ?? $booking->scheduled_start;
        }
        if (Schema::hasColumn('appointments', 'original_scheduled_end')) {
            $update['original_scheduled_end'] = $booking->original_scheduled_end ?? $booking->scheduled_end;
        }

        $booking->update($update);

        $booking->refresh()->loadMissing(['patient.user', 'doctor', 'room', 'service']);

        $formatted = Carbon::parse($assignment['scheduled_date'].' '.$assignment['scheduled_start'], $tz)
            ->format('d-m-Y H:i');

        $booking->patient?->user?->notify(new GenericDatabaseNotification(
            message: "Jadwal appointment {$booking->code} berhasil diubah ke {$formatted}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));

        $booking->doctor?->notify(new GenericDatabaseNotification(
            message: "Jadwal appointment {$booking->code} telah diubah ke {$formatted}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Reschedule berhasil.',
            'booking' => $booking,
        ];
    }

    /**
     * User action: cancel booking (appointment) by owner.
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Appointment}
     */
    public function cancelBookingByUser(int $bookingId, int $userId): array
    {
        $booking = Appointment::query()
            ->with(['patient.user', 'doctor'])
            ->find($bookingId);

        if (! $booking) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Booking tidak ditemukan.',
                'booking' => null,
            ];
        }

        $ownerUserId = (int) ($booking->patient?->user_id ?? 0);
        if ($ownerUserId !== (int) $userId) {
            return [
                'ok' => false,
                'status' => 403,
                'message' => 'Anda tidak berhak membatalkan booking ini.',
                'booking' => $booking,
            ];
        }

        if ($booking->status?->value === AppointmentStatus::CANCELLED->value) {
            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Booking sudah dibatalkan.',
                'booking' => $booking,
            ];
        }

        if (in_array($booking->status?->value, [AppointmentStatus::ONGOING->value, AppointmentStatus::DONE->value], true)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Booking yang sedang berlangsung / selesai tidak bisa dibatalkan.',
                'booking' => $booking,
            ];
        }

        $booking->update([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        $booking->refresh()->loadMissing(['patient.user', 'doctor']);

        $booking->patient?->user?->notify(new GenericDatabaseNotification(
            message: 'Booking Anda berhasil dibatalkan.',
            kind: NotificationType::Cancelled->value,
            extra: ['booking_id' => $booking->id, 'code' => $booking->code],
        ));

        $booking->doctor?->notify(new GenericDatabaseNotification(
            message: "Appointment {$booking->code} telah dibatalkan oleh pasien.",
            kind: NotificationType::Cancelled->value,
            extra: ['booking_id' => $booking->id, 'code' => $booking->code],
        ));

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Booking dibatalkan.',
            'booking' => $booking,
        ];
    }

    /**
     * Algoritma FCFS → Menentukan dokter & ruangan pertama yang tersedia
     */
    public function processReservation(Appointment $booking)
    {
        $booking->loadMissing(['service', 'patient.user']);

        $tz = self::TZ;
        $durationMinutes = (int) ($booking->service?->duration_minutes ?? 60);

        $dateString = $booking->scheduled_date
            ? Carbon::parse((string) $booking->scheduled_date, $tz)->toDateString()
            : null;

        $scheduledDate = ($dateString && $booking->scheduled_start)
            ? Carbon::parse($dateString.' '.$booking->scheduled_start, $tz)
            : null;

        $assignment = $scheduledDate
            ? $this->findAvailableAssignment($scheduledDate, $durationMinutes, $booking->id)
            : null;

        if (! $assignment) {
            $assignment = $this->findNextAvailableAssignment(
                startDate: Carbon::now($tz)->addDay()->startOfDay(),
                durationMinutes: $durationMinutes,
                excludeAppointmentId: $booking->id,
                maxDays: 30,
            );
        }

        if (! $assignment) {
            $booking->patient?->user?->notify(new GenericDatabaseNotification(
                message: 'Pembayaran berhasil, namun saat ini semua jadwal penuh. Tim kami akan menghubungi Anda untuk penjadwalan ulang.',
                kind: NotificationType::BookingCreated->value,
                extra: [
                    'booking_id' => $booking->id,
                    'code' => $booking->code,
                ],
            ));

            return;
        }

        $booking->update([
            // appointments.doctor_id adalah FK ke users (user id dokter)
            'doctor_id' => $assignment['doctor_user_id'],
            'room_id' => $assignment['room_id'],
            'scheduled_date' => $assignment['scheduled_date'],
            'scheduled_start' => $assignment['scheduled_start'],
            'scheduled_end' => $assignment['scheduled_end'],
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $booking->refresh()->loadMissing(['patient.user', 'service']);

        $formatted = Carbon::parse($assignment['scheduled_date'].' '.$assignment['scheduled_start'], $tz)
            ->format('d-m-Y H:i');

        ReservationServiceHelper::notifyBookingScheduled($booking, $formatted);
        ReservationServiceHelper::notifyDoctorScheduled((int) $assignment['doctor_user_id'], $booking, $formatted);
    }
}
