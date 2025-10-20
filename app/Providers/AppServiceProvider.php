<?php

namespace App\Providers;

use App\Services\TransactionValidatorService;
use App\Services\TPayService;
use App\Services\NodaService;
use App\Services\PaynowService;
use App\Services\TPaySignatureValidator;
use App\Services\TransactionSignatureService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
