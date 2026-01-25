<?php

use App\Models\Category;
use App\Models\Service;
use Livewire\Volt\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new class extends Component
{
    //

    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedCategorySlug = null;

    public ?string $selectedDuration = null;

    public ?string $selectedHarga = null;

    public ?string $selectedSortBy = null;

    public function mount()
    {
        if (request()->has('category')) {
            $this->selectedCategorySlug = request('category');
        }
    }

    /**
     * Auto reset pagination ketika filter berubah
     */
    public function updated($property)
    {
        if (in_array($property, [
            'search',
            'selectedCategorySlug',
            'selectedHarga',
            'selectedSortBy',
        ])) {
            $this->resetPage();
        }
    }

    /**
     * Query produk (AMAN & TER-GROUPING)
     */
    public function getPackage()
    {
        $query = Service::query()
            ->with('category');

        // Filter Kategori
        if ($this->selectedCategorySlug) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->selectedCategorySlug);
            });
        }

        // Filter Duration
        if ($this->selectedDuration) {
            $query->where('duration_minutes', $this->selectedDuration);
        }

        // Filter harga
        if ($this->selectedHarga === 'low_to_high') {
            $query->orderBy('price', 'asc');
        } elseif ($this->selectedHarga === 'high_to_low') {
            $query->orderBy('price', 'desc');
        }

        // Sorting
        if ($this->selectedSortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($this->selectedSortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        }

        // Search (GROUPED)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        return $query->paginate(10);
    }

    public function with()
    {

        $availableDurations = Service::select('duration_minutes')
            ->distinct()
            ->orderBy('duration_minutes', 'asc')
            ->get()
            ->pluck('duration_minutes');

        $availableCategory = Category::all();

        return [
            'services' => $this->getPackage(),
            'durations' => $availableDurations,
            'categories' => $availableCategory,
        ];
    }
}; ?>


<section class="p-12 min-h-screen " id="#paginated-posts">
    <div class="flex gap-24">
        {{-- FILTER --}}
        <div class="flex-1 bg-white rounded-2xl">
            <div class="fixed">
                <h1 class="text-xl font-semibold mb-6">Filter Produk</h1>

                <div class="space-y-6">
                    @component('components.form.select', [
                    'label' => 'Kategori',
                    'wireModel' => 'selectedCategorySlug',
                    'default' => [
                    'label' => 'Semua Kategori',
                    'value' => '',
                    ],
                    'options' => $categories->map(fn ($c) => [
                    'label' => $c->name,
                    'value' => $c->slug,
                    ]),
                    ]) @endcomponent
                    @component('components.form.select', [
                    'label' => 'Durasi',
                    'wireModel' => 'selectedDuration',
                    'default' => [
                    'label' => 'Semua Durasi',
                    'value' => '',
                    ],
                    'options' => $durations->map(fn ($d) => [
                    'label' => $d . ' menit',
                    'value' => $d,
                    ]),
                    ]) @endcomponent

                    @component('components.form.select', [
                    'label' => 'Harga',
                    'wireModel' => 'selectedHarga',
                    'options' => [
                    ['label' => 'Semua Harga', 'value' => ''],
                    ['label' => 'Rendah ke Tinggi', 'value' => 'low_to_high'],
                    ['label' => 'Tinggi ke Rendah', 'value' => 'high_to_low'],
                    ],
                    ]) @endcomponent

                    @component('components.form.select', [
                    'label' => 'Urutkan',
                    'wireModel' => 'selectedSortBy',
                    'options' => [
                    ['label' => 'Terbaru', 'value' => 'newest'],
                    ['label' => 'Terlama', 'value' => 'oldest'],
                    ],
                    ]) @endcomponent
                </div>
            </div>
        </div>
        <div class="flex-5 space-y-14 ">
            <div>
                <input type="text" wire:model.live.debounce.500ms="search"
                    class="border border-gray-300 rounded px-4 py-2 w-full" placeholder="Masukkan nama produk...">
            </div>
            {{ $services->links(data: ['scrollTo' => '#paginated-posts']) }}

            <div class="grid grid-cols-4 gap-10 " wire:loading.class="opacity-60 bg-white animate-pulse">
                @forelse ($services as $service)
                <a href="{{ route('guest.booking', ['slug' => $service->slug]) }}" class="">
                    <div class="relative">
                        <img src="{{ Storage::url($service->photo ?? 'services/services-placeholder.jpg') }}" alt=""
                            class="rounded-xl w-full h-60 object-cover">
                        <div
                            class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                            {{ $service->category->name }}</div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                            {{ $service->name }}
                        </h1>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($service->price, 0, ',', ',') }}
                        </h1>
                        <div class="flex gap-2 text-primary items-center">
                            @component('components.icons.clock')

                            @endcomponent

                            <div>{{ $service->duration_minutes }} menit</div>


                        </div>
                    </div>
                </a>
                @empty
                <p class="text-center col-span-4 text-lg font-light">Produk tidak ditemukan</p>
                @endforelse
            </div>

        </div>
    </div>
</section>