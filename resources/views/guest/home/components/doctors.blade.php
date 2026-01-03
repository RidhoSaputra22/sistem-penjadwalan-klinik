<section class="max-w-7xl  flex mx-auto py-24 px-10 gap-14">
    <div class="flex-1 space-y-5">
        <h1 class="text-2xl/tight font-medium" data-aos="fade-up">Dokter - Dokter Kami</h1>
        <p class="text-sm font-light" data-aos="fade-up">Memberikan layanan medis dengan standar keamanan dan kesehatan
            terbaik.</p>
        <div class="relative" data-aos="fade-up">
            <span class="absolute inset-y-0 left-3 flex items-center text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>

            </span>
            <input type="text" name="" id=""
                class="w-full pl-10 px-3 py-2 rounded-lg border border-primary bg-white placeholder:text-primary focus:shadow-md focus:outline-none"
                placeholder="Cari Dokter">

        </div>

    </div>
    <div class="swiper doctorSwiper h-96 flex-2  " data-aos="fade-up">
        <div class="swiper-wrapper ">
            @foreach ([1, 2, 3] as $doctor)
            <a href="#" class="swiper-slide">
                <div class="relative">
                    <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                        class="rounded-xl w-full h-60 object-cover">
                    <div class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                        Dokter Umum
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                        Dr Andi Salikin S.Kes
                    </h1>
                    <div class="flex gap-2 text-primary items-center text-lg font-light ">
                        @include('components.icons.kardiogram', [
                        'size' => 'size-6',
                        ])
                        <h1 class="">
                            Sp. Dental Care
                        </h1>


                    </div>
                </div>
            </a>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>



</section>

@push('scripts')
<script>
const doctorSwiper = new Swiper(".doctorSwiper", {
    slidesPerView: 2,
    // centeredSlides: true,
    loop: true,
    speed: 400,
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
