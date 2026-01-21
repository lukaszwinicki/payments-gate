<?php

namespace Tests\Unit;

use App\Dtos\Auth\AuthTokenDto;
use App\Dtos\Auth\LoginUserDto;
use App\Dtos\Auth\RegisterUserDto;
use App\Dtos\Auth\SendResetLinkDto;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ResetPasswordException;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'user']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_registers_user_and_returns_token(): void
    {
        $tokenService = Mockery::mock(TokenService::class);
        $tokenService->shouldReceive('create')
            ->once()
            ->andReturn(
                new AuthTokenDto(
                    user: User::factory()->make(),
                    token: 'fake-token',
                    expiresAt: now()->addMinutes(20)
                )
            );

        $service = new AuthService($tokenService);

        $dto = new RegisterUserDto(
            'John',
            'john@example.com',
            'Password123!'
        );

        $result = $service->register($dto);

        $this->assertInstanceOf(AuthTokenDto::class, $result);
        $this->assertEquals('fake-token', $result->token);
        $this->assertNotNull($result->expiresAt);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('merchants', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_it_logs_in_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!')
        ]);

        $tokenService = Mockery::mock(TokenService::class);
        $tokenService->shouldReceive('create')
            ->once()
            ->andReturn(
                new AuthTokenDto(
                    user: $user,
                    token: 'fake-token',
                    expiresAt: now()->addMinutes(20)
                )
            );

        $service = new AuthService($tokenService);

        $dto = new LoginUserDto($user->email, 'Password123!');

        $result = $service->login($dto);

        $this->assertInstanceOf(AuthTokenDto::class, $result);
        $this->assertEquals('fake-token', $result->token);
        $this->assertNotNull($result->expiresAt);
    }

    public function test_it_throws_exception_on_invalid_login(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $service = new AuthService(
            Mockery::mock(TokenService::class)
        );

        $dto = new LoginUserDto('wrong@email.com', 'wrong-password');

        $service->login($dto);
    }

    public function test_it_sends_reset_link(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $service = new AuthService(
            Mockery::mock(TokenService::class)
        );

        $dto = new SendResetLinkDto('john@example.com');

        $result = $service->sendResetLink($dto);

        $this->assertEquals(Password::RESET_LINK_SENT, $result);
    }

    public function test_it_throws_exception_when_reset_link_fails(): void
    {
        $this->expectException(ResetPasswordException::class);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::INVALID_USER);

        $service = new AuthService(
            Mockery::mock(TokenService::class)
        );

        $service->sendResetLink(
            new SendResetLinkDto('john@example.com')
        );
    }
}
