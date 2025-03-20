<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/createTransaction', [TransactionController::class, 'createTransaction']);
Route::post('/confirmTransaction', [TransactionController::class, 'confirmTransaction']);