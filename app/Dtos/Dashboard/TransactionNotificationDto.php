<?php

namespace App\Dtos\Dashboard;

use App\Enums\TransactionStatus;
use App\Models\Notification;

readonly class TransactionNotificationDto
{
    public function __construct(
        public int $id,
        public string $transactionUuid,
        public int $transactionId,
        public TransactionStatus $status,
        public TransactionStatus $statusType,
        public string|null $createdAt,
    ) {
    }

    public static function fromModel(Notification $n): self
    {
        return new self(
            id: $n->id,
            transactionUuid: $n->transaction->transaction_uuid,
            transactionId: $n->transaction_id,
            status: $n->status,
            statusType: $n->type_status,
            createdAt: $n->created_at?->toIso8601String(),
        );
    }
}