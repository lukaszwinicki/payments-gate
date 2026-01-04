<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class ApiKeyGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(
        protected UserProvider $provider,
        protected Request $request
    ) {
    }

    public function user(): Authenticatable|null
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $apiKey = $this->request->header('X-API-KEY');

        if (!$apiKey) {
            return null;
        }

        $this->user = $this->provider->retrieveByCredentials([
            'api_key' => $apiKey,
        ]);

        return $this->user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function id(): mixed
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function setUser($user): static
    {
        $this->user = $user;
        return $this;
    }
}
