<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //

    public string $email = '';
    public string $password = '';

    public function login(): void
    {


        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
        ]);

        // Throttle untuk mencegah brute force
        $throttleKey = \Illuminate\Support\Str::lower($validated['email']) . '|' . request()->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);

            $this->dispatch(
            'open-alert',
            type: 'error',
            message: 'Terlalu banyak percobaan login.',
            description: "Silakan coba lagi dalam {$seconds} detik."
            );

            return;
        }

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

            // Jangan bocorkan apakah email terdaftar atau tidak
            $this->dispatch(
            'open-alert',
            type: 'error',
            message: 'Login gagal!',
            description: 'Email atau password tidak valid.'
            );

            $this->password = '';
            return;
        }

        session()->flash('alert', [
            'type' => 'success',
            'message' => 'Login berhasil!',
            'description' => 'Selamat datang kembali di Klinik Goaria.',
        ]);
        // Sukses login: reset throttle & cegah session fixation
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->password = '';


        sleep(1);

        // IMPORTANT: session() was regenerated; refresh/redirect to avoid Livewire 419 ("page expired")
        $redirectTo = request()->header('referer') ?: route('guest.home.welcome');
        $this->redirect($redirectTo);
        return;
    }


}; ?>

<div class=" rounded-2xl border border-gray-200 bg-white p-6 sticky top-24 overflow-hidden">
    <div class="text-2xl font-semibold text-gray-900">Masuk ke Akun Anda</div>
    <div class="mt-2 text-sm text-gray-600">Masukkan Email dan Password Anda</div>

    <div class="mt-5 space-y-4">
        @include('components.form.input', [
        'label' => 'Email',
        'type' => 'email',
        'placeholder' => 'Masukkan email',
        'wireModel' => 'email',
        ])

        @include('components.form.input', [
        'label' => 'Password',
        'type' => 'password',
        'placeholder' => 'Masukkan password',
        'wireModel' => 'password',
        ])


        @include('components.form.button', [
        'label' => 'Masuk',
        'wireClick' => 'login',
        ])

        <div class="text-xs text-gray-500">
            Belum punya akun?
            <button>Daftar akun</button>
        </div>
    </div>

</div>
