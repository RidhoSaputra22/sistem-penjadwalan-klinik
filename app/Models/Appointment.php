<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Helpers\CodeGenerator;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model implements Eventable, Resourceable
{
    //
    use HasFactory;

    protected $fillable = [
        'code',
        'patient_id',
        'service_id',
        'priority_id',
        'doctor_id',
        'room_id',
        'scheduled_date',
        'scheduled_start',
        'scheduled_end',
        'original_scheduled_date',
        'original_scheduled_start',
        'original_scheduled_end',
        'status',
        'snap_token',
        'notes',
        'checked_in_at',
        'called_at',
        'service_started_at',
        'service_ended_at',
        'no_show_at',
        'rescheduled_count',
        'last_rescheduled_at',
    ];

    protected static function booted()
    {
        static::creating(function ($appointment) {
            if (empty($appointment->code)) {
                $appointment->code = CodeGenerator::appointment();
            }
        });
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $doctorName = $this->doctor?->name ?? 'Dokter';
        $serviceName = $this->service?->name ?? 'Layanan';
        $time = $this->scheduled_start ? ' pukul '.$this->scheduled_start : '';
        $title = trim($doctorName.' - '.$serviceName.$time, ' -');

        return CalendarEvent::make($this)
            ->title($title)
            ->start($this->scheduled_date)
            ->end($this->scheduled_date)
            ->backgroundColor($this->service?->color ?? 'red')
            ->allDay();
    }

    public function toCalendarResource(): CalendarResource
    {
        $resourceId = $this->code ?: (string) $this->getKey();

        return CalendarResource::make($resourceId)
            ->title($this->code ?: 'Appointment');
    }

    protected $casts = [
        'scheduled_date' => 'date',
        'status' => AppointmentStatus::class,
        'original_scheduled_date' => 'date',
        'checked_in_at' => 'datetime',
        'called_at' => 'datetime',
        'service_started_at' => 'datetime',
        'service_ended_at' => 'datetime',
        'no_show_at' => 'datetime',
        'last_rescheduled_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id')->where('role', UserRole::DOCTOR->value);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
