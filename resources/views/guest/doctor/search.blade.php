<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Doctor;
use App\Models\Service;

new class extends Component {


    public string $q = '';
    public ?int $service = null;

    public function mount(): void
    {
        $this->q = request()->query('q', '');
    }

    public function selectService(?int $serviceId): void
    {
        $this->service = $serviceId;
    }

    public function with(): array
    {
        $popularServices = Service::query()
            ->withCount('doctors')
            ->orderByDesc('doctors_count')
            ->limit(5)
            ->get();

        $doctors = Doctor::query()
            ->select('doctors.*')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->with([
                'user',
                'services' => fn ($q) => $q->orderBy('name'),
            ])
            ->when($this->service, function (Builder $q) {
                $q->whereHas('services', fn (Builder $s) => $s->whereKey($this->service));
            })
            ->when($this->q !== '', function (Builder $q) {
                $term = trim($this->q);
                $q->where(function (Builder $inner) use ($term) {
                    $inner->where('users.name', 'like', "%{$term}%")
                        ->orWhere('users.title', 'like', "%{$term}%")
                        ->orWhereHas('services', fn (Builder $s) => $s->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('users.name')
            ->limit(12)
            ->get();

        return [
            'popularServices' => $popularServices,
            'doctors' => $doctors,
        ];
    }
}; ?>

<div>
    @livewire('layouts.navbar')

    {{-- Content --}}
    <section class="max-w-7xl mx-auto px-6 lg:px-10 py-8 ">
        {{-- Search Bar --}}
        <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
            <button type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white hover:shadow-md transition">
                <span class="text-sm font-medium">Filter</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" class="size-5 text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.5 6h10.75M10.5 18h10.75M2.75 6h1.5m-1.5 12h1.5M6.5 6.25a1.75 1.75 0 1 0 0-.5m0 12a1.75 1.75 0 1 0 0-.5" />
                </svg>
            </button>

            <div  class="flex-1">
                <div class="flex items-stretch">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input type="text" wire:model.live.debounce.750="q"
                            class="w-full h-full pl-10 pr-3 py-2 rounded-l-lg border border-gray-200 bg-white placeholder:text-gray-400 focus:shadow-md focus:outline-none"
                            placeholder="Nama Dokter, Spesialis, atau Rumah Sakit">
                    </div>
                    <button type="submit" wire:click="$refresh()"
                        class=" inline-flex items-center justify-center gap-2 px-5 py-2 rounded-r-lg bg-primary text-white hover:shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span class="text-sm font-medium">Cari</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Popular chips --}}
        <div class="mt-6 flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="text-sm font-medium text-gray-500 shrink-0">Paling banyak dicari</div>

            <div class="flex flex-wrap gap-2">
                <button wire:click="selectService(null)"
                    class="{{ $service === null ? 'border-primary text-primary' : '' }} px-4 py-2 rounded-full border border-gray-200 bg-white text-sm text-gray-700 hover:border-primary hover:text-primary transition">
                    Semua
                </button>
                @foreach ($popularServices as $serviceItem)
                    <button wire:click="selectService({{ $serviceItem->id }})"
                        class="{{ $serviceItem->id === $service ? 'border-primary text-primary' : '' }} px-4 py-2 rounded-full border border-gray-200 bg-white text-sm text-gray-700 hover:border-primary hover:text-primary transition">
                        {{ Str::limit($serviceItem->name, 20) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Results --}}
        <div class="mt-8 min-h-screen" wire:loading.class="opacity-50 pointer-events-none">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse ($doctors as $doctor)
                    @php
                        $primaryService = $doctor->services->first();
                        $secondaryService = $doctor->services->skip(1)->first();
                    @endphp
                    <a href="{{ route('guest.doctor.detail', ['slug' => $doctor->slug]) }}"
                        class="group rounded-xl border border-gray-200 bg-white hover:shadow-md transition overflow-hidden">
                        <div class="relative p-4">
                            <div class="absolute top-4 left-4">
                                <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-7 h-7 rounded-sm">
                            </div>
                            <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                                class="w-full aspect-square object-cover rounded-lg bg-gray-50">
                        </div>
                        <div class="px-4 pb-4 space-y-2">
                            <h3 class="font-semibold text-gray-900 leading-snug truncate">
                                {{ $doctor->user?->name }}
                            </h3>

                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                @include('components.icons.kardiogram')
                                <div class="min-w-0">
                                    <div class="truncate">
                                        {{ ($primaryService?->name ?: 'Dokter') }}
                                    </div>

                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                @include('components.icons.clinic')
                                <span class="truncate">Klinik Goaria</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-lg border border-gray-200 bg-white p-6 text-center text-gray-600">
                        Tidak ada dokter yang sesuai.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- End Content --}}

    @livewire('layouts.footter')
</div>
