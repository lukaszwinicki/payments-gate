<?php

namespace App\Factory\Dtos;

use App\Enums\TransactionStatus;

readonly class ConfirmTransactionDto
{
   public function __construct(
      public TransactionStatus $status,
      public string $responseBody = '', 
      public string $remoteCode = '', 
      public string $completed = ''
   ) {}
}