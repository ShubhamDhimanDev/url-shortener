<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude payment webhook routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        // Redirect authenticated users away from guest-only pages (login, register, etc.)
        // to the main app dashboard instead of Laravel's default /home.
        $middleware->redirectUsersTo('/');

        // Named middleware aliases
        $middleware->alias([
            'link.quota'  => \App\Http\Middleware\CheckLinkQuota::class,
            'feature'     => \App\Http\Middleware\CheckSubscriptionFeature::class,
            'checkActive' => \App\Http\Middleware\CheckUserIsActive::class,
        ]);

        // Inject impersonation state (banner) for every web request so the
        // amber banner is visible even when an admin is browsing regular app pages.
        $middleware->appendToGroup('web', \App\Http\Middleware\HandleImpersonation::class);

        // Silently log out suspended users on every authenticated page load.
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckUserIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
