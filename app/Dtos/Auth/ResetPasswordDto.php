<?php

namespace App\Dtos\Auth;

readonly class ResetPasswordDto
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $passwordConfirmation
    ) {
    }
}