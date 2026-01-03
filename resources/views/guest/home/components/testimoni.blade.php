<?php

use Livewire\Volt\Component;

new class extends Component {
    //

    public function with(){

            $reviews = [
            [
                'name' => 'Budi Santoso',

                'review' => 'Pelayanannya cepat dan ramah. Dari pendaftaran sampai pemeriksaan dokter semuanya tertata. Saya merasa lebih tenang karena penjelasannya jelas dan mudah dipahami.'
            ],
            [
                'name' => 'Siti Aminah',

                'review' => 'Saya membawa anak untuk kontrol, perawatnya sabar dan telaten. Klinik Goaria juga bersih dan nyaman, jadi anak tidak takut saat diperiksa.'
            ],
            [
                'name' => 'Agus Wijaya',

                'review' => 'Waktu tunggu tidak lama dan dokter komunikatif. Saya juga dibantu pilihan pemeriksaan yang sesuai kebutuhan, jadi tidak merasa diarahkan yang tidak perlu.'
            ],
            [
                'name' => 'Dewi Lestari',

                'review' => 'Suka dengan suasana kliniknya yang rapi dan profesional. Administrasinya mudah, informasinya transparan, dan saya mendapatkan saran perawatan yang detail.'
            ],
            [
                'name' => 'Rina Marlina',

                'review' => 'Saya kontrol rutin di Klinik Goaria dan selalu puas. Staf responsif, jadwalnya jelas, serta fasilitasnya lengkap untuk pemeriksaan dasar. Recommended.'
            ]
            ];



        return [
            'reviews' => $reviews,
        ];
    }
};

?>


<section class="max-w-7xl m-auto py-24 px-10 space-y-10" wire:ignore>
    <div class=" w-4xl " data-aos="fade-up">
        <h1 class="text-xl font-light ">Testimoni</h1>
        <p class="text-4xl/loose font-semibold">Testimoni Pasien yang Puas dengan Layanan Kami</p>
        <p class="text-lg/relaxed font-light">
            Kami terus berusaha memberikan pelayanan terbaik kepada setiap pasien
        </p>
    </div>
    <div class="swiper gallerySwiper h-62 w-full" data-aos="fade-up">
        <div class="swiper-wrapper ">

            @foreach ($reviews as $i => $review)
            <div class="relative swiper-slide h-96 w-full px-5
                ">
                <div class="flex gap-5 ">
                    <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                        class="size-14 rounded-full object-cover object-center ">
                    <div class="">
                        <h1 class="text-lg font-semibold ">{{ $review['name'] }}</h1>
                        <p class="text-sm/normal font-light">Pasien Klinik Goaria</p>
                    </div>
                </div>
                <div>
                    <p class="mt-5 text-sm/loose font-light">
                        "{{ $review['review'] }}"
                    </p>
                </div>
            </div>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

@push('scripts')
<script>
const gallerySwiper = new Swiper(".gallerySwiper", {
    slidesPerView: 3,
    spaceBetween: 16,
    loop: true,
    speed: 400,
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },
});
</script>
@endpush
