<section class="max-w-7xl h-screen mx-auto py-24 px-10 space-y-14">
    <div class="flex-1" data-aos="fade-up">
        <h1 class="text-xl font-light ">Ayo Lakukan Reservasi Sekarang</h1>
        <p class="text-4xl font-semibold">Booking Pertemuan dengan dokter lebih mudah disini.</p>
    </div>
    <div class="flex gap-10" data-aos="fade-up">
        <div class="flex-2 grid grid-cols-5 gap-5">
            @component('components.form.input', [
                'label' => 'Nama',
                'name' => 'name',
                'class' => 'col-span-2',
            ])
            @endcomponent
            @component('components.form.input', [
                'label' => 'Alamat',
                'name' => 'alamat',
                'class' => 'col-span-3',
            ])
            @endcomponent
            @component('components.form.input', [
                'label' => 'No Hp (Whatsapp)',
                'name' => 'hp',
                'class' => 'col-span-3',
            ])
            @endcomponent
            @component('components.form.input', [
                'label' => 'Jam',
                'name' => 'jam',
                'type' => 'time',
                'class' => 'col-span-2',
            ])
            @endcomponent
            @component('components.form.input', [
                'label' => 'Tanggal',
                'name' => 'tanggal',
                'type' => 'date',
                'class' => 'col-span-2',
            ])
            @endcomponent
            @component('components.form.select', [
                'label' => 'Layanan',
                'name' => 'layanan',
                'options' => $services->map(
                    fn($service) => [
                        'value' => $service->id,
                        'label' => $service->name,
                    ]),
                'class' => 'col-span-3',
            ])
            @endcomponent
            @component('components.form.textarea', [
                'label' => 'Catatan',
                'name' => 'catatan',
                'class' => 'col-span-5',
            ])
            @endcomponent
            @component('components.form.button', [
                'label' => 'Kirim',
                'class' => 'bg-primary text-white font-light ',
            ])
            @endcomponent

        </div>
        <div class="relative flex-1 p-5">
            <span class="absolute -top-5 -right-5 bg-primary w-30 aspect-square -z-10">
            </span>
            <img src="{{ asset('images/medical-consultant.jpg') }}" alt=""
                class="h-full object-cover object-center">
        </div>

    </div>



</section>
