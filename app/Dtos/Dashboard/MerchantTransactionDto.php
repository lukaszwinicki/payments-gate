<?php

namespace App\Dtos\Dashboard;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Transaction;

readonly class MerchantTransactionDto
{
    public function __construct(
        public int $id,
        public string $transactionUuid,
        public string $transactionId,
        public int $merchantId,
        public string $amount,
        public string $fullname,
        public string $email,
        public string $currency,
        public TransactionStatus $status,
        public string|null $notificationUrl,
        public string|null $returnUrl,
        public PaymentMethod $paymentMethod,
        public string|null $refundCode,
        public string|null $createdAt,
        public string|null $updatedAt
    ) {
    }

    public static function fromModel(Transaction $t): self
    {
        return new self(
            id: $t->id,
            transactionUuid: $t->transaction_uuid,
            transactionId: $t->transaction_id,
            merchantId: $t->merchant_id,
            amount: $t->amount,
            fullname: $t->name,
            email: $t->email,
            currency: $t->currency,
            status: $t->status,
            notificationUrl: $t->notification_url,
            returnUrl: $t->return_url,
            paymentMethod: $t->payment_method,
            refundCode: $t->refund_code,
            createdAt: $t->created_at?->toIso8601String(),
            updatedAt: $t->updated_at?->toIso8601String()
        );
    }
}