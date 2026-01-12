<?php

namespace App\Models;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medical_record_number',
        'nik',
        'birth_date',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];


    protected static function booted()
    {
        static::creating(function ($patient) {
            if (empty($patient->medical_record_number)) {
                $latestPatient = Patient::latest('id')->first();
                $nextNumber = $latestPatient ? intval(substr($latestPatient->medical_record_number, -6)) + 1 : 1;
                $patient->medical_record_number = 'MRN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
