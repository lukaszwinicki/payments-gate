<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: "/api/transactions/recent",
        tags: ["Transactions"],
        summary: "Get recent transactions",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of recent transactions",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "transactionUuid", type: "string", example: "c061745d-e26f-4d8b-b923-4f2297f414a6"),
                            new OA\Property(property: "amount", type: "number", format: "float", example: 199.99),
                            new OA\Property(property: "currency", type: "string", example: "PLN"),
                            new OA\Property(property: "status", type: "string", example: "SUCCESS"),
                            new OA\Property(property: "paymentMethod", type: "string", example: "TPAY"),
                            new OA\Property(property: "createdAt", type: "string", format: "date-time", example: "2026-03-01 12:00:00")
                        ]
                    )
                )
            )
        ]
    )]
    public function getRecentTransaction(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getRecentTransactions($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/transactions/payment-methods/share",
        tags: ["Transactions"],
        summary: "Get payment method share statistics",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Share of each payment method",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "paymentMethod", type: "string", example: "TPAY"),
                            new OA\Property(property: "count", type: "integer", example: 100)
                        ]
                    )
                )
            )
        ]
    )]
    public function getPaymentMethodShare(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getPaymentMethodShare($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/transactions/total",
        tags: ["Transactions"],
        summary: "Get total number of transactions",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Total transaction count",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total", type: "integer", example: 1234)
                    ]
                )
            )
        ]
    )]
    public function getTransactionTotal(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTotalCount($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/transactions/balances",
        tags: ["Transactions"],
        summary: "Get total balances of transactions",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Balances of transactions",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "totalAmount", type: "number", format: "float", example: 9999.99),
                        new OA\Property(property: "currency", type: "string", example: "PLN")
                    ]
                )
            )
        ]
    )]
    public function getTransactionsBalances(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTransactionsBalances($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/transactions/rejected",
        tags: ["Transactions"],
        summary: "Get total number of failed transactions",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Total failed transactions",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "failedCount", type: "integer", example: 12)
                    ]
                )
            )
        ]
    )]
    public function getFailedCount(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getFailedCount($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/transactions",
        tags: ["Transactions"],
        summary: "Get all transactions for merchant",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of merchant transactions",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "transactionUuid", type: "string", example: "c061745d-e26f-4d8b-b923-4f2297f414a6"),
                            new OA\Property(property: "amount", type: "number", format: "float", example: 199.99),
                            new OA\Property(property: "currency", type: "string", example: "PLN"),
                            new OA\Property(property: "status", type: "string", example: "SUCCESS"),
                            new OA\Property(property: "paymentMethod", type: "string", example: "TPAY"),
                            new OA\Property(property: "createdAt", type: "string", format: "date-time", example: "2026-03-01 12:00:00")
                        ]
                    )
                )
            )
        ]
    )]
    public function getMerchantTransactions(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getMerchantTransactions($request->merchantId())
        );
    }

    #[OA\Get(
        path: "/api/notifications",
        tags: ["Transactions"],
        summary: "Get transaction notifications for merchant",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(type: "string", example: "api-key")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of transaction notifications",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "transactionUuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
                            new OA\Property(property: "transactionId", type: "integer", example: 123),
                            new OA\Property(property: "status", type: "string", example: "SUCCESS"),
                            new OA\Property(property: "statusType", type: "string", example: "SUCCESS_REFUND"),
                            new OA\Property(property: "createdAt", type: "string", format: "date-time", nullable: true, example: "2026-03-01 12:00:00")
                        ]
                    )
                )
            )
        ]
    )]
    public function getTransactionNotifications(TransactionService $service, MerchantRequest $request): JsonResponse
    {
        return response()->json(
            $service->getTransactionNotifications($request->merchantId())
        );
    }
}
