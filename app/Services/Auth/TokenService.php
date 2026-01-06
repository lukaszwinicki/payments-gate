<?php

namespace App\Services\Auth;

use App\Dtos\Auth\AuthTokenDto;
use App\Models\User;

class TokenService
{
    public function create(User $user): AuthTokenDTO
    {
        $token = $user->createToken(
            config('token.name'),
            ['*'],
            now()->addMinutes(config('token.expires_in_minutes'))
        );

        return new AuthTokenDTO(
            $user,
            $token->plainTextToken,
            $token->accessToken->expires_at?->format('Y-m-d H:i:s')
        );
    }
}
