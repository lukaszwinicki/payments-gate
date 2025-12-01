<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/', function () {
    return auth()->check()
        ? (auth()->user()->hasRole('admin') ? redirect('/admin') : redirect('/user'))
        : redirect()->route('login');
});


require __DIR__ . '/auth.php';
