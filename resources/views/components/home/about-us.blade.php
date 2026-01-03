<section class=" bg-secondary w-full ">
    <div class="max-w-7xl m-auto py-24 px-10 space-y-10">
        <div class=" text-white " data-aos="fade-up">
            <h1 class="text-xl font-light ">Tentang Kami</h1>
            <p class="text-4xl font-semibold">+3 Tahun Menyediakan Layanan Kesehatan Terbaik</p>
        </div>
        <div class="flex gap-10" data-aos="fade-up">
            <div class="flex-2 text-white font-light">
                <p>Klinik dan Apotek Goa Ria merupakan fasilitas kesehatan di Sudiang, Makassar, yang telah melayani
                    masyarakat setidaknya sejak awal era 2010-an. Klinik ini menyediakan berbagai layanan medis terpadu
                    yang mencakup poli umum, poli gigi (termasuk pemasangan behel), layanan KIA/KB, imunisasi, serta
                    laboratorium kesehatan untuk cek gula darah dan kolesterol. Selain itu, pada 2025 klinik ini juga
                    mengunggulkan fasilitas Mom & Baby Care seperti pijat bayi, tindik, serta pemeriksaan USG rutin
                    untuk memantau kesehatan janin. Beroperasi setiap hari mulai pukul 08.00 hingga 21.00, klinik ini
                    melayani pasien umum maupun peserta BPJS Kesehatan sebagai Fasilitas Kesehatan Tingkat Pertama
                    (FKTP). </p>
            </div>
            <div class="flex-1">
                <img src="{{ asset('images/medical-1.jpg') }}" alt="">
            </div>

        </div>

    </div>


</section>

@push('scripts')
    <script>
        const serviceSwiper = new Swiper(".serviceSwiper", {
            slidesPerView: 5,
            // centeredSlides: true,
            loop: true,
            speed: 700,
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
