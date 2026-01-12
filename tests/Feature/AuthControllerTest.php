<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => 'user',
            'guard_name' => 'web',
        ]);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'passwordConfirmation' => 'Password123!',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'expiresAt',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'passwordConfirmation' => 'Password123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'expiresAt',
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.'
            ]);
    }

    public function test_reset_link_can_be_sent(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Reset link sent to your email.'
            ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'john@example.com',
            'token' => $token,
            'password' => 'NewPassword123!',
            'passwordConfirmation' => 'NewPassword123!',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Password successfully reset.'
            ]);

        $this->assertNotNull($user->fresh());

        $this->assertTrue(
            Hash::check('NewPassword123!', $user->fresh()->password)
        );
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'john@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'passwordConfirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400);
    }
}
