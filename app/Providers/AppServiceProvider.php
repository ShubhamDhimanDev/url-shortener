<?php

namespace App\Providers;

use App\Contracts\AnalyticsDriverInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Link;
use App\Models\Subscription;
use App\Models\User;
use App\Observers\LinkObserver;
use App\Observers\SubscriptionObserver;
use App\Observers\UserObserver;
use App\Services\Analytics\AnalyticsService;
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

        // Bind the analytics driver interface to the concrete MySQL implementation.
        $this->app->singleton(AnalyticsService::class);
        $this->app->alias(AnalyticsService::class, AnalyticsDriverInterface::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Observers ────────────────────────────────────────────────────────
        // Registered here so they apply for every request / queue worker.
        Link::observe(LinkObserver::class);
        User::observe(UserObserver::class);
        Subscription::observe(SubscriptionObserver::class);

        // ── View Composers ───────────────────────────────────────────────────
        // Share the current subscription with all views for feature-gating.
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentSubscription', auth()->user()->activeSubscription());
            }
        });
    }
}
