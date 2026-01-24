<?php

use App\Models\Service;
use App\Services\Nlp\ServiceRecommender;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $isOpen = false;

    public string $keluhan = '';

    /**
     * @var array<int, array{id:int, name:string, slug:string, description:?string, score:float}>
     */
    public ?Collection $recommendations = null;

    public ?string $recommendationMessage = null;

    public function resetRecommendation(): void
    {
        $this->recommendations = null;
        $this->recommendationMessage = null;
    }

    public function getServiceRecommendation(): void
    {
        $this->validate([
            'keluhan' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'keluhan.required' => 'Silakan tulis keluhan terlebih dahulu.',
            'keluhan.min' => 'Keluhan terlalu singkat. Jelaskan sedikit lebih detail.',
            'keluhan.max' => 'Keluhan terlalu panjang. Maksimal 500 karakter.',
        ]);

        $services = Service::query()
            ->select(['id', 'name', 'slug', 'description', 'duration_minutes', 'price'])
            ->get();

        if ($services->isEmpty()) {
            $this->recommendations = null;
            $this->recommendationMessage = 'Belum ada data layanan untuk direkomendasikan.';

            return;
        }

        $documents = $services
            ->map(function (Service $s) {
                $text = trim(($s->name ?? '').' '.($s->description ?? ''));

                return [
                    'id' => $s->id,
                    'text' => $text,
                    'meta' => [
                        'name' => (string) $s->name,
                        'slug' => (string) $s->slug,
                        'description' => $s->description ? (string) $s->description : null,
                    ],
                ];
            })
            ->values()
            ->all();

        $ranked = (new ServiceRecommender)->rank($this->keluhan, $documents, limit: 3, minScore: 0.08);
        // dd($ranked);

        $recomendtId = collect($ranked)
            ->filter(fn ($r) => isset($r['meta']) && is_array($r['meta']))
            ->map(fn ($r) => [
                'id' => (int) $r['id'],
            ])
            ->values()
            ->all();

        $this->recommendations = Service::query()
            ->select(['id', 'name', 'slug', 'description', 'duration_minutes', 'price'])
            ->whereIn('id', $recomendtId)
            ->get();

        $this->recommendationMessage = empty($this->recommendations)
            ? 'Maaf, kami belum menemukan layanan yang cocok. Coba jelaskan gejala lebih spesifik.'
            : 'Berikut rekomendasi layanan yang paling sesuai dengan keluhan Anda:';
    }
}; ?>

<div x-data="{ isOpen: @entangle('isOpen') }">
    <button type="button" x-cloak x-show="!isOpen" @click="isOpen = true"
        class="fixed bottom-6 right-6 z-40 bg-blue-700 text-white rounded-bl-xl rounded-tl-xl rounded-tr-xl shadow-lg hover:bg-blue-800 transition">
        <div class="flex items-center gap-3 px-5 py-3">
            @include('components.icons.chat-bubble')
            Chat Keluhan Kamu disini
        </div>
    </button>

    @component('components.modal', [
    'name' => 'chat-modal',
    'maxWidth' => 'max-w-5xl',
    ])

    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Masukan Keluhan Anda</h1>
                <p class="font-light">Masukan keluhan anda agar kami dapat merekomendasikan layanan kami</p>
            </div>
            <button type="button" @click="isOpen = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        @component('components.form.textarea', [
        'placeholder' => 'Ketik pesan anda disini...',
        'wireModel' => 'keluhan',
        ])

        @endcomponent

        @if($recommendationMessage)
        <div class="mt-4 p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
            {{ $recommendationMessage }}
        </div>
        @endif

        @if(!empty($recommendations))
        <div class="mt-4 space-y-3 grid grid-cols-3 gap-12">
            @foreach($recommendations as $service)
            <a href="{{ route('guest.booking', $service->slug) }}" class="swiper-slide">
                <div class="relative">
                    <img src="{{ asset('images/services-placeholder.jpg') }}" alt=""
                        class="rounded-xl w-full h-60 object-cover">
                    <div
                        class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white flex gap-2 items-center ">
                        @include('components.icons.clock')
                        {{ $service->duration_minutes }} Menit
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                        {{ $service->name }}
                    </h1>
                    <p>

                        {{ Str::limit($service->description ?? 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, dolorem. ', 50, '...') }}
                    </p>
                    <h1 class="text-lg font-semibold mt-2">Rp.
                        {{ number_format(200000, 0, ',', ',') }}
                    </h1>

                </div>
            </a>
            @endforeach
        </div>
        @endif

        <div class="flex justify-end">
            @component('components.form.button', [
            'label' => 'Rekomendasikan',
            'type' => 'button',
            'wireClick' => 'getServiceRecommendation',
            'wireLoadingClass' => 'opacity-50',
            'class' => 'mt-4 ml-auto px-4 py-2 bg-primary text-white rounded-md hover:opacity-90',

            ])

            @endcomponent

            <button type="button" wire:click="resetRecommendation"
                class="mt-4 ml-3 px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Reset
            </button>
        </div>
    </div>

    @endcomponent
</div>