<?php

namespace App\Services;

use App\Models\{
    Appointment,
    Patient,
    Doctor,
    User,
    Holiday,
    Room,
    Service,
    SesiPertemuan
};
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Enums\NotificationType;
use App\Jobs\SendWhatsAppBookingMessage;
use App\Notifications\GenericDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Carbon\Carbon;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        $serverKey = (string) config('services.midtrans.server_key', '');
        if (trim($serverKey) === '') {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diset. Pastikan MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY ada di .env, lalu jalankan `php artisan config:clear`.');
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    public static function getAvailableTimeSlots(
        ?string $date,
        int $durationMinutes,
        ?int $roomId = null,
        ?int $doctorId = null,
        ?int $excludeAppointmentId = null,
    ): array {
        if (empty($date)) {
            return [];
        }

        Carbon::setLocale('id');
        $tz = 'Asia/Makassar';

        $slotTimes = SesiPertemuan::orderBy('session_time')
            ->pluck('session_time')
            ->map(fn ($time) => Carbon::createFromTimeString((string) $time)->format('H:i'))
            ->toArray();

        if (empty($slotTimes)) {
            return [];
        }

        // Batas operasional harus pakai tanggal yang dipilih (bukan tanggal hari ini)
        $operationalStart = Carbon::parse("$date {$slotTimes[0]}", $tz);
        $operationalEnd = Carbon::parse("$date {$slotTimes[count($slotTimes) - 1]}", $tz)
            ->addMinutes($durationMinutes);

        $totalRooms = $roomId !== null
            ? 1
            : Room::query()->count();

        $totalDoctors = $doctorId !== null
            ? 1
            : Doctor::query()->count();

        $takenAppointmentsQuery = Appointment::query()
            ->whereDate('scheduled_date', $date)
            ->whereNotNull('scheduled_date')
            ->where('status', '!=', AppointmentStatus::CANCELLED->value)
            ->when($excludeAppointmentId !== null, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->when($roomId !== null, fn ($q) => $q->where('room_id', $roomId))
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->with('service');

        $takenIntervals = $takenAppointmentsQuery
            ->get()
            ->map(function (Appointment $booking) use ($durationMinutes, $tz) {
                $dateString = $booking->scheduled_date
                    ? Carbon::parse((string) $booking->scheduled_date, $tz)->toDateString()
                    : null;
                $startTime = $booking->scheduled_start;
                $endTime = $booking->scheduled_end;

                $start = ($dateString && $startTime)
                    ? Carbon::parse("$dateString $startTime", $tz)
                    : null;

                $end = ($dateString && $endTime)
                    ? Carbon::parse("$dateString $endTime", $tz)
                    : null;

                if (! $end && $start) {
                    $bookingDuration = $booking->service?->duration_minutes;
                    $end = $start->copy()->addMinutes($bookingDuration ?? $durationMinutes);
                }

                return [
                    'start' => $start,
                    'end' => $end,
                ];
            });

        return collect($slotTimes)
            ->map(function (string $time) use ($date, $durationMinutes, $operationalStart, $operationalEnd, $takenIntervals, $totalDoctors, $totalRooms, $tz) {
                $slotStart = Carbon::parse("$date $time", $tz);
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if ($slotStart->lt($operationalStart) || $slotEnd->gt($operationalEnd)) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

                if ($totalDoctors <= 0 || $totalRooms <= 0) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

                $overlapCount = $takenIntervals->filter(function (array $taken) use ($slotStart, $slotEnd) {
                    $takenStart = $taken['start'];
                    $takenEnd = $taken['end'];

                    if (! $takenStart || ! $takenEnd) {
                        return false;
                    }

                    return $slotStart->lt($takenEnd) && $slotEnd->gt($takenStart);
                })->count();

                $available = $overlapCount < $totalDoctors && $overlapCount < $totalRooms;

                return [
                    'time' => $time,
                    'available' => $available,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Membuat reservasi baru dan inisialisasi pembayaran
     */
    public function createReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $name = (string) ($data['name'] ?? 'Customer');
            $email = $data['email'] ?? null;
            $phone = (string) ($data['phone'] ?? '');

            $userId = $data['user_id'] ?? null;
            $user = null;

            if (is_numeric($userId)) {
                $user = User::query()->find((int) $userId);
            }

            if (! $user) {
                $authUser = Auth::user();
                $user = $authUser instanceof User ? $authUser : null;
            }

            if (! $user) {
                $user = $this->resolveOrCreateUser(
                    name: $name,
                    email: is_string($email) ? $email : null,
                    phone: $phone,
                );
            }

            // Ambil Layanan
            $service = Service::findOrFail($data['service_id']);

            $scheduledDate = $data['scheduled_date'] ?? null;
            if (is_string($scheduledDate) && $scheduledDate !== '') {
                $scheduledDate = Carbon::parse($scheduledDate, 'Asia/Makassar');
            }

            $scheduleStart = $scheduledDate instanceof Carbon
                ? $scheduledDate->copy()
                : null;

            $scheduleEnd = $scheduleStart
                ? $scheduleStart->copy()->addMinutes($service->duration_minutes)
                : null;

            $patient = $user?->patient;
            if (! $patient && $user) {
                $patient = Patient::query()->create([
                    'user_id' => $user->id,
                    'medical_record_number' => null,
                    'nik' => null,
                    'birth_date' => null,
                    'address' => null,
                ]);
            }

            if (! $patient) {
                throw new \RuntimeException('Data pasien tidak ditemukan untuk user yang login.');
            }



            // Buat booking (masih pending)
            $booking = Appointment::create([
                'service_id' => $service->id,
                'scheduled_date' => $scheduledDate instanceof Carbon ? $scheduledDate->toDateString() : null,
                'status' => AppointmentStatus::PENDING,
                'patient_id' => $patient->id,
                'scheduled_start' => $scheduleStart ? $scheduleStart->format('H:i:s') : null,
                'scheduled_end' => $scheduleEnd ? $scheduleEnd->format('H:i:s') : null,
            ]);

            // dd($booking);



            // Buat parameter pembayaran Midtrans
            $params = [
                'transaction_details' => [
                    // Gunakan code booking agar callback bisa mencari booking tanpa kolom order_id khusus.
                    'order_id' => $booking->code,
                    'gross_amount' => $service->price,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->hp,
                ],
                'item_details' => [
                    [
                        'id' => $service->id,
                        'price' => $service->price,
                        'quantity' => 1,
                        'name' => $service->name,
                    ],
                ],
            ];

            // Dapatkan Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Simpan Snap Token ke booking
            $booking->update(['snap_token' => $snapToken]);

            $user->notify(new GenericDatabaseNotification(
                message: 'Booking berhasil dibuat. Silakan lakukan pembayaran untuk konfirmasi.',
                kind: NotificationType::BookingCreated->value,
                extra: [
                    'booking_id' => $booking->id,
                    'code' => $booking->code,
                ],
            ));

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
            $this->processFCFS($booking);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Payment confirmed',
                'booking' => $booking,
            ];
        }

        if (in_array($transactionStatus, ['cancel', 'expire', 'deny'], true)) {
            $booking->update(['status' => AppointmentStatus::CANCELLED]);

            $booking->loadMissing('patient.user');
            $booking->patient?->user?->notify(new GenericDatabaseNotification(
                message: 'Pembayaran dibatalkan / kedaluwarsa. Booking Anda dibatalkan.',
                kind: NotificationType::Cancelled->value,
                extra: ['booking_id' => $booking->id, 'code' => $booking->code],
            ));

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
        $tz = 'Asia/Makassar';

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

        $slots = self::getAvailableTimeSlots(
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

        $booking->update([
            'doctor_id' => $assignment['doctor_id'],
            'room_id' => $assignment['room_id'],
            'scheduled_date' => $assignment['scheduled_date'],
            'scheduled_start' => $assignment['scheduled_start'],
            'scheduled_end' => $assignment['scheduled_end'],
            // status tidak diubah (tetap pending/confirmed sesuai kondisi sebelumnya)
        ]);

        $booking->refresh()->loadMissing(['patient.user', 'doctor', 'room', 'service']);

        $formatted = Carbon::parse($assignment['scheduled_date'] . ' ' . $assignment['scheduled_start'], $tz)
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
    protected function processFCFS(Appointment $booking)
    {
        $booking->loadMissing(['service', 'patient.user']);

        $tz = 'Asia/Makassar';
        $durationMinutes = (int) ($booking->service?->duration_minutes ?? 60);

        $dateString = $booking->scheduled_date
            ? Carbon::parse((string) $booking->scheduled_date, $tz)->toDateString()
            : null;

        $scheduledDate = ($dateString && $booking->scheduled_start)
            ? Carbon::parse($dateString . ' ' . $booking->scheduled_start, $tz)
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
            'doctor_id' => $assignment['doctor_id'],
            'room_id' => $assignment['room_id'],
            'scheduled_date' => $assignment['scheduled_date'],
            'scheduled_start' => $assignment['scheduled_start'],
            'scheduled_end' => $assignment['scheduled_end'],
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $formatted = Carbon::parse($assignment['scheduled_date'] . ' ' . $assignment['scheduled_start'], $tz)
            ->format('d-m-Y H:i');

        $booking->patient?->user?->notify(new GenericDatabaseNotification(
            message: "Anda telah dijadwalkan untuk appointment {$booking->code} pada {$formatted}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));

        $doctorUser = User::query()->find((int) $assignment['doctor_id']);
        $doctorUser?->notify(new GenericDatabaseNotification(
            message: "Anda dijadwalkan untuk appointment {$booking->code} pada {$formatted}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],)
        );

        // Send WhatsApp notification
        $email = (string) ($booking->patient?->user?->email ?? '');
        if (trim($email) !== '') {
            SendWhatsAppBookingMessage::dispatch(
                userEmail: $email,
                bookingCode: (string) $booking->code,
                paketSlug: (string) ($booking->service?->slug ?? ''),
            );
        }
    }

    private function resolveOrCreateUser(string $name, ?string $email, string $phone): User
    {
        $hp = trim($phone);
        $hpDigits = preg_replace('/\D+/', '', $hp) ?: (string) Str::random(10);

        $resolvedEmail = $email;
        if (! is_string($resolvedEmail) || $resolvedEmail === '') {
            $resolvedEmail = 'guest+' . $hpDigits . '@example.test';
        }

        $existing = User::query()
            ->where('email', $resolvedEmail)
            ->orWhere('hp', $hp)
            ->first();

        if ($existing) {
            return $existing;
        }

        $uniqueEmail = $resolvedEmail;
        $counter = 2;
        while (User::query()->where('email', $uniqueEmail)->exists()) {
            $uniqueEmail = 'guest+' . $hpDigits . '+' . $counter . '@example.test';
            $counter++;
        }

        $uniqueHp = $hp !== '' ? $hp : $hpDigits;
        $hpCounter = 2;
        while (User::query()->where('hp', $uniqueHp)->exists()) {
            $uniqueHp = $hpDigits . '-' . $hpCounter;
            $hpCounter++;
        }

        return User::query()->create([
            'name' => $name,
            'email' => $uniqueEmail,
            'hp' => $uniqueHp,
            'password' => bcrypt(Str::random(32)),
            // role default sudah ada di migration
        ]);
    }

    private function findNextAvailableAssignment(Carbon $startDate, int $durationMinutes, int $excludeAppointmentId, int $maxDays = 30): ?array
    {
        $tz = 'Asia/Makassar';

        for ($i = 0; $i <= $maxDays; $i++) {
            $date = $startDate->copy()->addDays($i);

            if (Holiday::query()->whereDate('date', $date->toDateString())->exists()) {
                continue;
            }

            $slots = self::getAvailableTimeSlots(
                date: $date->toDateString(),
                durationMinutes: $durationMinutes,
            );

            foreach ($slots as $slot) {
                if (! ($slot['available'] ?? false)) {
                    continue;
                }

                $scheduledDate = Carbon::parse($date->toDateString() . ' ' . $slot['time'], $tz);
                $assignment = $this->findAvailableAssignment($scheduledDate, $durationMinutes, $excludeAppointmentId);
                if ($assignment) {
                    return $assignment;
                }
            }
        }

        return null;
    }

    private function findAvailableAssignment(Carbon $scheduledDate, int $durationMinutes, int $excludeAppointmentId): ?array
    {
        $tz = 'Asia/Makassar';
        $start = $scheduledDate->copy()->setTimezone($tz);
        $end = $start->copy()->addMinutes($durationMinutes);

        $date = $start->toDateString();

        $busyAppointments = Appointment::query()
            ->whereDate('scheduled_date', $date)
            ->whereNotNull('scheduled_date')
            ->where('status', '!=', AppointmentStatus::CANCELLED->value)
            ->where('id', '!=', $excludeAppointmentId)
            ->with('service')
            ->get();

        $busyDoctorUserIds = [];
        $busyRoomIds = [];

        foreach ($busyAppointments as $b) {
            $bDateString = $b->scheduled_date
                ? Carbon::parse((string) $b->scheduled_date, $tz)->toDateString()
                : null;

            $bStart = ($bDateString && $b->scheduled_start)
                ? Carbon::parse($bDateString . ' ' . $b->scheduled_start, $tz)
                : null;

            if (! $bStart) {
                continue;
            }

            $bEnd = ($bDateString && $b->scheduled_end)
                ? Carbon::parse($bDateString . ' ' . $b->scheduled_end, $tz)
                : null;

            if (! $bEnd) {
                $bDuration = (int) ($b->service?->duration_minutes ?? $durationMinutes);
                $bEnd = $bStart->copy()->addMinutes($bDuration);
            }

            $overlap = $start->lt($bEnd) && $end->gt($bStart);
            if (! $overlap) {
                continue;
            }

            if ($b->doctor_id) {
                $busyDoctorUserIds[] = (int) $b->doctor_id;
            }
            if ($b->room_id) {
                $busyRoomIds[] = (int) $b->room_id;
            }
        }

        $busyDoctorUserIds = array_values(array_unique($busyDoctorUserIds));
        $busyRoomIds = array_values(array_unique($busyRoomIds));

        $doctorUserId = Doctor::query()
            ->where('is_active', true)
            ->pluck('user_id')
            ->first(fn (int $id) => ! in_array($id, $busyDoctorUserIds, true));

        $room = Room::query()
            ->when($busyRoomIds !== [], fn ($q) => $q->whereNotIn('id', $busyRoomIds))
            ->first();

        if (! $doctorUserId || ! $room) {
            return null;
        }

        return [
            'scheduled_date' => $start->toDateString(),
            'scheduled_start' => $start->format('H:i:s'),
            'scheduled_end' => $end->format('H:i:s'),
            'doctor_id' => (int) $doctorUserId,
            'room_id' => $room->id,
        ];
    }
}
