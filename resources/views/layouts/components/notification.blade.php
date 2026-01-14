<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //

    protected $listeners = [
        'notification-updated' => '$refresh',
    ];

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        $this->dispatch('notification-updated');
    }

    public function with()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->where('read_at', null)->latest()->take(5)->get();


        return [
            'notifications' => $notifications,
        ];

    }

}; ?>

<div>
    @component('components.dropdown', [
    'align' => 'right',
    'width' => 'min-w-sm',
    'useDownArrow' => false,
    'onCloseCallback' => 'Livewire.dis("notification-updated")',

    ])

    @slot('trigger')
    <div class="relative">
        @include('components.icons.bell')
        @if (count($notifications) > 0)
        <span
            class="absolute -top-2 -right-1.5 text-sm font-light w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center">{{ count($notifications) }}</span>

        @endif

    </div>
    @endslot

    @slot('content')
    <div class="min-h-2xl">
        <div class="px-4 py-2 border-b border-gray-300 flex justify-between items-center">
            <h3 class="font-semibold text-lg">Notifikasi</h3>
            <button wire:click="markAllAsRead" class="text-sm font-light text-primary hover:underline">Tandai
                dibaca</button>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @if (count($notifications) > 0)
            @foreach ($notifications as $notification)
            <div class="px-4 py-3 border-b border-gray-300 hover:bg-gray-100">
                <p class="text-sm">{{ $notification->data['message'] }}</p>
                <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>


            </div>
            @endforeach
            @else
            <div class="px-4 py-3">
                <p class="text-sm text-gray-500">Tidak ada notifikasi baru.</p>
            </div>
            @endif
        </div>

    </div>
    @endslot

    @endcomponent
</div>
