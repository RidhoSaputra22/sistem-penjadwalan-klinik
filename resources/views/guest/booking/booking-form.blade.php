<?php

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    //
    public Service $service;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public ?string $booking_date = null;

    public ?string $booking_time = null;

    public ?string $selected_dp = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $this->name ?: ($user->name ?? '');
            $this->email = $this->email ?: ($user->email ?? '');

            $this->phone = $this->phone ?: ($user->phone ?? '');
        }
    }

    #[On('date-time-selected')]
    public function setDateTime($data)
    {
        $this->booking_date = $data['date'];
        $this->booking_time = $data['time'];
    }

    public function clearData(): void
    {
        $this->reset([
            'booking_date',
            'booking_time',
        ]);

        $this->dispatch('reset-booking-calendar');
    }

    #[On('booking-success')]
    public function handleBookingSuccess()
    {
        session()->flash('success', 'Reservasi berhasil dibuat!');
    }

    public function selectDp(string $dp)
    {
        if (! in_array($dp, ['0.25', '0.5'])) {
            return;
        }

        if ($this->selected_dp === $dp) {
            $this->selected_dp = null;

            return;
        }

        $this->selected_dp = $dp;
    }

    public function submitForm(): void
    {
        if (! Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk melakukan reservasi.');

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'required|date_format:Y-m-d',
            'booking_time' => 'required|date_format:H:i',
            'selected_dp' => 'required|in:0.25,0.5',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'booking_date.required' => 'Tanggal reservasi wajib diisi.',
            'booking_time.required' => 'Waktu reservasi wajib diisi.',
            'selected_dp.required' => 'Silakan pilih DP (25% atau 50%).',
        ]);

        try {
            $tz = 'Asia/Makassar';
            $scheduledDate = Carbon::parse("{$this->booking_date} {$this->booking_time}", $tz);

            $reservationService = new \App\Services\ReservationService;
            $result = $reservationService->createReservation([
                'name' => $this->name,
                'phone' => $this->phone,
                'service_id' => $this->service->id,
                'scheduled_date' => $scheduledDate,
                'dp_ratio' => $this->selected_dp,

            ]);

            if (is_array($result) && isset($result['snap_token'])) {
                $this->dispatch('open-midtrans-snap', snapToken: $result['snap_token']);
            }

            $this->clearData();
            $this->dispatch('booking-created');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('form', 'Terjadi kesalahan saat menyimpan reservasi. Silakan coba lagi.'.$e->getMessage());
        }

    }
}; ?>

<div>
    <div wire:loading.class="opacity-50 cursor-not-allowed" class="space-y-5">
        @if (session()->has('success'))
        <div class="p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
            {{ session('success') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->has('form'))
        <div class="p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
            {{ $errors->first('form') }}
        </div>
        @endif

        <div class="space-y-2">
            <h1 class="text-4xl font-bold">Booking Sekarang</h1>
            <p class="text-sm font-light">Silakan isi formulir di bawah untuk melakukan pemesanan.</p>
        </div>

        @guest
        <div class="p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
            Silakan login terlebih dahulu untuk mengisi formulir reservasi.
            <button wire:click="$dispatch('open-auth-modal', { tab: 'login' })" class="text-primary font-medium ml-1">
                Login
            </button>
        </div>
        @endguest

        @auth
        <div class="space-y-4">
            @component('components.form.input', [
            'wireModel' => 'name',
            'label' => 'Nama Lengkap',
            'type' => 'text',
            'required' => true,
            'disabled' => true,

            ])

            @endcomponent
            @component('components.form.input', [
            'wireModel' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'disabled' => true,

            ])

            @endcomponent
            @component('components.form.input', [
            'wireModel' => 'phone',
            'label' => 'Nomor Telepon',
            'type' => 'text',
            'required' => true,
            'disabled' => true,

            ])

            @endcomponent
        </div>
        <div>
            <h1 class="font-light pb-2">Pilih Tanggal & Waktu Reservasi</h1>
            @livewire('guest.booking.components.booking-callendar', ['service' => $service])
            @if ($errors->has('booking_date') || $errors->has('booking_time'))
            <p class="text-sm font-light text-red-500">
                Silakan pilih tanggal dan waktu reservasi.
            </p>
            @endif
        </div>

        <div x-data="{ selectedDp: @entangle('selected_dp') }">
            <h1 class="font-light pb-2">Uang Muka (DP)</h1>
            <div class=" grid grid-cols-2 gap-5">
                @php
                $dpOptions = [
                '0.25' => '25%',
                '0.5' => '50%',
                ];
                @endphp
                @foreach ($dpOptions as $value => $label)
                <div @click="$wire.selectDp('{{ $value }}')"
                    class="flex items-center  p-3 cursor-pointer border border-gray-300 border-dashed rounded-md text-center hover:border-primary "
                    :class="selectedDp === '{{ $value }}' ? 'border-primary bg-primary/10 font-medium' : ''">
                    {{ $label }}
                </div>
                @endforeach
            </div>
        </div>


        <div>
            @component('components.form.button', [
            'label' => 'Submit',
            'wireClick' => 'submitForm',
            'wireLoadingClass' => 'opacity-50',
            'class' => 'w-full py-3 bg-primary text-white rounded-md hover:bg-primary-dark',
            ])

            @endcomponent
        </div>
        @endauth
    </div>

</div>

@push('scripts')
@once
@php
$isProduction = (bool) config('services.midtrans.is_production', false);
$clientKey = (string) config('services.midtrans.client_key', '');
$snapBaseUrl = $isProduction
? 'https://app.midtrans.com/snap/snap.js'
: 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

@if ($clientKey !== '')
<script src=" {{ $snapBaseUrl }}" data-client-key="{{ $clientKey }}">
</script>
@endif

<script>
(function() {
    function waitForSnap(maxMs) {
        return new Promise(function(resolve, reject) {
            var start = Date.now();
            (function tick() {
                if (window.snap && typeof window.snap.pay === 'function') {
                    return resolve();
                }
                if (Date.now() - start > maxMs) {
                    return reject(new Error('Midtrans Snap is not available'));
                }
                setTimeout(tick, 100);
            })();
        });
    }

    document.addEventListener('open-midtrans-snap', function(event) {
        var detail = event && event.detail ? event.detail : {};
        var snapToken = detail.snapToken;
        if (!snapToken) return;

        waitForSnap(5000)
            .then(function() {
                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        Livewire.dispatch('payment-success', {
                            payload: result || {}
                        });
                    },
                    onPending: function() {
                        window.location.reload();
                    },
                    onError: function() {
                        Livewire.dispatch('payment-error', {
                            payload: {
                                snapToken: snapToken
                            }
                        });
                    },
                    onClose: function() {
                        Livewire.dispatch('payment-error', {
                            payload: {
                                snapToken: snapToken
                            }
                        });

                    }
                });
            })
            .catch(function(err) {
                console.error(err);
                alert(
                    'Gagal memuat pembayaran. Silakan refresh halaman dan coba lagi.'
                );
            });
    });
})();
</script>
@endonce
@endpush
