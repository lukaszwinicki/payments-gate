<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function getRecentTransaction(TransactionService $service): JsonResponse
    {
        $transactions = $service->getRecentTransactions(request()->merchant_id);
        return response()->json($transactions);
    }

    public function getPaymentMethodShare(TransactionService $service): JsonResponse
    {
        $paymentMethodShare = $service->getPaymentMethodShare(request()->merchant_id);
        return response()->json($paymentMethodShare);
    }

    public function getTransactionTotal(TransactionService $service): JsonResponse
    {
        $transationTotal = $service->getTotalCount(request()->merchant_id);
        return response()->json($transationTotal);
    }

    public function getTransactionsBalances(TransactionService $service): JsonResponse
    {
        $transactionBalances = $service->getTransactionsBalances(request()->merchant_id);
        return response()->json($transactionBalances);
    }

    public function getFailedCount(TransactionService $service): JsonResponse
    {
        $transactionRejected = $service->getFailedCount(request()->merchant_id);
        return response()->json($transactionRejected);
    }

    public function getMerchantTransactions(TransactionService $service): JsonResponse
    {
        $transactions = $service->getMerchantTransactions(request()->merchant_id);
        return response()->json($transactions);
    }

    public function getTransactionNotifications(TransactionService $service): JsonResponse
    {
        $notifications = $service->getTransactionNotifications(request()->merchant_id);
        return response()->json($notifications);
    }
}
