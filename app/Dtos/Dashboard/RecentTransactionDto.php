<?php

namespace App\Dtos\Dashboard;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Transaction;

readonly class RecentTransactionDto
{
    public function __construct(
        public string $transactionUuid,
        public string $amount,
        public string $currency,
        public TransactionStatus $status,
        public PaymentMethod $paymentMethod,
        public string|null $createdAt,
    ) {
    }

    public static function fromModel(Transaction $t): self
    {
        return new self(
            transactionUuid: $t->transaction_uuid,
            amount: $t->amount,
            currency: $t->currency,
            status: $t->status,
            paymentMethod: $t->payment_method,
            createdAt: $t->created_at?->toIso8601String(),
        );
    }
}