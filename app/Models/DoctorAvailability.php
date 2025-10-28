<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\WeekdayEnum;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAvailability extends Model implements Eventable
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
        return $this->belongsTo(User::class, 'user_id')->whereRole(UserRole::DOCTOR);
    }

    public function toCalendarEvent(): CalendarEvent
    {
        // For eloquent models, make sure to pass the model to the constructor
        $year = now()->year;
        $month = now()->month;
        $startDateTime = Carbon::parse($this->start_time);
        $endDateTime = Carbon::parse($this->end_time);

        return CalendarEvent::make($this)
            ->title($this->doctor->name)
            ->start(Carbon::create(year: $year, month: $month, day: $this->weekday->value, hour: $startDateTime->hour, minute: $startDateTime->minute))
            ->end(Carbon::create(year: $year, month: $month, day: $this->weekday->value, hour: $endDateTime->hour, minute: $endDateTime->minute))

        ;
    }
}
