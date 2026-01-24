<?php

namespace App\Services\Helper;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Holiday;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Service;
use App\Models\SesiPertemuan;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReservationServiceHelper
{
    public const TZ = 'Asia/Makassar';

    /**
     * Wrapper yang dipakai UI untuk menampilkan slot yang tersedia.
     *
     * @return array<int, array{time: string, available: bool}>
     */
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
        $tz = self::TZ;

        $weekday = Carbon::parse($date, $tz)->dayOfWeek; // 0=Sunday..6=Saturday

        $slotTimes = self::getSlotTimes();
        if (empty($slotTimes)) {
            return [];
        }

        [$operationalStart, $operationalEnd] = self::getOperationalWindow(
            date: $date,
            slotTimes: $slotTimes,
            durationMinutes: $durationMinutes,
            tz: $tz,
        );

        // Kapasitas ruangan (untuk shortcut cepat)
        $roomIds = $roomId !== null
            ? [$roomId]
            : Room::query()->pluck('id')->all();

        // Kandidat dokter aktif (user_id pada tabel doctors)
        $activeDoctorUserIds = Doctor::query()
            ->where('is_active', true)
            ->pluck('user_id')
            ->all();

        if ($doctorId !== null) {
            $activeDoctorUserIds = in_array($doctorId, $activeDoctorUserIds, true)
                ? [$doctorId]
                : [];
        }

        // Ambil availability dokter untuk weekday tsb (dipakai untuk filter per slot)
        $availabilityByDoctor = DoctorAvailability::query()
            ->where('is_active', true)
            ->where('weekday', $weekday)
            ->get(['user_id', 'start_time', 'end_time'])
            ->groupBy('user_id');

        $takenAppointmentsQuery = Appointment::query()
            ->whereDate('scheduled_date', $date)
            ->whereNotNull('scheduled_date')
            ->where('status', AppointmentStatus::CONFIRMED->value)
            ->when($excludeAppointmentId !== null, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->when($roomId !== null, fn ($q) => $q->where('room_id', $roomId))
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->with('service');

        // Interval booking existing (dipakai untuk cek overlap + dokter/ruangan yang sedang sibuk)
        $takenIntervals = self::mapAppointmentsToIntervals(
            bookings: $takenAppointmentsQuery->get(),
            fallbackDurationMinutes: $durationMinutes,
            tz: $tz,
        );

        return collect($slotTimes)
            ->map(function (string $time) use ($date, $durationMinutes, $operationalStart, $operationalEnd, $takenIntervals, $tz, $roomIds, $activeDoctorUserIds, $availabilityByDoctor) {
                $slotStart = Carbon::parse("$date $time", $tz);
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if ($slotStart->lt($operationalStart) || $slotEnd->gt($operationalEnd)) {
                    return ['time' => $time, 'available' => false];
                }

                $slotStartTime = $slotStart->format('H:i:s');
                $slotEndTime = $slotEnd->format('H:i:s');

                $overlapping = $takenIntervals->filter(function (array $taken) use ($slotStart, $slotEnd) {
                    $takenStart = $taken['start'] ?? null;
                    $takenEnd = $taken['end'] ?? null;

                    if (! $takenStart || ! $takenEnd) {
                        return false;
                    }

                    return $slotStart->lt($takenEnd) && $slotEnd->gt($takenStart);
                });

                $busyDoctorUserIds = $overlapping
                    ->pluck('doctor_user_id')
                    ->filter(fn ($v) => is_numeric($v))
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();

                $busyRoomIds = $overlapping
                    ->pluck('room_id')
                    ->filter(fn ($v) => is_numeric($v))
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();

                $availableRoomCount = count(array_diff($roomIds, $busyRoomIds));
                if ($availableRoomCount <= 0) {
                    return ['time' => $time, 'available' => false];
                }

                $availableDoctorCount = 0;
                foreach ($activeDoctorUserIds as $doctorUserId) {
                    if (in_array((int) $doctorUserId, $busyDoctorUserIds, true)) {
                        continue;
                    }

                    $doctorAvailabilities = $availabilityByDoctor->get((int) $doctorUserId, collect());
                    if (! self::doctorHasAvailability($doctorAvailabilities, $slotStartTime, $slotEndTime)) {
                        continue;
                    }

                    $availableDoctorCount++;
                    break; // cukup 1 dokter available untuk menandai slot tersedia
                }

                return ['time' => $time, 'available' => $availableDoctorCount > 0];
            })
            ->values()
            ->toArray();
    }

    /** @return string[] */
    public static function getSlotTimes(): array
    {
        return SesiPertemuan::query()
            ->orderBy('session_time', 'asc')
            ->pluck('session_time')
            ->filter()
            ->unique()
            ->map(fn ($time) => Carbon::createFromTimeString((string) $time)->format('H:i'))
            ->values()
            ->all();
    }

    /**
     * @param  string[]  $slotTimes
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function getOperationalWindow(string $date, array $slotTimes, int $durationMinutes, string $tz = self::TZ): array
    {
        $start = Carbon::parse("$date {$slotTimes[0]}", $tz);
        $end = Carbon::parse("$date {$slotTimes[count($slotTimes) - 1]}", $tz)
            ->addMinutes($durationMinutes);

        return [$start, $end];
    }

    /** @return array{0: int, 1: int} */
    public static function getCapacityCounts(?int $roomId, ?int $doctorId): array
    {
        $totalRooms = $roomId !== null ? 1 : Room::query()->count();
        $totalDoctors = $doctorId !== null ? 1 : Doctor::query()->count();

        return [$totalRooms, $totalDoctors];
    }

    /**
     * @return Collection<int, array{start: ?Carbon, end: ?Carbon}>
     */
    public static function mapAppointmentsToIntervals(Collection $bookings, int $fallbackDurationMinutes, string $tz = self::TZ): Collection
    {
        return $bookings->map(function (Appointment $booking) use ($fallbackDurationMinutes, $tz) {
            $dateString = $booking->scheduled_date
                ? Carbon::parse((string) $booking->scheduled_date, $tz)->toDateString()
                : null;

            $startTime = $booking->scheduled_start;
            $endTime = $booking->scheduled_end;

            $start = ($dateString && $startTime) ? Carbon::parse("$dateString $startTime", $tz) : null;
            $end = ($dateString && $endTime) ? Carbon::parse("$dateString $endTime", $tz) : null;

            if (! $end && $start) {
                $bookingDuration = $booking->service?->duration_minutes;
                $end = $start->copy()->addMinutes($bookingDuration ?? $fallbackDurationMinutes);
            }

            return [
                'start' => $start,
                'end' => $end,
                // appointments.doctor_id adalah FK ke users (user id dokter)
                'doctor_user_id' => $booking->doctor_id ? (int) $booking->doctor_id : null,
                'room_id' => $booking->room_id ? (int) $booking->room_id : null,
            ];
        });
    }

    /**
     * @param  Collection<int, DoctorAvailability>  $doctorAvailabilities
     */
    private static function doctorHasAvailability(Collection $doctorAvailabilities, string $slotStartTime, string $slotEndTime): bool
    {
        if ($doctorAvailabilities->isEmpty()) {
            return false;
        }

        foreach ($doctorAvailabilities as $availability) {
            $start = Carbon::createFromTimeString((string) $availability->start_time)->format('H:i:s');
            $end = Carbon::createFromTimeString((string) $availability->end_time)->format('H:i:s');

            if ($start <= $slotStartTime && $end >= $slotEndTime) {
                return true;
            }
        }

        return false;
    }

    public static function countOverlaps(Collection $takenIntervals, Carbon $slotStart, Carbon $slotEnd): int
    {
        return $takenIntervals->filter(function (array $taken) use ($slotStart, $slotEnd) {
            $takenStart = $taken['start'] ?? null;
            $takenEnd = $taken['end'] ?? null;

            if (! $takenStart || ! $takenEnd) {
                return false;
            }

            return $slotStart->lt($takenEnd) && $slotEnd->gt($takenStart);
        })->count();
    }

    public static function resolveUserFromRequestData(array $data, string $fallbackName, ?string $fallbackEmail, string $fallbackPhone): User
    {
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
            $user = self::resolveOrCreateUser(
                name: $fallbackName,
                email: $fallbackEmail,
                phone: $fallbackPhone,
            );
        }

        return $user;
    }

    public static function parseScheduledAt(mixed $scheduledAt): ?Carbon
    {
        if ($scheduledAt instanceof Carbon) {
            return $scheduledAt->copy()->setTimezone(self::TZ);
        }

        if ($scheduledAt instanceof \DateTimeInterface) {
            return Carbon::instance($scheduledAt)->setTimezone(self::TZ);
        }

        if (! is_string($scheduledAt) || trim($scheduledAt) === '') {
            return null;
        }

        return Carbon::parse($scheduledAt, self::TZ);
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    public static function buildScheduleWindow(?Carbon $scheduledAt, int $durationMinutes): array
    {
        $scheduleStart = $scheduledAt?->copy();
        $scheduleEnd = $scheduleStart ? $scheduleStart->copy()->addMinutes($durationMinutes) : null;

        return [$scheduleStart, $scheduleEnd];
    }

    public static function resolveOrCreatePatient(User $user): Patient
    {
        $patient = $user->patient;
        if ($patient) {
            return $patient;
        }

        return Patient::query()->create([
            'user_id' => $user->id,
            'medical_record_number' => null,
            'nik' => null,
            'birth_date' => null,
            'address' => null,
        ]);
    }

    public static function createPendingAppointment(Patient $patient, Service $service, ?Carbon $scheduledAt, ?Carbon $scheduleStart, ?Carbon $scheduleEnd): Appointment
    {
        $payload = [
            'service_id' => $service->id,
            'scheduled_date' => $scheduledAt?->toDateString(),
            'status' => AppointmentStatus::PENDING,
            'snap_token' => null,
            'patient_id' => $patient->id,
            'scheduled_start' => $scheduleStart ? $scheduleStart->format('H:i:s') : null,
            'scheduled_end' => $scheduleEnd ? $scheduleEnd->format('H:i:s') : null,
        ];

        if (Schema::hasColumn('appointments', 'priority_id')) {
            $payload['priority_id'] = $service->priority_id;
        }

        return Appointment::create($payload);
    }

    public static function buildMidtransSnapParams(User $user, Appointment $booking, Service $service): array
    {
        $grossAmount = (int) ($service->price ?? 0);
        $itemName = (string) ($service->name ?? 'Layanan');

        if (Schema::hasColumn('appointments', 'dp_amount')) {
            $dpAmount = (float) ($booking->dp_amount ?? 0);
            if ($dpAmount > 0) {
                $grossAmount = (int) round($dpAmount);

                if (Schema::hasColumn('appointments', 'dp_percentage')) {
                    $dpPercentage = (float) ($booking->dp_percentage ?? 0);
                    if ($dpPercentage > 0) {
                        $itemName = 'DP ('.rtrim(rtrim(number_format($dpPercentage, 2, '.', ''), '0'), '.').'%) - '.$itemName;
                    } else {
                        $itemName = 'DP - '.$itemName;
                    }
                } else {
                    $itemName = 'DP - '.$itemName;
                }
            }
        }

        return [
            'transaction_details' => [
                'order_id' => $booking->code,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->hp,
            ],
            'item_details' => [
                [
                    'id' => $service->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => $itemName,
                ],
            ],
        ];
    }

    public static function notifyBookingCreated(User $user, Appointment $booking): void
    {
        $user->notify(new GenericDatabaseNotification(
            message: 'Booking berhasil dibuat. Silakan lakukan pembayaran untuk konfirmasi.',
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));
    }

    public static function notifyBookingCancelledByPayment(Appointment $booking): void
    {
        $booking->loadMissing('patient.user');

        $booking->patient?->user?->notify(new GenericDatabaseNotification(
            message: 'Pembayaran dibatalkan / kedaluwarsa. Booking Anda dibatalkan.',
            kind: NotificationType::Cancelled->value,
            extra: ['booking_id' => $booking->id, 'code' => $booking->code],
        ));
    }

    public static function notifyBookingScheduled(Appointment $booking, string $formattedDateTime): void
    {
        $booking->patient?->user?->notify(new GenericDatabaseNotification(
            message: "Anda telah dijadwalkan untuk appointment {$booking->code} pada {$formattedDateTime}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));
    }

    public static function notifyDoctorScheduled(int $doctorUserId, Appointment $booking, string $formattedDateTime): void
    {
        $doctorUser = User::query()->find($doctorUserId);
        $doctorUser?->notify(new GenericDatabaseNotification(
            message: "Anda dijadwalkan untuk appointment {$booking->code} pada {$formattedDateTime}.",
            kind: NotificationType::BookingCreated->value,
            extra: [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ],
        ));
    }

    public static function resolveOrCreateUser(string $name, ?string $email, string $phone): User
    {
        $hp = trim($phone);
        $hpDigits = preg_replace('/\D+/', '', $hp) ?: (string) Str::random(10);

        $resolvedEmail = $email;
        if (! is_string($resolvedEmail) || $resolvedEmail === '') {
            $resolvedEmail = 'guest+'.$hpDigits.'@example.test';
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
            $uniqueEmail = 'guest+'.$hpDigits.'+'.$counter.'@example.test';
            $counter++;
        }

        $uniqueHp = $hp !== '' ? $hp : $hpDigits;
        $hpCounter = 2;
        while (User::query()->where('hp', $uniqueHp)->exists()) {
            $uniqueHp = $hpDigits.'-'.$hpCounter;
            $hpCounter++;
        }

        return User::query()->create([
            'name' => $name,
            'email' => $uniqueEmail,
            'hp' => $uniqueHp,
            'password' => bcrypt(Str::random(32)),
        ]);
    }

    public static function findNextAvailableAssignment(Carbon $startDate, int $durationMinutes, int $excludeAppointmentId, int $maxDays = 30): ?array
    {
        $tz = self::TZ;

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

                $scheduledDate = Carbon::parse($date->toDateString().' '.$slot['time'], $tz);
                $assignment = self::findAvailableAssignment($scheduledDate, $durationMinutes, $excludeAppointmentId);
                if ($assignment) {
                    return $assignment;
                }
            }
        }

        return null;
    }

    public static function findAvailableAssignment(Carbon $scheduledDate, int $durationMinutes, int $excludeAppointmentId): ?array
    {
        $tz = self::TZ;
        $start = $scheduledDate->copy()->setTimezone($tz);
        $end = $start->copy()->addMinutes($durationMinutes);

        $weekday = $start->dayOfWeek; // 0=Sunday..6=Saturday
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

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
                ? Carbon::parse($bDateString.' '.$b->scheduled_start, $tz)
                : null;

            if (! $bStart) {
                continue;
            }

            $bEnd = ($bDateString && $b->scheduled_end)
                ? Carbon::parse($bDateString.' '.$b->scheduled_end, $tz)
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

        // Pilih dokter aktif yang punya availability di weekday & jam tersebut, dan tidak sedang sibuk.
        $doctorUserId = Doctor::query()
            ->where('is_active', true)
            ->whereHas('doctorAvailabilities', function ($q) use ($weekday, $startTime, $endTime) {
                $q->where('is_active', true)
                    ->where('weekday', $weekday)
                    ->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime);
            })
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
            'doctor_user_id' => (int) $doctorUserId,
            'room_id' => $room->id,
        ];
    }
}
