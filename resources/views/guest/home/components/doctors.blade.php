<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function with(){
        return [
            'doctors' => \App\Models\Doctor::take(5)->get(),
        ];
    }
};

?>

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
    <div class="swiper doctorSwiper h-96 flex-2 " data-aos="fade-up">
        <div class="swiper-wrapper ">
            @foreach ($doctors as $doctor)
            <a href="#"
                class="swiper-slide group rounded-xl border border-gray-200 bg-white hover:shadow-md transition overflow-hidden">
                <div class="relative p-4">
                    <div class="absolute top-4 left-4">
                        <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-7 h-7 rounded-sm">
                    </div>
                    <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                        class="h-52 w-full  object-cover  rounded-lg bg-gray-50">
                </div>
                <div class="px-4 pb-4 space-y-2">
                    <h3 class="font-semibold text-gray-800 leading-snug truncate">
                        {{ $doctor->user->name }}
                    </h3>

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        @include('components.icons.kardiogram')
                        <div class="min-w-0">
                            <div class="truncate">
                                Sp. {{ $doctor->specialization }}
                            </div>

                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" class="size-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7.5a1.5 1.5 0 0 1 1.5-1.5h11A1.5 1.5 0 0 1 19 7.5V21M9 10.5h.01M9 13.5h.01M9 16.5h.01M12 10.5h.01M12 13.5h.01M12 16.5h.01M15 10.5h.01M15 13.5h.01M15 16.5h.01" />
                        </svg>
                        <span class="truncate">Klinik Goaria</span>
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
    slidesPerView: 2.5,
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
