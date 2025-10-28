<?php

namespace App\Filament\Resources\DoctorAvailabilities\Widgets;

use Carbon\Carbon;
use Carbon\WeekDay;
use Filament\Widgets\Widget;
use App\Models\DoctorAvailability;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DateClickInfo;


class DoctorAvailabilityCalendar extends CalendarWidget
{
    protected static ?string $pollingInterval = '5s';

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;
    protected bool $dateClickEnabled = true;



    public function getHeading(): string|HtmlString
    {
        return new HtmlString('
            <div>
                <h1 style="font-weight: 600; font-size: 20px;">Jadwal Ketersediaan Dokter</h1>
                <p style="font-weight: 300; font-size: 12.36px;">Berikut adalah jadwal ketersediaan dokter</p>
            </div>
        ');
    }

    /**
     * Generate recurring weekly doctor schedules for the entire month.
     */
    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $events = collect();

        // Get all active doctor availabilities
        $availabilities = DoctorAvailability::with('doctor')
            ->where('is_active', true)
            ->get();

        // Define the visible calendar date range (based on current view)
        $startOfRange = Carbon::parse($info->start);
        $endOfRange = Carbon::parse($info->end);

        foreach ($availabilities as $availability) {
            $startTime = Carbon::parse($availability->start_time);
            $endTime = Carbon::parse($availability->end_time);

            // Loop from the visible start date until the end of the range
            $currentDate = $startOfRange->copy();

            while ($currentDate->lte($endOfRange)) {
                // Match weekday (0 = Sunday, 1 = Monday, ...)
                if ($currentDate->dayOfWeek === (int) $availability->weekday->value) {
                    $start = $currentDate->copy()->setTime($startTime->hour, $startTime->minute);
                    $end = $currentDate->copy()->setTime($endTime->hour, $endTime->minute);

                    $events->push(
                        CalendarEvent::make($availability)
                            ->action('edit')
                            ->title("{$availability->doctor->name}")
                            ->start($start)
                            ->end($end)
                    );
                }

                $currentDate->addDay();
            }
        }

        return $events;
    }



    /**
     * Customize FullCalendar options
     */
}
