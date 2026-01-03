<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $services = App\Models\Service::all();
    return view('welcome', compact('services'));
});
