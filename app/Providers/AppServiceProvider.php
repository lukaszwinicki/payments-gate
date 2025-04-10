<?php

namespace App\Providers;

use App\Services\CreateTransactionValidatorService;
use App\Services\TPaySignatureValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CreateTransactionValidatorService::class, function($app): CreateTransactionValidatorService{
            return new CreateTransactionValidatorService();
        });

        $this->app->singleton('tpay-signature-validator', function ($app) {
            return new TPaySignatureValidator();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
