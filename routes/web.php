<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-session', function () {
    return [
        'cookie' => request()->cookie(config('session.cookie')),
        'session_data' => session()->all(),
        'auth' => auth()->check(),
    ];
});

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';