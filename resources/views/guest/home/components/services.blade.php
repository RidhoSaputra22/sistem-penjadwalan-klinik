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

<section class=" bg-primary w-full ">
    <div class="max-w-7xl m-auto py-24 px-10 space-y-10">
        <div class="flex-1  text-white w-4xl " data-aos="fade-up">
            <h1 class="text-xl font-light ">Layanan Kami</h1>
            <p class="text-4xl/loose font-semibold">Memastikan kesehatan anda adalah prioritas kami.</p>
            <p class="text-lg/relaxed font-light">
                Kami menyediakan berbagai layanan medis berkualitas tinggi yang dirancang untuk memenuhi kebutuhan
                kesehatan
                Anda dan keluarga.
            </p>
        </div>
        <div class="swiper serviceSwiper h-96 flex-2 " data-aos="fade-up">
            <div class="swiper-wrapper text-white ">
                @foreach ($services as $service)
                <a href="{{ route('guest.booking', $service->slug) }}" class="swiper-slide">
                    <div class="relative">
                        <img src="{{ Storage::url($service->photo ?? 'services/services-placeholder.jpg') }}" alt=""
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
                        <p class="text-sm font-light text-overflow-ellipsis">

                            {{ Str::limit($service->description ?? 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, dolorem. ', 50, '...') }}
                        </p>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($service->price, 0, ',', ',') }}
                        </h1>

                    </div>
                </a>
                @endforeach


            </div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="flex justify-end" data-aos="fade-up">
            <a href="{{ route('guest.service.search') }}"
                class=" mt-5 inline-block bg-white text-primary px-6 py-2 rounded-md font-medium hover:bg-gray-100 hover:-translate-y-1 transition">
                Lihat Semua Layanan
            </a>
        </div>
    </div>

</section>

@push('scripts')
<script>
const serviceSwiper = new Swiper(".serviceSwiper", {
    slidesPerView: 3,
    // centeredSlides: true,
    loop: true,
    speed: 700,
    spaceBetween: 30,

    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },



    pagination: {
        // el: ".swiper-pagination",

    },
});
</script>
@endpush
