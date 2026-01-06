<?php

namespace App\Dtos\Auth;

use App\Models\User;

readonly class AuthTokenDto
{
    public function __construct(
        public User $user,
        public string $token,
        public ?string $expiresAt,
    ) {
    }
}