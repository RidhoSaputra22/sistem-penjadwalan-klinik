<?php

use Livewire\Volt\Component;

new class extends Component
{
    //

    public function with()
    {
        return [
            'services' => \App\Models\Service::all(),
        ];
    }
}; ?>

<section class=" py-24 px-10 space-y-5 bg-primary text-white text-center" data-aos="fade-up">
    <h1 class="text-3xl font-semibold">Tunggu Apa Yuk Lagi Buat Reservasi Sekarang</h1>
    <p class="text-lg/loose font-light">Membuat janji temu dengan dokter kini lebih mudah dan cepat di sini.</p>
    <a href="{{ route('guest.service.search') }}"
        class=" bg-white text-primary px-6 py-2 rounded-md font-medium hover:bg-gray-100 transition">
        Buat Reservasi
    </a>

</section>