<?php

use App\Models\User;

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;

new class extends Component {
    //
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $confirmPassword = '';

    public function goToLogin(){
        $this->dispatch('changeAuthModalTab', tab: 'login');
    }

    public function mount()
    {
        $this->name = 'Test User';
        $this->email = 'testuser@gmail.com';
        $this->phone = '+6281234567890';
        $this->password = 'password123';
        $this->confirmPassword = 'password123';
    }

    public function regist(): void
    {
        // Rate limit (anti spam/bruteforce sederhana)
        $key = 'regist:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            return;
        }
        RateLimiter::hit($key, 60);

        // Normalisasi input
        $this->name  = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        // Simpan format phone "bersih" (angka + optional leading +)
        $normalizedPhone = preg_replace('/[^\d+]/', '', trim($this->phone ?? ''));
        // Jika user menulis +62..., biarkan; kalau 08..., biarkan juga (sesuaikan kebutuhan Anda)
        $this->phone = $normalizedPhone ?? '';

        // Validasi aman
        $validated = $this->validate(
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'string', 'email:rfc,dns', 'max:190', 'unique:users,email'],
                'phone' => ['required', 'string', 'max:25', 'regex:/^\+?\d{9,20}$/'],
                'password' => ['required', 'string', Password::defaults()],
                'confirmPassword' => ['required', 'same:password'],
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'phone.required' => 'No. handphone wajib diisi.',
                'phone.regex' => 'Format no. handphone tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'confirmPassword.same' => 'Konfirmasi password tidak sama.',
            ]
        );

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'], // pastikan kolom "phone" ada di tabel users
                'password' => Hash::make($validated['password']),
            ]);

            event(new Registered($user));
        });



        session()->flash('alert', [
            'type' => 'success',
            'message' => 'Login berhasil!',
            'description' => 'Selamat datang kembali di Klinik Goaria.',
        ]);

        Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']]);
        session()->regenerate();

        // Bersihkan field sensitif
        $this->password = '';
        $this->confirmPassword = '';

        // Reset rate-limit bila sukses
        RateLimiter::clear($key);

        // IMPORTANT: session() was regenerated; refresh/redirect to avoid Livewire 419 ("page expired")
        $redirectTo = request()->header('referer') ?: route('guest.home.welcome');
        $this->redirect($redirectTo);
        return;



    }






}; ?>


<div class=" rounded-2xl border border-gray-200 bg-white p-6 sticky top-24 overflow-hidden"
    wire:loading.class="opacity-50 pointer-events-none" wire:target="goToLogin">
    <div class="text-2xl font-semibold text-gray-900">Buat Akun Anda</div>
    <div class="mt-2 text-sm text-gray-600">Masukkan informasi berikut untuk membuat akun anda</div>

    <div class="mt-5 space-y-4">
        @include('components.form.input', [
        'label' => 'Nama Lengkap',
        'type' => 'text',
        'placeholder' => 'Masukkan nama lengkap',
        'wireModel' => 'name',
        ])

        @include('components.form.input', [
        'label' => 'Email',
        'type' => 'email',
        'placeholder' => 'Masukkan email',
        'wireModel' => 'email',
        ])

        @include('components.form.input', [
        'label' => 'No. Handphone (WA Aktif)',
        'type' => 'text',
        'placeholder' => 'Masukkan no. handphone',
        'wireModel' => 'phone',
        ])

        @include('components.form.input', [
        'label' => 'Password',
        'type' => 'password',
        'placeholder' => 'Masukkan password',
        'wireModel' => 'password',
        ])

        @include('components.form.input', [
        'label' => 'Konfirmasi Password',
        'type' => 'password',
        'placeholder' => 'Masukkan konfirmasi password',
        'wireModel' => 'confirmPassword',
        ])

        @include('components.form.button', [
        'label' => 'Daftar',
        'wireClick' => 'regist',
        ])

        <div class="text-xs text-gray-500">
            Sudah punya akun?
            <button wire:click="goToLogin">Masuk</button>
        </div>
    </div>

</div>
