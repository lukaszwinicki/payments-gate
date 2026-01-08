<?php 

namespace App\Dtos\Dashboard;

readonly class FailedTransactionsCountDto 
{
    public function __construct(
        public int $total,
    ) {
    }
}