<?php

namespace App\Services\Auth;

use App\Dtos\Auth\AuthTokenDto;
use App\Dtos\Auth\LoginUserDto;
use App\Dtos\Auth\RegisterUserDto;
use App\Dtos\Auth\ResetPasswordDto;
use App\Dtos\Auth\SendResetLinkDto;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ResetPasswordException;
use App\Models\Merchant;
use App\Models\User;
use App\Services\Auth\TokenService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(
        private TokenService $tokenService
    ) {
    }

    public function register(RegisterUserDto $registerUserDto): AuthTokenDto
    {
        return DB::transaction(function () use ($registerUserDto) {

            $merchant = Merchant::create([
                'email' => $registerUserDto->email,
                'api_key' => hash('sha256', Str::random(64)),
                'secret_key' => Str::uuid()
            ]);

            $user = User::create([
                'name' => $registerUserDto->name,
                'email' => $registerUserDto->email,
                'password' => Hash::make($registerUserDto->password),
                'merchant_id' => $merchant->id
            ]);

            $user->assignRole(Role::findByName('user'));

            return $this->tokenService->create($user);
        });
    }

    public function login(LoginUserDto $loginUserDto): AuthTokenDto
    {
        $user = User::where('email', $loginUserDto->email)->first();
        if (!$user || !Hash::check($loginUserDto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        $user->tokens()->delete();

        return $this->tokenService->create($user);
    }

    public function sendResetLink(SendResetLinkDto $dto)
    {
        $status = Password::sendResetLink(['email' => $dto->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return $status;
        }

        throw new ResetPasswordException('Failed to send reset link');
    }

    public function resetPassword(ResetPasswordDto $resetPasswordDto): void
    {
        $status = Password::reset(
            [
                'email' => $resetPasswordDto->email,
                'password' => $resetPasswordDto->password,
                'password_confirmation' => $resetPasswordDto->passwordConfirmation,
                'token' => $resetPasswordDto->token
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new ResetPasswordException('Invalid token or email.');
        }
    }
}