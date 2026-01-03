<section class="max-w-7xl mx-auto py-10 px-5">
    <!-- Swiper -->
    <div class="swiper bannerSwiper h-96">
        <div class="swiper-wrapper *:bg-primary">
            <div class="swiper-slide">Slide 1</div>
            <div class="swiper-slide">Slide 2</div>
            <div class="swiper-slide">Slide 3</div>
            <div class="swiper-slide">Slide 4</div>
            <div class="swiper-slide">Slide 5</div>
            <div class="swiper-slide">Slide 6</div>
            <div class="swiper-slide">Slide 7</div>
            <div class="swiper-slide">Slide 8</div>
            <div class="swiper-slide">Slide 9</div>
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
            speed: 400,
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
