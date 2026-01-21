<?php

namespace App\Providers;

use App\Services\TransactionValidatorService;
use App\Services\TPayService;
use App\Services\NodaService;
use App\Services\PaynowService;
use App\Services\TPaySignatureValidator;
use App\Services\TransactionSignatureService;
use App\Auth\ApiKeyGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;
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
    }
}
