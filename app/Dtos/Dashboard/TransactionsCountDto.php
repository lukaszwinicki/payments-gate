<?php

namespace App\Dtos\Dashboard;

readonly class TransactionsCountDto
{
    public function __construct(
        public int $total,
    ) {
    }
}