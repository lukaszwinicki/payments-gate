<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/create-transaction', [TransactionController::class, 'createTransaction']);
Route::post('/confirm-transaction', [TransactionController::class, 'confirmTransaction']);
Route::post('/refund-payment', [TransactionController::class, 'refundPayment']);