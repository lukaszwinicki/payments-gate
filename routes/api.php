<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-key')->group(function () {
    Route::post('/create-transaction', [TransactionController::class, 'createTransaction']);
    Route::post('/refund-payment', [TransactionController::class, 'refundPayment']);
});

Route::post('/confirm-transaction', [TransactionController::class, 'confirmTransaction']);
Route::get('/transactions/{uuid}/status', [TransactionController::class, 'getStatus']);