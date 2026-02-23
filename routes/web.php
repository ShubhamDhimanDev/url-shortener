<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\App\AnalyticsController as AppAnalyticsController;
use App\Http\Controllers\App\BillingController;
use App\Http\Controllers\App\DashboardController as AppDashboardController;
use App\Http\Controllers\App\DomainController;
use App\Http\Controllers\App\LinkController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\QrCodeController;
use App\Http\Controllers\App\TeamController as AppTeamController;
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
| Authentication – Account Suspended
|--------------------------------------------------------------------------
|
| Named route for the "account deactivated" page shown after CheckUserIsActive
| logs someone out. No auth guard needed — user is already logged out.
|
*/
Route::get('/account-suspended', fn () => view('auth.suspended'))->name('auth.suspended');

/*
|--------------------------------------------------------------------------
| Authentication – Guest Routes
|--------------------------------------------------------------------------
|
| Routes accessible only to unauthenticated visitors.
| The `guest` middleware redirects already-logged-in users to /app/dashboard.
|
*/
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register',  [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Forgot password
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');

    // Reset password
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password',        [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authentication – Authenticated Routes
|--------------------------------------------------------------------------
|
| Logout and email verification routes — require an authenticated session.
| The `checkActive` middleware is applied globally (bootstrap/app.php) so
| suspended users are caught before reaching these routes.
|
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/email/verify',               [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}',   [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:1,1')
        ->name('verification.send');
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
| User / Team App Panel
|--------------------------------------------------------------------------
|
| All routes prefixed /app. Requires authenticated + verified users.
| HandleImpersonation middleware injects the impersonation banner if active.
|
*/
Route::prefix('app')
    ->middleware(['auth', 'verified', \App\Http\Middleware\HandleImpersonation::class])
    ->name('app.')
    ->group(function () {

        // Dashboard
        Route::get('/', [AppDashboardController::class, 'index'])->name('dashboard');

        // Links
        Route::prefix('links')->name('links.')->group(function () {
            Route::get('/',                  [LinkController::class, 'index'])->name('index');
            Route::get('/create',            [LinkController::class, 'create'])->name('create');
            Route::post('/',                 [LinkController::class, 'store'])->name('store');
            Route::get('/{ulid}',            [LinkController::class, 'show'])->name('show');
            Route::get('/{ulid}/edit',       [LinkController::class, 'edit'])->name('edit');
            Route::put('/{ulid}',            [LinkController::class, 'update'])->name('update');
            Route::delete('/{ulid}',         [LinkController::class, 'destroy'])->name('destroy');
            Route::patch('/{ulid}/toggle',   [LinkController::class, 'toggle'])->name('toggle');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/{ulid}', [AppAnalyticsController::class, 'show'])->name('show');
        });

        // QR Codes
        Route::prefix('qrcodes')->name('qrcodes.')->group(function () {
            Route::get('/{ulid}',            [QrCodeController::class, 'show'])->name('show');
            Route::post('/{ulid}/generate',  [QrCodeController::class, 'generate'])->name('generate');
            Route::get('/{ulid}/download',   [QrCodeController::class, 'download'])->name('download');
        });

        // Domains
        Route::prefix('domains')->name('domains.')->group(function () {
            Route::get('/',             [DomainController::class, 'index'])->name('index');
            Route::get('/create',       [DomainController::class, 'create'])->name('create');
            Route::post('/',            [DomainController::class, 'store'])->name('store');
            Route::post('/{domain}/verify', [DomainController::class, 'verify'])->name('verify');
            Route::delete('/{domain}',  [DomainController::class, 'destroy'])->name('destroy');
        });

        // Teams
        Route::prefix('teams')->name('teams.')->group(function () {
            Route::get('/',                          [AppTeamController::class, 'index'])->name('index');
            Route::get('/create',                    [AppTeamController::class, 'create'])->name('create');
            Route::post('/',                         [AppTeamController::class, 'store'])->name('store');
            Route::get('/{ulid}',                    [AppTeamController::class, 'show'])->name('show');
            Route::get('/{ulid}/settings',           [AppTeamController::class, 'settings'])->name('settings');
            Route::put('/{ulid}',                    [AppTeamController::class, 'update'])->name('update');
            Route::post('/{ulid}/invite',            [AppTeamController::class, 'invite'])->name('invite');
            Route::delete('/{ulid}/members/{id}',   [AppTeamController::class, 'removeMember'])->name('members.remove');
            Route::delete('/{ulid}/leave',           [AppTeamController::class, 'leave'])->name('leave');
            Route::post('/switch-context',           [AppTeamController::class, 'switchContext'])->name('switch-context');
        });

        // Billing
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/',                    [BillingController::class, 'index'])->name('index');
            Route::get('/plans',               [BillingController::class, 'plans'])->name('plans');
            Route::post('/subscribe',          [BillingController::class, 'subscribe'])->name('subscribe');
            Route::post('/cancel',             [BillingController::class, 'cancel'])->name('cancel');
            Route::get('/invoices/{ulid}/download', [BillingController::class, 'downloadInvoice'])->name('invoices.download');
        });

        // Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',               [ProfileController::class, 'edit'])->name('edit');
            Route::put('/',               [ProfileController::class, 'update'])->name('update');
            Route::put('/password',       [ProfileController::class, 'updatePassword'])->name('password');
            Route::delete('/',            [ProfileController::class, 'destroy'])->name('destroy');
        });
    });

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

