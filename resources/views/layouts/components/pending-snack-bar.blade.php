<?php

use Carbon\Carbon;
use App\Models\Package;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;

new class extends Component {
    //
    public bool $isOpen = true;
    public int $appointmentCount = 0;

    public function mount(): void
    {
        $appointmentCount = Appointment::where('status', AppointmentStatus::PENDING)
            ->count();

        $this->appointmentCount = $appointmentCount;
        $this->isOpen = $appointmentCount > 0;
    }







}; ?>

<div x-data="{ isOpen: @entangle('isOpen') }"  class="fixed bottom-0 w-full">
    <div
    x-cloak
    x-show="isOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
    class="p-3  bg-primary  text-white flex items-center space-x-3">
    @include('components.icons.callendar', ['class' => 'size-6'])
    <a class=" font-semibold" href="{{ route('user.dashboard', ['tab' => 'history']) }}">
         {{ $appointmentCount }} reservasi menunggu konfirmasi!
        Cek disini
    </a>
</div>

</div>
