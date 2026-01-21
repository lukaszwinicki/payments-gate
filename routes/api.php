<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentLinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/confirm-payment-link', [PaymentLinkController::class, 'confirmPaymentLink']);
Route::post('/confirm-transaction', [TransactionController::class, 'confirmTransaction']);

Route::get('/transaction/{uuid}/status', [TransactionController::class, 'getStatus']);
Route::get('/payment/{payment_link_id}', [PaymentLinkController::class, 'paymentDetails']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware(['auth.or.apikey','token.expiration', 'merchant'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    });
    
    Route::post('/create-transaction', [TransactionController::class, 'createTransaction']);
    Route::post('/create-payment-link', [PaymentLinkController::class, 'createPaymentLink']);
    Route::post('/refund-payment', [TransactionController::class, 'refundPayment']);

    Route::get('/transactions', [DashboardController::class, 'getMerchantTransactions']);
    Route::get('/transactions/recent', [DashboardController::class, 'getRecentTransaction']);
    Route::get('/transactions/payment-methods/share', [DashboardController::class, 'getPaymentMethodShare']);
    Route::get('/transactions/total', [DashboardController::class, 'getTransactionTotal']);
    Route::get('/transactions/balances', [DashboardController::class, 'getTransactionsBalances']);
    Route::get('/transactions/rejected', [DashboardController::class, 'getFailedCount']);
    Route::get('/notifications', [DashboardController::class, 'getTransactionNotifications']);
    Route::get('/api-credentials', [ProfileController::class, 'getApiCredentials']);
});
