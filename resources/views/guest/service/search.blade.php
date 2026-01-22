<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div>
    @livewire('layouts.navbar')


    {{-- Content --}}
    @livewire('guest.service.banner')
    @livewire('guest.service.content')

    {{-- End Content --}}

    @livewire('guest.service.components.chat-modal')


    @livewire('layouts.footter')
</div>