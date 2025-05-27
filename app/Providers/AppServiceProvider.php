<?php

namespace App\Providers;

use App\Services\CreateTransactionValidatorService;
use App\Services\TPayService;
use App\Services\NodaService;
use App\Services\PaynowService;
use App\Services\TPaySignatureValidator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CreateTransactionValidatorService::class, function ($app): CreateTransactionValidatorService {
            return new CreateTransactionValidatorService();
        });

        $this->app->singleton('tpay-signature-validator', function ($app) {
            return new TPaySignatureValidator();
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
            Request::setTrustedProxies(
                ['*'],
                Request::HEADER_X_FORWARDED_PROTO
            );

            URL::forceScheme('https');
        }
    }
}
