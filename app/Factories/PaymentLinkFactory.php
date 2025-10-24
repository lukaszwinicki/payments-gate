<?php

namespace App\Factories;

use App\Models\PaymentLink;

class PaymentLinkFactory
{
    public function make(): PaymentLink
    {
        return new PaymentLink();
    }
}