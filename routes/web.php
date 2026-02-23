<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SuperAdmin\AnalyticsController as SuperAdminAnalyticsController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\ImpersonationController;
use App\Http\Controllers\SuperAdmin\InvoiceController as SuperAdminInvoiceController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\SuperAdmin\SubscriptionController as SuperAdminSubscriptionController;
use App\Http\Controllers\SuperAdmin\TeamController as SuperAdminTeamController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
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

/*
|--------------------------------------------------------------------------
| Super Admin Panel
|--------------------------------------------------------------------------
|
| All routes here are prefixed /super-admin and require the auth guard
| plus the super_admin Spatie role. The HandleImpersonation middleware
| is also applied globally so the impersonation banner is always visible.
|
*/
Route::prefix('super-admin')
    ->middleware(['auth', 'role:super_admin', \App\Http\Middleware\HandleImpersonation::class])
    ->name('super-admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',          [SuperAdminUserController::class, 'index'])->name('index');
            Route::get('/create',    [SuperAdminUserController::class, 'create'])->name('create');
            Route::post('/',         [SuperAdminUserController::class, 'store'])->name('store');
            Route::get('/{user}',    [SuperAdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit',  [SuperAdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}',    [SuperAdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [SuperAdminUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle-active', [SuperAdminUserController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('/{id}/restore', [SuperAdminUserController::class, 'restore'])->name('restore');
        });

        // Teams
        Route::prefix('teams')->name('teams.')->group(function () {
            Route::get('/',             [SuperAdminTeamController::class, 'index'])->name('index');
            Route::get('/{team}',       [SuperAdminTeamController::class, 'show'])->name('show');
            Route::get('/{team}/edit',  [SuperAdminTeamController::class, 'edit'])->name('edit');
            Route::put('/{team}',       [SuperAdminTeamController::class, 'update'])->name('update');
            Route::delete('/{team}',    [SuperAdminTeamController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/restore', [SuperAdminTeamController::class, 'restore'])->name('restore');
        });

        // Plans
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/',             [PlanController::class, 'index'])->name('index');
            Route::get('/create',       [PlanController::class, 'create'])->name('create');
            Route::post('/',            [PlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit',  [PlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}',       [PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}',    [PlanController::class, 'destroy'])->name('destroy');
            Route::patch('/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/reorder',     [PlanController::class, 'reorder'])->name('reorder');
        });

        // Subscriptions
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/',                        [SuperAdminSubscriptionController::class, 'index'])->name('index');
            Route::get('/{subscription}',          [SuperAdminSubscriptionController::class, 'show'])->name('show');
            Route::put('/{subscription}',          [SuperAdminSubscriptionController::class, 'update'])->name('update');
            Route::patch('/{subscription}/cancel', [SuperAdminSubscriptionController::class, 'cancel'])->name('cancel');
        });

        // Invoices
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/',                       [SuperAdminInvoiceController::class, 'index'])->name('index');
            Route::get('/{invoice}',              [SuperAdminInvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/download',     [SuperAdminInvoiceController::class, 'download'])->name('download');
            Route::patch('/{invoice}/void',       [SuperAdminInvoiceController::class, 'void'])->name('void');
        });

        // Impersonation
        Route::post('/impersonate/{user}', [ImpersonationController::class, 'impersonate'])->name('impersonate');
        Route::delete('/impersonate',      [ImpersonationController::class, 'stopImpersonating'])->name('impersonate.stop');

        // Settings
        Route::get('/settings',  [SuperAdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings',  [SuperAdminSettingsController::class, 'update'])->name('settings.update');

        // Analytics
        Route::get('/analytics', [SuperAdminAnalyticsController::class, 'index'])->name('analytics.index');
    });

