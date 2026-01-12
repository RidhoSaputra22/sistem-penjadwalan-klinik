<?php

use Livewire\Volt\Component;

new class extends Component {
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
                @foreach ($banner as $item)
                <img class="swiper-slide h-full w-full object-cover object-center bg-primary" src="{{ asset($item) }}" alt="" >

                @endforeach

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
