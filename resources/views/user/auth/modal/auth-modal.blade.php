<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    //

    public bool $isOpen = false;
    public ?string $tab = null;

    public function mount(): void
    {
        $this->tab = null;
        $this->isOpen = false;

    }


    #[On('open-auth-modal')]
    public function openModal(?string $tab): void
    {
        $this->isOpen = true;
        $this->tab = $tab;
    }

    #[On('changeAuthModalTab')]
    public function changeTab(string $tab): void
    {
        $this->tab = $tab;
    }

    #[On('modal-closed')]
    public function onModalClosed(string $name): void
    {
        if ($name !== 'auth-modal') {
            return;
        }

        $this->isOpen = false;
        $this->tab = 'login';
    }


}; ?>

<div x-data="{ isOpen: @entangle('isOpen') }" >
    @component('components.modal', [
        'name' => 'auth-modal',
    ])

    @if ($this->tab === 'login')
        @livewire('user.auth.login')
    @elseif ($this->tab === 'regist')
        @livewire('user.auth.regist')
    @endif

    @endcomponent
</div>
