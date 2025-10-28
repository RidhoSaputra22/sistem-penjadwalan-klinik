<?php

namespace App\Filament\Resources\Appointments\Widgets;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use App\Enums\AppointmentStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Guava\Calendar\Enums\Context;
use App\Models\DoctorAvailability;
use App\Models\Holiday;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\ValueObjects\CalendarResource;

class AppointmentCalendarWidget extends CalendarWidget
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
            ->push(...Appointment::query()->get())
            ->push(...Holiday::query()->get());
    }

    protected function getResources(): Collection | array | Builder
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
