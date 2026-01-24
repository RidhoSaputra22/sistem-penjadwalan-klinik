<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $tab = 'profile';

    public function mount(string $tab = 'profile'): void
    {
        $this->tab = in_array($tab, ['profile', 'history'], true) ? $tab : 'profile';
    }
}; ?>


<div class="space-y-3">
    <div class="p-6 ">
        <p class="text-sm text-gray-500">Dashboard</p>
        <p class="font-semibold text-lg">Akun Saya</p>
    </div>

    <div class="p-4  space-y-2">
        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'profile']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'profile' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Profil
        </a>

        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'history']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'history' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Riwayat
        </a>

        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class=" font-medium block w-full text-start px-3 py-2 hover:bg-gray-100 text-red-600">
                Logout
            </button>
        </form>


    </div>
</div>
