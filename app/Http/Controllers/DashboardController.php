<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function getRecentTransaction(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getRecentTransactions($request->merchantId())
        );
    }

    public function getPaymentMethodShare(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getPaymentMethodShare($request->merchantId())
        );
    }

    public function getTransactionTotal(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTotalCount($request->merchantId())
        );
    }

    public function getTransactionsBalances(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTransactionsBalances($request->merchantId())
        );
    }

    public function getFailedCount(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getFailedCount($request->merchantId())
        );
    }

    public function getMerchantTransactions(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getMerchantTransactions($request->merchantId())
        );
    }

    public function getTransactionNotifications(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTransactionNotifications($request->merchantId())
        );
    }
}
