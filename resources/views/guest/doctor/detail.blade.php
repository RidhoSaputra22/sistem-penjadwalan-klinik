<?php

use Livewire\Volt\Component;
use App\Models\Doctor;

new class extends Component {
    public string $slug;
    public string $tab = 'jadwal';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function with(): array
    {
        $doctor = Doctor::query()
            ->where('slug', $this->slug)
            ->with([
                'user',
                'services' => fn ($q) => $q->orderBy('name'),
                'doctorAvailabilities' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('weekday')
                    ->orderBy('start_time'),
            ])
            ->firstOrFail();

        return [
            'doctor' => $doctor,
        ];
    }
}; ?>

<div>
    @livewire('layouts.navbar')

    <section class="min-h-screen max-w-7xl mx-auto px-6 lg:px-10 py-8">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-primary">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/30"></div>
                <div class="absolute -bottom-28 -right-28 h-96 w-96 rounded-full bg-white/20"></div>
            </div>

            <div class="relative px-6 lg:px-10 pt-10 pb-16 text-white">
                <div class="flex flex-col lg:flex-row gap-8 lg:items-center">
                    <div class="flex items-center gap-5">
                        <div class="shrink-0">
                            <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                                class="w-24 h-24 rounded-full object-cover ring-4 ring-white/40 bg-white/10">
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-2xl lg:text-3xl font-semibold truncate">
                                {{ $doctor->user?->name }}
                            </h1>
                            <div class="mt-2 space-y-1 text-sm text-white/90">
                                <div class="flex items-center gap-2">
                                    @include('components.icons.kardiogram')
                                    <span class="truncate">
                                        Sp. {{ $doctor->specialization }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @include('components.icons.clinic')
                                    <span class="truncate">Klinik Goaria</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:ml-auto flex items-center gap-2">
                        <button type="button"
                            class="inline-flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/15 border border-white/20 px-3 py-2 transition">
                            @include('components.icons.chat-bubble', ['size' => 'size-5'])
                        </button>
                        <button type="button"
                            class="inline-flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/15 border border-white/20 px-3 py-2 transition">
                            @include('components.icons.share', ['size' => 'size-5'])
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs + Content --}}
        <div class="mt-6 flex gap-6">
            <div class="flex-2">
                <div class="border-b border-gray-200">
                    <div class="flex gap-6 text-sm">
                        <button wire:click="setTab('jadwal')"
                            class="py-3 -mb-px border-b-2 {{ $tab === 'jadwal' ? 'border-primary text-primary font-semibold' : 'border-transparent text-gray-600 hover:text-primary' }} transition">
                            Jadwal
                        </button>
                        <button wire:click="setTab('profil')"
                            class="py-3 -mb-px border-b-2 {{ $tab === 'profil' ? 'border-primary text-primary font-semibold' : 'border-transparent text-gray-600 hover:text-primary' }} transition">
                            Profil
                        </button>
                        <button wire:click="setTab('artikel')"
                            class="py-3 -mb-px border-b-2 {{ $tab === 'artikel' ? 'border-primary text-primary font-semibold' : 'border-transparent text-gray-600 hover:text-primary' }} transition">
                            Artikel
                        </button>
                    </div>
                </div>

                <div wire:loading.class="opacity-50 pointer-events-none">
                    {{-- Tab content --}}
                    @if ($tab === 'jadwal')
                    <div class="mt-6 space-y-6">
                        @forelse ($doctor->services as $service)
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $service->name }}</div>
                            @if ($service->description)
                            <div class="mt-2 rounded-lg bg-primary/10 px-4 py-3 text-sm text-gray-700">
                                {{ $service->description }}
                            </div>
                            @endif

                            <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5" class="size-5 text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="font-medium">Jadwal Praktik</span>
                                </div>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @forelse ($doctor->doctorAvailabilities as $avail)
                                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $avail->weekday?->getLabel() ?? 'Hari' }}
                                            </div>
                                            <div class="text-sm text-gray-700">
                                                {{ substr($avail->start_time, 0, 5) }} -
                                                {{ substr($avail->end_time, 0, 5) }}
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">Klinik Goaria</div>
                                    </div>
                                    @empty
                                    <div class="col-span-full text-sm text-gray-600">
                                        Jadwal belum tersedia.
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-gray-600">
                            Belum ada layanan untuk dokter ini.
                        </div>
                        @endforelse
                    </div>
                    @elseif ($tab === 'profil')
                    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 space-y-4">
                        <div class="text-lg font-semibold text-gray-900">Profil Dokter</div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500">Nama</div>
                                <div class="font-medium text-gray-900">{{ $doctor->user?->name }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Title</div>
                                <div class="font-medium text-gray-900">{{ $doctor->user?->title ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Spesialisasi</div>
                                <div class="font-medium text-gray-900">{{ $doctor->specialization ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Status</div>
                                <div class="font-medium text-gray-900">{{ $doctor->is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">SIP</div>
                                <div class="font-medium text-gray-900">{{ $doctor->sip_number ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">STR</div>
                                <div class="font-medium text-gray-900">{{ $doctor->str_number ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Jenis Kelamin</div>
                                <div class="font-medium text-gray-900">
                                    {{ $doctor->gender === 'male' ? 'Laki-laki' : ($doctor->gender === 'female' ? 'Perempuan' : '-') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Tanggal Lahir</div>
                                <div class="font-medium text-gray-900">
                                    {{ $doctor->birth_date?->format('d M Y') ?: '-' }}
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <div class="text-gray-500">Alamat</div>
                                <div class="font-medium text-gray-900">{{ $doctor->address ?: '-' }}</div>
                            </div>
                            <div class="sm:col-span-2">
                                <div class="text-gray-500">Catatan</div>
                                <div class="font-medium text-gray-900">{{ $doctor->notes ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 text-gray-600">
                        Belum ada artikel.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Side card --}}
            @if (!auth()->check())
            <div class="flex-1">
                @livewire('user.auth.login')

            </div>

            @endif

        </div>
    </section>

    @livewire('layouts.footter')
</div>
