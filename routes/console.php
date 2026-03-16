<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Registered commands are resolved from the service container, so dependency
| injection (e.g. PaymentGatewayInterface) works out of the box.
|
*/

// Soft-delete expired links every hour and fire LinkExpired events.
Schedule::command('links:expire')->hourly()->withoutOverlapping();

// Notify users whose trial ends within 3 days — runs once a day at 09:00.
Schedule::command('subscriptions:trial-reminders')->dailyAt('09:00')->withoutOverlapping();

// Sync subscription statuses with the payment gateway — runs nightly at 02:00.
Schedule::command('subscriptions:sync')->dailyAt('02:00')->withoutOverlapping()->runInBackground();

// Renew SSL certificates for custom domains expiring within 30 days — runs weekly at 03:00.
Schedule::command('domains:renew-ssl')->weekly()->at('03:00')->withoutOverlapping()->runInBackground();
