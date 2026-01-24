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

            $this->dispatch('booking-updated-nav');
            $this->dispatch('booking-success');
            $this->dispatch('notification-updated');

            $this->dispatch('open-alert', 'success', 'Pembayaran berhasil!', 'Pembayaran Anda telah berhasil diproses.');


        } else {
        $this->dispatch('open-alert', 'error', 'Terjadi kesalahan pada proses pembayaran.', 'Silakan coba lagi atau hubungi layanan pelanggan.');
        }
    }

    #[On('payment-error')]
    public function handlePaymentError($payload)
    {
        // dd($payload);
        $snapToken = $payload['snapToken'] ?? null;

        $booking = App\Models\Appointment::where('snap_token', '=', $snapToken, 'and')->first();
        if ($booking) {
            $booking->status = \App\Enums\AppointmentStatus::CANCELLED;
            if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'payment_status')) {
                $booking->payment_status = \App\Enums\PaymentStatusEnum::FAILED;
            }
            $booking->save();
        }

        $this->dispatch('open-alert', 'error', 'Pembayaran Gagal', 'Terjadi kesalahan pada proses pembayaran. Silakan coba lagi atau hubungi layanan pelanggan.');
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

    {{-- @livewire('layouts.footter') --}}

</div>
