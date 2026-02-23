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

        // Named middleware aliases
        $middleware->alias([
            'link.quota' => \App\Http\Middleware\CheckLinkQuota::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
