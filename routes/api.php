<?php

use App\Http\Controllers\PaymentLinkController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-key')->group(function () {
    Route::post('/create-transaction', [TransactionController::class, 'createTransaction']);
    Route::post('/refund-payment', [TransactionController::class, 'refundPayment']);
    Route::post('/create-payment-link', [PaymentLinkController::class, 'createPaymentLink']);
});

Route::post('/create-payment-link-transaction', [PaymentLinkController::class, 'createPaymentLinkTransaction']);
Route::post('/confirm-transaction', [TransactionController::class, 'confirmTransaction']);

Route::get('/transactions/{uuid}/status', [TransactionController::class, 'getStatus']);
Route::get('/payment/{payment_link_id}', [PaymentLinkController::class, 'paymentSummary']);
