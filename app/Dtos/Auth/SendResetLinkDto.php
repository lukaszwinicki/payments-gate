<?php

namespace App\Dtos\Auth;

readonly class SendResetLinkDto
{
    public function __construct(
        public string $email
    ) {
    }
}