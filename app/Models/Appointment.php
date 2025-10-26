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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
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
        return $this->belongsTo(User::class, 'doctor_id')->where('role', UserRole::DOCTOR);
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
