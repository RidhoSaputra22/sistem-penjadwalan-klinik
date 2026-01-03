<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //

    public string $email = '';
    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if(Auth::attempt(['email' => $this->email, 'password' => $this->password])){


        }



        // Login logic here
    }


}; ?>

<div class="rounded-2xl border border-gray-200 bg-white p-6 sticky top-24">
    <div class="text-2xl font-semibold text-gray-900">Masuk ke Akun Anda</div>
    <div class="mt-2 text-sm text-gray-600">Masukkan Email dan Password Anda</div>

    <div class="mt-5 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" placeholder="Masukkan email"
                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 focus:outline-none focus:shadow-md">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" placeholder="Masukkan password"
                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 focus:outline-none focus:shadow-md">
        </div>
        <button type="button"
            class="w-full inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-white hover:shadow-md transition">
            Masuk
        </button>
        <div class="text-xs text-gray-500">
            Belum punya akun?
            <button>Daftar akun</button>
        </div>
    </div>
</div>
