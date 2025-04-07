<?php

namespace App\Providers;

use App\Services\CreateTransactionValidatorService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
