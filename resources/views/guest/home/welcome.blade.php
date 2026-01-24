<?php

use Livewire\Volt\Component;

new class extends Component
{
    //

    public function with()
    {
        return [
            'services' => \App\Models\Service::all(),
        ];
    }
}; ?>

<div class="relative">
    @livewire('layouts.navbar')

    {{-- Content --}}
    @livewire('guest.home.components.banner')
    @livewire('guest.home.components.medical-services')
    @livewire('guest.home.components.doctors')
    @livewire('guest.home.components.services')
    @livewire('guest.home.components.about-us')
    @livewire('guest.home.components.testimoni')
    @livewire('guest.home.components.reservasi')
    @livewire('guest.home.components.berita')

    {{-- End Content --}}

    @livewire('layouts.footter')

    <!-- GoToTop -->



</div>
