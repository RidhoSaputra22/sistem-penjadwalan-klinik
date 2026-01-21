<?php

namespace App\Filament\Resources\Appointments\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Holiday;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarResource;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class AppointmentCallendarWidget extends CalendarWidget
{
    protected static ?string $pollingInterval = null;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected ?string $locale = 'ID';

    public function getHeading(): string|HtmlString
    {
        return new HtmlString('
            <div>
                <h1 style="font-weight: 600; font-size: 20px;">Jadwal Pertemuan Dokter</h1>
                <p style="font-weight: 300; font-size: 12.36px;">Berikut adalah jadwal pertemuan dokter</p>
            </div>
        ');
    }

    /**
     * Generate recurring weekly doctor schedules for the entire month.
     */
    protected function getEvents(FetchInfo $info): Collection|array
    {

        return collect()
            ->push(...Appointment::query()
                ->where('status', AppointmentStatus::CONFIRMED)
                ->get())
            ->push(...Holiday::query()->get());

    }

    protected function getResources(): Collection|array|Builder
    {
        return [
            CalendarResource::make('baz') // This has to be unique ID
                ->title('My resource'),
        ];
    }

    protected function getCalendarOptions(): array
    {
        return [
            'locale' => 'id', // Bahasa Indonesia
        ];
    }
}
