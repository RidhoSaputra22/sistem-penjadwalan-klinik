<?php

use Livewire\Volt\Component;

new class extends Component
{
    //

    public function with()
    {
        $banner = [
            'images/medicald-1.jpg',
            'images/medicald-2.jpg',
            'images/medicald-3.jpg',
        ];

        return [
            'banner' => $banner,
            //
        ];
    }
}; ?>

<section class="">
    <!-- Swiper -->
    <div class="lg:p-12">
        <div class="swiper bannerSwiper lg:h-100  lg:rounded-2xl">
            <div class="w-full h-full swiper-wrapper">
                <div class="relative swiper-slide rounded-2xl overflow-hidden">
                    <img src="{{ asset('images/medical-1.jpg') }}" alt="Banner Image"
                        class="w-full h-full object-cover">
                    <span class="absolute inset-0 w-full h-full backdrop-blur-2xl backdrop-brightness-50"> </span>
                    <div class="absolute inset-0 w-full h-full ">
                        <div
                            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white px-4">
                            <h2 class="text-3xl md:text-5xl font-bold mb-4">Pilihan Layanan Kami</h2>
                            <p class="text-lg md:text-2xl mb-6"></p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

@push('scripts')
<script>
const swiper = new Swiper(".bannerSwiper", {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    speed: 400,
    // spaceBetween: 30,

    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },



    pagination: {
        el: ".swiper-pagination",

    },
});
</script>
@endpush
