<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? (auth()->user()->hasRole('admin') ? redirect('/admin') : redirect('/user'))
        : redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::get('/payment-status', function () {
    return view('payment-status');
});
