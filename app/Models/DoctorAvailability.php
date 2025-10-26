<?php

namespace App\Models;

use App\Models\User;
use App\Enums\WeekdayEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAvailability extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weekday',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'weekday' => WeekdayEnum::class,
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
