<?php

namespace App\Providers;

use App\Services\TransactionValidatorService;
use App\Services\TPayService;
use App\Services\NodaService;
use App\Services\PaynowService;
use App\Services\TPaySignatureValidator;
use App\Services\TransactionSignatureService;
use App\Auth\ApiKeyGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TransactionValidatorService::class, function ($app): TransactionValidatorService {
            return new TransactionValidatorService();
        });

        $this->app->singleton('tpay-signature-validator', function ($app) {
            return new TPaySignatureValidator();
        });

        $this->app->singleton('transaction-signature-service', function ($app) {
            return new TransactionSignatureService();
        });

        $this->app->bind(TPayService::class, function () {
            return new TPayService(new Client());
        });

        $this->app->bind(PaynowService::class, function () {
            return new PaynowService(new Client());
        });

        $this->app->bind(NodaService::class, function () {
            return new NodaService(new Client());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('apikey', function ($app, $name, array $config) {

            $provider = $app['auth']->createUserProvider(
                $config['provider'] ?? null
            );

            if (!$provider) {
                throw new \InvalidArgumentException(
                    'Invalid or missing auth provider for apikey guard.'
                );
            }

            return $app->make(ApiKeyGuard::class, [
                'provider' => $provider,
                'request' => $app['request'],
            ]);
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontendUrl') . "/reset-password?token={$token}&email={$user->email}";
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('create-transaction', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many attempts to create transactions. Please try again later.',
                        'retry_after' => $headers['Retry-After'],
                    ], 429, $headers);
                });
        });

        RateLimiter::for('create-payment-link', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many attempts to create payment link. Please try again later.',
                        'retry_after' => $headers['Retry-After'],
                    ], 429, $headers);
                });
        });
    }

}
