<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Lazy;

new class extends Component {
    //


    public function with(){

          $berita = [
            [
                'judul' => 'Klinik Buka Layanan Vaksinasi Influenza Harian',
                'subjudul' => 'Layanan vaksin influenza tersedia setiap hari untuk kelompok berisiko dan masyarakat umum.',
                'gambar' => 'medical-1.jpg'
            ],
            [
                'judul' => 'Skrining Hipertensi & Gula Darah Gratis Akhir Pekan Ini',
                'subjudul' => 'Pemeriksaan tekanan darah dan gula darah sewaktu untuk deteksi dini penyakit tidak menular.',
                'gambar' => 'medical-2.jpg'
            ],
            [
                'judul' => 'Telekonsultasi Dokter Kini Tersedia 24/7',
                'subjudul' => 'Pasien dapat konsultasi keluhan umum, kontrol rutin, dan tebus resep tanpa antre.',
                'gambar' => 'medical-3.jpg'
            ],
            [
                'judul' => 'Edukasi Ibu Hamil: Tanda Bahaya Kehamilan & Pemeriksaan Rutin',
                'subjudul' => 'Kelas edukasi membahas nutrisi, jadwal ANC, serta kapan harus segera ke fasilitas kesehatan.',
                'gambar' => 'medical-2.jpg'
            ],
        ];


        return [
            'berita' => $berita,
        ];
    }

}; ?>


<section class="max-w-7xl m-auto py-24 px-10 space-y-10" wire:ignore>
    <div class="w-4xl" data-aos="fade-up">
        <h1 class="text-xl font-light">Berita Terbaru</h1>
        <p class="text-4xl/tight font-semibold">Dapatkan informasi terkini seputar Klinik Goaria</p>
    </div>
    <div class="swiper beritaSwiper h-96 w-full" data-aos="fade-up">
        <div class="swiper-wrapper ">

            @foreach ($berita as $i => $beritaItem)
            <a href="#" class="relative swiper-slide h-96 w-full
                ">
                <img src="{{ asset('images/' . $beritaItem['gambar']) }}" alt=""
                    class=" object-cover object-center h-96 w-full rounded-xl ">

                <div class="absolute bottom-0 w-full bg-white bg-opacity-75 p-4 rounded-tr-md max-w-md h-32 space-y-1">
                    <h1 class="text-lg font-semibold ">{{ $beritaItem['judul'] }}</h1>
                    <p class="text-sm/normal font-light">{{ Str::limit($beritaItem['subjudul'], 100) }}</p>
                </div>

            </a>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

@push('scripts')
<script>
const beritaSwiper = new Swiper(".beritaSwiper", {
    slidesPerView: 3,
    spaceBetween: 24,
    loop: true,
    speed: 400,
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },
});
</script>
@endpush
