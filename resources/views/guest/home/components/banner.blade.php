<section class="max-w-7xl mx-auto py-10 px-5">
    <!-- Swiper -->
    <div class="swiper bannerSwiper h-96 rounded-2xl overflow-hidden">
        <div class="swiper-wrapper ">
            <div class="relative swiper-slide rounded-2xl overflow-hidden">
                <img src="{{ asset('images/medical-1.jpg') }}" alt="Banner Image" class="w-full h-full object-cover">
                <span class="absolute inset-0 w-full h-full backdrop-blur-2xl backdrop-brightness-50"> </span>
                <div class="absolute inset-0 w-full h-full ">
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white px-4">
                        <h2 class="text-3xl md:text-5xl font-bold mb-4">Selamat Datang di Klinik Goaria</h2>
                        <p class="text-lg md:text-2xl mb-6">Kesehatan anda adalah prioritas kami</p>
                    </div>
                </div>
            </div>

            <div class="relative swiper-slide rounded-2xl overflow-hidden">
                <img src="{{ asset('images/medical-1.jpg') }}" alt="Banner Image" class="w-full h-full object-cover">
                <span class="absolute inset-0 w-full h-full backdrop-blur-2xl backdrop-brightness-50"> </span>
                <div class="absolute inset-0 w-full h-full ">
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white px-4">
                        <h2 class="text-3xl md:text-5xl font-bold mb-4">Selamat Datang di Klinik Goaria</h2>
                        <p class="text-lg md:text-2xl mb-6">Kesehatan anda adalah prioritas kami</p>
                    </div>
                </div>
            </div>



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
