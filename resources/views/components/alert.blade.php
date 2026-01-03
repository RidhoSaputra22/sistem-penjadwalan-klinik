<?php

use Livewire\Attributes\On;
use App\Enums\AlertTypeEnum;
use Livewire\Volt\Component;
use Illuminate\Console\View\Components\Alert;



new class extends Component {
    //

    public bool $isOpen = false;
    public AlertTypeEnum $type = AlertTypeEnum::INFO;
    public string $message = 'test alert message';
    public string $description = 'test alert description';

    public function mount(): void
    {
        $payload = session()->pull('alert');

        if (! is_array($payload)) {
            return;
        }

        $type = AlertTypeEnum::tryFrom((string) ($payload['type'] ?? '')) ?? AlertTypeEnum::INFO;
        $message = (string) ($payload['message'] ?? '');
        $description = (string) ($payload['description'] ?? '');

        if ($message === '') {
            return;
        }

        $this->openAlert($type, $message, $description);
    }



    #[On('open-alert')]
    public function openAlert(AlertTypeEnum $type, string $message, string $description): void
    {

        $this->isOpen = true;
        $this->type = $type;
        $this->message = $message;
        $this->description = $description;
    }


}; ?>

<div x-data="{ isOpen: @entangle('isOpen') }">
    @component('components.modal', [
    'name' => 'alert-modal'
    ])

    <div
        x-data="{
            progress: 0,
            intervalId: null,

            start() {
                // reset each time modal opens
                this.progress = 0;

                if (this.intervalId) clearInterval(this.intervalId);

                this.intervalId = setInterval(() => {
                    this.progress = Math.min(100, this.progress + 1);

                    if (this.progress >= 100) {
                        clearInterval(this.intervalId);
                        this.intervalId = null;
                        isOpen = false;
                    }
                }, 10);
            },
        }"
        x-cloak
        x-effect="if (isOpen) start()"
        x-on:open-modal.window="if ($event.detail === 'alert-modal') start()"
    >
        <div class="relative p-6">
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

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $message }}</h2>
                    @if ($description)
                    <p class=" text-sm text-gray-600">
                        {{ $description }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
       <div class="rounded-b-lg overflow-hidden">
         <div  class=" h-1.5  bg-primary " :style="`width: ${progress}%`" >
        </div>
       </div>
    </div>

    @endcomponent
</div>
