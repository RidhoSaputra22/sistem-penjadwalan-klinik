<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    //
    #[Url]
    public string $tab = 'profile';

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->dispatch('open-auth-modal', 'login');

            return;
        }

        if (! in_array($this->tab, ['profile', 'history'], true)) {
            $this->tab = 'profile';
        }
    }
}; ?>

<div>
    @livewire('layouts.navbar')

    <div class="min-h-screen p-12 flex gap-8">
        <div class="flex-1">
            @livewire('user.dashboard.sidebar', ['tab' => $tab])
        </div>

        <div class="flex-3">
            @if ($tab === 'history')
            @livewire('user.dashboard.history-page')

            @else
            @livewire('user.dashboard.profile-page')
            @endif
        </div>
    </div>

    {{-- @include('layouts.footter') --}}
</div>