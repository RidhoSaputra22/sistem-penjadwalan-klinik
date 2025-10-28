<?php

namespace App\Models;

use App\Models\User;
use App\Enums\ColorEnum;
use App\Models\Appointment;
use App\Helpers\CodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'duration_minutes',
        'description',
        'color',
    ];




    protected static function booted()
    {
        static::creating(function ($service) {
            if (empty($service->code)) {
                $service->code = CodeGenerator::service();
            }
        });
    }

    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_services', 'service_id', 'user_id')
            ->withPivot('priority')
            ->withTimestamps();
    }

    public function rrPointer()
    {
        return $this->hasOne(RrPointer::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
