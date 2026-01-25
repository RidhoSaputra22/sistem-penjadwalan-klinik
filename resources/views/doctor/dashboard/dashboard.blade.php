<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url]
    public string $tab = 'bookings';

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('doctor.login');

            return;
        }

        if (Auth::user()?->role !== UserRole::DOCTOR) {
            $this->redirectRoute('user.dashboard');

            return;
        }

        if (! in_array($this->tab, ['bookings', 'reports'], true)) {
            $this->tab = 'bookings';
        }
    }
}; ?>

<div>
    @livewire('layouts.navbar')

    <div class="min-h-screen p-12 flex gap-8">
        <div class="flex-1">
            @livewire('doctor.dashboard.sidebar', ['tab' => $tab])
        </div>

        <div class="flex-3">
            @if ($tab === 'reports')
                @livewire('doctor.dashboard.reports-page')
            @else
                @livewire('doctor.dashboard.bookings-page')
            @endif
        </div>
    </div>
</div>
