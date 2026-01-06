<?php

namespace App\Http\Controllers;

use App\Dtos\Auth\LoginUserDto;
use App\Dtos\Auth\RegisterUserDto;
use App\Dtos\Auth\ResetPasswordDto;
use App\Dtos\Auth\SendResetLinkDto;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ResetPasswordException;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'password_confirmation' => $request->input('passwordConfirmation')
        ]);

        $registerData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $registerDto = new RegisterUserDto(
            $registerData['name'],
            $registerData['email'],
            $registerData['password']
        );

        $authTokenDto = $this->authService->register($registerDto);

        return response()->json([
            'user' => $authTokenDto->user,
            'token' => $authTokenDto->token,
            'expiresAt' => $authTokenDto->expiresAt,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $loginData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $loginDto = new LoginUserDto(
            $loginData['email'],
            $loginData['password']
        );

        try {
            $authTokenDto = $this->authService->login($loginDto);

            return response()->json([
                'user' => $authTokenDto->user,
                'token' => $authTokenDto->token,
                'expiresAt' => $authTokenDto->expiresAt,
            ], 200);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function sendResetLink(Request $request): JsonResponse
    {
        $sendResetLinkData = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $sendResetLinkDto = new SendResetLinkDto($sendResetLinkData['email']);

        try {
            $this->authService->sendResetLink($sendResetLinkDto);

            return response()->json([
                'message' => 'Reset link sent to your email.'
            ]);
        } catch (ResetPasswordException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->merge([
            'password_confirmation' => $request->input('passwordConfirmation')
        ]);

        $resetPasswordData = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8'
        ]);

        $resetPasswordDto = new ResetPasswordDto(
            $resetPasswordData['email'],
            $resetPasswordData['token'],
            $resetPasswordData['password'],
            $request->input('password_confirmation')
        );

        try {
            $this->authService->resetPassword($resetPasswordDto);

            return response()->json([
                'message' => 'Password successfully reset.'
            ]);

        } catch (ResetPasswordException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}