<?php

use App\Exceptions\RefundNotSupportedException;
use App\Exceptions\UnsupportedCurrencyException;
use App\Http\Middleware\ApiKeyMiddleware;
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
            'api-key' => ApiKeyMiddleware::class
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
    })->create();
