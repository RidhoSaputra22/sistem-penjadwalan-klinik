<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RoundRobinScheduler;

class MakeRRSchedule extends Command
{
    protected $signature = 'schedule:rr {serviceId} {patientId} {date} {start_time} {--room=}';
    protected $description = 'Create appointment using Round Robin';

    public function handle(RoundRobinScheduler $scheduler)
    {
        try {
            $serviceId = $this->argument('serviceId');
            $patientId = $this->argument('patientId');
            $date = $this->argument('date'); // Y-m-d
            $time = $this->argument('start_time'); // H:i:s
            $room = $this->option('room');

            $appointment = $scheduler->schedule((int)$serviceId, (int)$patientId, $date, $time, $room ? (int)$room : null);

            if (!$appointment) {
                $this->error('Unable to schedule appointment. No available doctor / holiday / conflicts.');
                return 1;
            }
            $this->info('Appointment created: ' . $appointment->code . ' Doctor ID: ' . $appointment->doctor_id);
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
