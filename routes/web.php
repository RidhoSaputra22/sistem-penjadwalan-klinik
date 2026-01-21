<?php

use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'guest.home.welcome')->name('guest.home.welcome');

Volt::route('/cari-dokter', 'guest.doctor.search')->name('guest.doctor.search');
Volt::route('/detail-dokter/{slug}', 'guest.doctor.detail')->name('guest.doctor.detail');

Volt::route('/cari-layanan', 'guest.service.search')->name('guest.service.search');
Volt::route('/booking/{slug}', 'guest.booking.booking')->name('guest.booking');

Volt::route('/user/dashboard', 'user.dashboard.dashboard')->name('user.dashboard');

Volt::route('/services', 'guest.services.index')->name('guest.services.index');
Volt::route('/about', 'guest.about.index')->name('guest.about.index');

Route::post('/logout', function () {
    Auth::logout();

    return redirect(route('guest.home.welcome'));
})->name('user.logout');

Route::get('/laporan/booking/AWT/pdf', [GuestController::class, 'generateAWTBookingReportPdf'])->name('reports.booking.awt.booking.pdf');
Route::get('/laporan/booking/TAT/pdf', [GuestController::class, 'generateTATBookingReportPdf'])->name('reports.booking.tat.booking.pdf');
Route::get('/laporan/booking/ALL/pdf', [GuestController::class, 'generateALLBookingReportPdf'])->name('reports.booking.all.booking.pdf');
