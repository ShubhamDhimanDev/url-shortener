<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the payment gateway interface to the manager's default driver.
        // This allows type-hinting PaymentGatewayInterface anywhere in the app.
        $this->app->singleton(
            PaymentGatewayInterface::class,
            fn () => app(PaymentGatewayManager::class)->driver()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share the current subscription with all views for feature-gating.
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentSubscription', auth()->user()->subscription ?? null);
            }
        });
    }
}
