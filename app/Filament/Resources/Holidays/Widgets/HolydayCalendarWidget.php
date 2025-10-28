<?php

namespace App\Filament\Resources\Holidays\Widgets;

use Carbon\Carbon;
use Carbon\WeekDay;
use Filament\Widgets\Widget;
use App\Models\DoctorAvailability;
use App\Models\Holiday;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DateClickInfo;


class HolydayCalendarWidget extends CalendarWidget
{
    protected static ?string $pollingInterval = '5s';

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;
    protected bool $dateClickEnabled = true;



    public function getHeading(): string|HtmlString
    {
        return new HtmlString('
            <div>
                <h1 style="font-weight: 600; font-size: 20px;">Data Hari Libur Nasional</h1>
                <p style="font-weight: 300; font-size: 12.36px;">Berikut adalah data hari libur nasional</p>
            </div>
        ');
    }

    /**
     * Generate recurring weekly doctor schedules for the entire month.
     */
    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Holiday::query();
    }



    /**
     * Customize FullCalendar options
     */
}
