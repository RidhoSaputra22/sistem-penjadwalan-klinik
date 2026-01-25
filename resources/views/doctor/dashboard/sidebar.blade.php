<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $tab = 'bookings';

    public function mount(string $tab = 'bookings'): void
    {
        $this->tab = in_array($tab, ['bookings', 'reports'], true) ? $tab : 'bookings';
    }
}; ?>

<div class="space-y-3">
    <div class="p-6 ">
        <p class="text-sm text-gray-500">Dashboard</p>
        <p class="font-semibold text-lg">Dokter</p>
    </div>

    <div class="p-4 space-y-2">
        <a wire:navigate href="{{ route('doctor.dashboard', ['tab' => 'bookings']) }}"
           class="block px-3 py-2 rounded-sm {{ $tab === 'bookings' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Booking Saya
        </a>

        <a wire:navigate href="{{ route('doctor.dashboard', ['tab' => 'reports']) }}"
           class="block px-3 py-2 rounded-sm {{ $tab === 'reports' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Rekap / Laporan
        </a>

        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="font-medium block w-full text-start px-3 py-2 hover:bg-gray-100 text-red-600">
                Logout
            </button>
        </form>
    </div>
</div>
