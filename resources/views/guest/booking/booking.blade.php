<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    //

    public string $servicesSlug = '';


    public function mount($slug)
    {
        $this->servicesSlug = $slug;
    }


    #[On('payment-success')]
    public function handlePaymentSuccess($payload)
    {
        $service = new \App\Services\ReservationService();
        $result = $service->processPaymentResult(is_array($payload) ? $payload : []);
        $payload = $payload ?? [];

        if (($result['ok'] ?? false) === true && ($result['message'] ?? '') === 'Payment confirmed') {
            session()->flash('success', 'Pembayaran berhasil! Terima kasih telah melakukan reservasi.');
            $this->dispatch('booking-updated-nav');
            $this->dispatch('booking-success');

        } else {
            session()->flash('error', 'Status pembayaran belum dikonfirmasi. Silakan cek kembali.');
        }


    }


    public function with(){

        $service = \App\Models\Service::with('category')->where('slug', $this->servicesSlug)->firstOrFail();

        return [
            'service' => $service,

        ];
    }


}; ?>

<div>


    @livewire('layouts.navbar')

    <div class=" flex min-h-screen p-12 gap-10" wire:loading.class="opacity-50 pointer-events-none">

        <div class="flex-2 space-y-14">

            <div class="relative rounded-2xl overflow-hidden ">
                <img src="{{ Storage::url($service->photo ?? 'services/services-placeholder.jpg') }}"
                    class=" w-full h-screen object-cover ">
                <span class="absolute inset-0 h-full w-full bg-linear-to-tr from-black to-transparent"></span>
                <div class="absolute bottom-6 left-6 text-white space-y-5">
                    <div class="space-y-5">
                        <h1 class="text-5xl font-semibold">{{ $service->name }}</h1>
                        <h1 class="text-4xl font-semibold"> Rp. {{ number_format($service->price, 0, ',', ',') }}</h1>
                        <p class="text-lg">{{ $service->description }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @component('components.icons.clock')

                        @endcomponent
                        <p class="text-md font-light">{{ $service->duration_minutes }} menit</p>
                    </div>

                </div>
            </div>


        </div>
        <div class="flex-1 ">
            <div class="sticky top-20">
                @livewire('guest.booking.booking-form', ['service' => $service])
            </div>
        </div>


    </div>

    @livewire('layouts.footter')

</div>
