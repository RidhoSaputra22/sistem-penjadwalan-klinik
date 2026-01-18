<?php

namespace App\Models;

use App\Models\Room;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\Service;
use App\Helpers\CodeGenerator;
use App\Enums\AppointmentStatus;
use App\Helpers\AppointmentHelper;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model implements Eventable, Resourceable
{
    //
    use HasFactory;

    protected $fillable = [
        'code',
        'patient_id',
        'service_id',
        'doctor_id',
        'room_id',
        'scheduled_date',
        'scheduled_start',
        'scheduled_end',
        'status',
        'snap_token',
        'notes',
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
        $title = trim($doctorName . ' - ' . $serviceName, ' -');

        return CalendarEvent::make($this)
            ->title($title)
            ->start($this->scheduled_date)
            ->end($this->scheduled_date)
            ->backgroundColor($this->service?->color ?? 'primary')
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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
