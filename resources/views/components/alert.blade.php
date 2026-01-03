<?php

use Livewire\Attributes\On;
use App\Enums\AlertTypeEnum;
use Livewire\Volt\Component;
use Illuminate\Console\View\Components\Alert;



new class extends Component {
    //

    public bool $isOpen = false;
    public AlertTypeEnum $type = AlertTypeEnum::INFO;
    public string $message = '';
    public string $description = '';

    #[On('open-alert')]
    public function openAlert(): void
    {
        $this->isOpen = true;
    }


}; ?>

<div x-data="{ isOpen: @entangle('isOpen') }">
    @component('components.modal', [
        'name' => 'alert-modal'
        ])

    <div>
        <div class="flex items-center gap-4">
            @if ($type === AlertTypeEnum::SUCCESS)
                <span class="text-green-500">
                    @include('components.icons.success', ['size' => 'size-8'])
                </span>
            @elseif ($type === AlertTypeEnum::ERROR)
                <span class="text-red-500">
                    @include('components.icons.error', ['size' => 'size-8'])
                </span>
            @elseif ($type === AlertTypeEnum::WARNING)
                <span class="text-yellow-500">
                    @include('components.icons.warning', ['size' => 'size-8'])
                </span>
            @else
                <span class="text-blue-500">
                    @include('components.icons.info', ['size' => 'size-8'])
                </span>
            @endif

            <h2 class="text-lg font-semibold text-gray-900">{{ $message }}</h2>
        </div>
        @if ($description)
            <p class="mt-4 text-sm text-gray-600">
                {{ $description }}
            </p>
        @endif

    </div>

    @endcomponent
</div>
