<?php

namespace App\Dtos;

use App\Enums\TransactionStatus;

readonly class ConfirmTransactionDto
{
   public function __construct(
      public TransactionStatus $status,
      public string $responseBody = '',
      public string $remoteCode = '',
      public bool $completed = false
   ) {
   }
}