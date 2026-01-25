<section class="max-w-7xl mx-auto py-10 px-5">
    <!-- Swiper -->
    <div class="swiper bannerSwiper h-96 rounded-2xl overflow-hidden">
        <div class="swiper-wrapper ">
            <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-1.png') }}" alt="Banner 1" />
            <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-2.png') }}" alt="Banner 1" />
            <img class=" swiper-slide object-cover " src="{{ asset('images/banner/banner-3.png') }}" alt="Banner 1" />






        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

@push('scripts')
<script>
const swiper = new Swiper(".bannerSwiper", {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    speed: 600,
    spaceBetween: 30,

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