<?php

namespace Tests\Unit;

use App\Dtos\Auth\AuthTokenDto;
use App\Models\User;
use App\Services\Auth\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_it_creates_auth_token_dto(): void
    {
        config()->set('token.name', 'test-token');
        config()->set('token.expires_in_minutes', 20);

        $user = User::factory()->create();
        $service = new TokenService();
        $result = $service->create($user);

        $this->assertInstanceOf(AuthTokenDto::class, $result);

        $this->assertEquals($user->id, $result->user->id);
        $this->assertNotEmpty($result->token);
        $this->assertNotNull($result->expiresAt);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }
}