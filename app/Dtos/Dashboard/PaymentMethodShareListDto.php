<?php

namespace App\Dtos\Dashboard;

class PaymentMethodShareListDto
{
    /** @param PaymentMethodShareDto[] $shares */
    public function __construct(
        public array $shares
    ) {
    }
}
