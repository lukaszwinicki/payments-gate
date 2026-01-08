<?php

use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\RefundNotSupportedException;
use App\Exceptions\UnexpectedStatusCodeException;
use App\Exceptions\UnsupportedCurrencyException;
use App\Http\Middleware\AuthOrApiKey;
use App\Http\Middleware\CheckTokenExpiration;
use App\Http\Middleware\EnsureMerchant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.or.apikey' => AuthOrApiKey::class,
            'token.expiration' => CheckTokenExpiration::class,
            'merchant' => EnsureMerchant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (RefundNotSupportedException $e, Request $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        });

        $exceptions->render(function (UnsupportedCurrencyException $e, Request $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        });

        $exceptions->render(function (UnexpectedStatusCodeException $e, Request $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        });

        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        });
    })->create();
