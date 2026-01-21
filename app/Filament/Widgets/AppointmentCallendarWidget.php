<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\WithPagination;

class AppointmentCallendarWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithPagination;

    protected string $view = 'filament.widgets.appointment-callendar-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public string $selectedStatus = AppointmentStatus::CONFIRMED->value;

    public array $statusOptions = [];

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->statusOptions = AppointmentStatus::asArray();
    }

    public function getHeading(): HtmlString
    {
        return new HtmlString('
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold">Jadwal Pertemuan Dokter</h1>
                    <p class="text-sm text-gray-500">
                        Berikut adalah jadwal pertemuan dokter
                    </p>
                </div>

                <button
                    wire:click="mountAction(\'callNextInQueue\')"
                    type="button"
                    class="text-sm bg-red-600 text-white px-4 py-2 rounded-md
                           hover:bg-red-500 focus:outline-none focus:ring-2
                           focus:ring-red-500 focus:ring-offset-2"
                >
                    Panggil Antrian Selanjutnya
                </button>
            </div>
        ');
    }

    protected function callNextInQueueAction(): Action
    {
        return Action::make('callNextInQueue')
            ->label('Panggil Antrian Selanjutnya')
            ->modalHeading('Panggil Antrian Selanjutnya')
            ->modalDescription('Apakah Anda yakin ingin memanggil antrian berikutnya?')
            ->color('danger')
            ->icon('heroicon-o-megaphone')
            ->requiresConfirmation()
            ->action(function (): void {
                $nextAppointment = Appointment::query()
                    ->where('status', AppointmentStatus::CONFIRMED)
                    ->orderBy('scheduled_date')
                    ->orderBy('scheduled_start')
                    ->first();

                if (! $nextAppointment) {
                    Notification::make()
                        ->title('Tidak ada antrian berikutnya.')
                        ->warning()
                        ->send();

                    return;
                }

                $nextAppointment->update([
                    'status' => AppointmentStatus::ONGOING,
                ]);
            });
    }

    public function filterByStatus(string $selectedStatus): void
    {
        // Implement filtering logic here
        $this->selectedStatus = $selectedStatus;
        $this->resetPage();

    }

    public function getCountByStatus(string $status): int
    {
        return Appointment::query()
            ->select('appointments.*')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('priorities', 'services.priority_id', '=', 'priorities.id')
            ->with(['doctor', 'patient.user', 'service.priority', 'room'])
            ->where('appointments.status', $status)
            ->whereDate('appointments.scheduled_date', now()->toDateString().'')

            ->orderBy('appointments.scheduled_date', 'ASC')
            ->orderBy('priorities.level', 'ASC')->count();
    }

    public function render(): View
    {
        $appointments = Appointment::query()
            ->select('appointments.*')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('priorities', 'services.priority_id', '=', 'priorities.id')
            ->with(['doctor', 'patient.user', 'service.priority', 'room'])
            ->where('appointments.status', $this->selectedStatus)
            ->whereDate('appointments.scheduled_date', now()->toDateString().'')

            ->orderBy('appointments.scheduled_date', 'ASC')
            ->orderBy('priorities.level', 'ASC')
            ->paginate(5);

        return view($this->view, compact('appointments'));
    }
}
