<?php

namespace App\Dtos\Dashboard;

readonly class RecentTransactionsListDto
{
    /** @param RecentTransactionDto[] $transactions */
    public function __construct(
        public array $transactions
    ) {
    }
}
