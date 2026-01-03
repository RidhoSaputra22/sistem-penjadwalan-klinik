<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Volt::route('/', 'guest.home.welcome')->name('guest.home.welcome');


Volt::route('/cari-dokter', 'guest.doctor.search')->name('guest.doctor.search');
Volt::route('/detail-dokter/{slug}', 'guest.doctor.detail')->name('guest.doctor.detail');


Volt::route('/services', 'guest.services.index')->name('guest.services.index');
Volt::route('/about', 'guest.about.index')->name('guest.about.index');

Route::get('/logout', function () {
    Auth::logout();
    // return redirect()->route('user.auth.login');
})->name('user.auth.logout');
