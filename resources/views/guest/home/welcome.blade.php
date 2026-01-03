<?php

use Livewire\Volt\Component;

new class extends Component {
    //

    public function with(){
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
    <div x-data="{
        atTop: true,
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        update() {
            this.atTop = window.pageYOffset < 100;
        }
    }" @scroll.window="update()" x-cloak x-show="!atTop" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4"
        class="fixed bottom-3 right-3 p-3 rounded-full bg-primary text-white flex justify-center items-center cursor-pointer z-50 shadow-lg"
        @click="scrollToTop()">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24      "
            stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>

    </div>

</div>
