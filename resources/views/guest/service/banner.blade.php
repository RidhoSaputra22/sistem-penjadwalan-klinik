<?php

use Livewire\Volt\Component;

new class extends Component
{
    //

}; ?>

<section class="">
    <!-- Swiper -->
    <div class="lg:p-12">
        <div class="swiper bannerSwiper lg:h-100  lg:rounded-2xl">
            <div class="w-full h-full swiper-wrapper">
                <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-1.png') }}"
                    alt="Banner 1" />
                <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-2.png') }}"
                    alt="Banner 1" />
                <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-3.png') }}"
                    alt="Banner 1" />
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