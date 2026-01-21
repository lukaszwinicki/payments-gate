<?php

namespace App\Dtos\Dashboard;

readonly class MerchantTransactionsListDto
{
    /** @param MerchantTransactionDto[] $transactions */
    public function __construct(
        public array $transactions
    ) {
    }
}
