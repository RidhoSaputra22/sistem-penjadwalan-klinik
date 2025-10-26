<?php

namespace App\Models;

use App\Models\Appointment;
use App\Helpers\CodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($room) {
            if (empty($room->code)) {
                $room->code = CodeGenerator::room();
            }
        });
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
