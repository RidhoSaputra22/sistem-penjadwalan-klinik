<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model implements Eventable
{
    //
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'full_day',
    ];

    protected $casts = [
        'date' => 'date',
        'full_day' => 'boolean',
    ];

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->name)
            ->start($this->date)
            ->end($this->date)
            ->backgroundColor('red')
            ->allDay();
    }
}
