<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Short-Link Redirect (public)
|--------------------------------------------------------------------------
|
| These two routes MUST be declared last (or have lower priority) to avoid
| shadowing named application routes. They catch any /{shortCode} requests
| that haven't been handled by a more specific route above.
|
*/
Route::get('/{shortCode}', [RedirectController::class, 'redirect'])
    ->name('redirect')
    ->where('shortCode', '[A-Za-z0-9_-]+');

Route::post('/{shortCode}/unlock', [RedirectController::class, 'unlock'])
    ->name('redirect.unlock')
    ->where('shortCode', '[A-Za-z0-9_-]+');

/*
|--------------------------------------------------------------------------
| Payment Webhooks
|--------------------------------------------------------------------------
|
| This route accepts incoming payment gateway webhook notifications.
| The {gateway} segment resolves the correct driver (e.g. razorpay, stripe).
| CSRF verification is excluded for this route via bootstrap/app.php.
|
*/
Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle'])
    ->name('webhooks.handle');
