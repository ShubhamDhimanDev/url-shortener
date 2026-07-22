## CONTEXT & CONSTRAINTS

You are building a **multi-tenant SaaS URL Shortener** on the following stack:
- **Laravel 12**, MySQL, Blade (Inertia + React will come later — keep the frontend layer swappable)
- **Spatie Laravel Permission** already installed
- **Razorpay** as initial payment gateway — architecture must be payment-gateway agnostic
- Theme files are in `resources/views/stitch_link_management_creation/` — use them as Blade layout reference
- All emails and notifications **must** go through **Laravel Queues**
- Use **Events, Listeners, Observers, Service classes, Action classes, Notifications, Policies, FormRequests, Resource classes** wherever appropriate
- Code must be **scalable, clean, and production-ready**
- Follow **SOLID principles** and **repository pattern** where it adds value

---

## ROLES

| Role | Guard | Description |
|---|---|---|
| `super_admin` | web | Full platform access |
| `team_owner` | web | Owns a team, manages members, billing |
| `team_member` | web | Uses team's quota to create links |
| `individual` | web | Solo user on their own plan |

Use Spatie's `roles` and `permissions` to gate all features. Define granular permissions (e.g. `links.create`, `analytics.view`, `domains.manage`, `billing.manage`).

---

## DATABASE SCHEMA

Generate migrations in this order. Every table must have `ulid` as the public-facing ID alongside the auto-increment PK. Use `$table->ulid('ulid')->unique()` pattern.

### 1. `users`
```
id, ulid, name, email, password, avatar, timezone, locale,
email_verified_at, two_factor_secret, two_factor_recovery_codes,
impersonated_by (FK users nullable), last_login_at,
is_active (bool default true), timestamps, softDeletes
```

### 2. `teams`
```
id, ulid, name, slug (unique), owner_id (FK users),
avatar, description, is_active, timestamps, softDeletes
```

### 3. `team_members`
```
id, team_id (FK), user_id (FK), role (enum: owner,admin,member),
joined_at, timestamps
unique(team_id, user_id)
```

### 4. `plans`
```
id, ulid, name, slug (unique), description,
price_monthly, price_yearly, currency (default INR),
is_active, is_public, sort_order,
trial_days (int default 0),
timestamps, softDeletes
```

### 5. `plan_features`
```
id, plan_id (FK), feature_key (enum — see below), feature_value (string),
timestamps
index(plan_id, feature_key)

Feature keys (enum):
links_per_month, custom_domains_count, domain_whitelist (bool),
custom_subdomain (bool), meta_tracking (bool), google_tracking (bool),
analytics_level (enum: none/basic/advanced), qrcode (bool),
password_protected_links (bool), geo_metrics (bool),
bot_detection (bool), spam_detection (bool),
team_members_count, link_expiry (bool), bulk_links (bool),
campaign_tracking (bool)
```

### 6. `subscriptions`
```
id, ulid, subscribable_type (morphs — user or team), subscribable_id,
plan_id (FK), gateway (string default razorpay),
gateway_subscription_id, gateway_customer_id,
status (enum: trialing,active,past_due,cancelled,expired),
trial_ends_at, current_period_start, current_period_end,
cancelled_at, ends_at, timestamps, softDeletes
```

### 7. `invoices`
```
id, ulid, subscribable_type, subscribable_id,
subscription_id (FK nullable), plan_id (FK nullable),
gateway, gateway_invoice_id, gateway_payment_id,
amount, tax, total, currency,
status (enum: draft,open,paid,void,uncollectible),
description, paid_at, due_at, pdf_url,
metadata (json), timestamps
```

### 8. `payment_methods`
```
id, subscribable_type, subscribable_id,
gateway, gateway_token, type (enum: card,upi,netbanking,wallet),
last_four, brand, exp_month, exp_year,
is_default, timestamps
```

### 9. `domains`
```
id, ulid, user_id (FK), team_id (FK nullable),
domain (unique), type (enum: custom_subdomain, custom_domain),
is_verified, is_active, verification_token, verified_at,
ssl_status (enum: pending,active,failed), timestamps, softDeletes
```

### 10. `links`
```
id, ulid, user_id (FK), team_id (FK nullable), domain_id (FK nullable),
short_code (unique indexed), destination_url (text),
title, description, og_image,
password (nullable, hashed), is_password_protected (bool),
is_active (bool default true),
expires_at (nullable), utm_source, utm_medium, utm_campaign, utm_term, utm_content,
meta_pixel_id (nullable), google_tag_id (nullable),
is_bot_protection_enabled (bool default false),
is_spam_detected (bool default false),
clicks_count (unsignedBigInt default 0, denormalized),
unique_clicks_count (unsignedBigInt default 0),
timestamps, softDeletes
index(short_code), index(user_id), index(team_id)
```

### 11. `link_clicks`
```
id, link_id (FK), session_hash (varchar 64 — sha256 of ip+ua+date for unique detection),
ip_address, country, country_code, region, city, latitude, longitude,
device_type (enum: desktop,mobile,tablet,bot,unknown),
os, os_version, browser, browser_version,
referrer_url, referrer_domain,
utm_source, utm_medium, utm_campaign, utm_term, utm_content,
is_bot (bool default false), bot_name (nullable),
is_unique (bool),
clicked_at (timestamp — NOT use created_at for this),
timestamps
index(link_id), index(clicked_at), index(is_bot), index(country_code)
```

### 12. `qrcodes`
```
id, link_id (FK), foreground_color, background_color,
logo_url, size, format (enum: png,svg,pdf),
file_path, timestamps
```

### 13. `link_tags`
```
id, name, slug, user_id (FK), team_id (FK nullable), color, timestamps
```

### 14. `link_tag_pivot`
```
link_id (FK), tag_id (FK), primary key composite
```

### 15. `activity_logs`
```
id, causer_type, causer_id, subject_type, subject_id,
event, description, properties (json), ip_address, user_agent,
created_at
index(causer_type, causer_id), index(subject_type, subject_id)
```

### 16. `notifications` (Laravel default — already exists)

### 17. `settings`
```
id, group (string), key (string), value (text), cast (string),
unique(group, key)
```

---

## PROJECT STRUCTURE

Create the following directories and base classes:

```
app/
├── Actions/
│   ├── Links/CreateLinkAction.php
│   ├── Links/UpdateLinkAction.php
│   ├── Links/DeleteLinkAction.php
│   ├── Links/RedirectLinkAction.php
│   ├── Analytics/RecordClickAction.php
│   ├── Subscriptions/CreateSubscriptionAction.php
│   ├── Subscriptions/CancelSubscriptionAction.php
│   ├── Teams/CreateTeamAction.php
│   ├── Teams/InviteMemberAction.php
│   └── QrCodes/GenerateQrCodeAction.php
├── Contracts/
│   ├── PaymentGatewayInterface.php
│   └── AnalyticsDriverInterface.php
├── Services/
│   ├── Payment/
│   │   ├── PaymentGatewayManager.php   ← resolves correct driver
│   │   ├── Drivers/RazorpayDriver.php
│   │   └── Drivers/StripeDriver.php    ← stub for future
│   ├── Analytics/
│   │   ├── AnalyticsService.php
│   │   ├── BotDetectionService.php
│   │   └── GeoLocationService.php
│   ├── LinkService.php
│   ├── QrCodeService.php
│   ├── SpamDetectionService.php
│   └── ImpersonationService.php
├── Events/
│   ├── Links/LinkCreated.php
│   ├── Links/LinkClicked.php
│   ├── Links/LinkDeleted.php
│   ├── Subscriptions/SubscriptionCreated.php
│   ├── Subscriptions/SubscriptionCancelled.php
│   ├── Subscriptions/SubscriptionRenewed.php
│   └── Teams/MemberInvited.php
├── Listeners/
│   ├── Links/SendLinkCreatedNotification.php
│   ├── Links/RecordLinkClick.php
│   ├── Subscriptions/SendWelcomeEmail.php
│   ├── Subscriptions/SendCancellationEmail.php
│   └── Teams/SendTeamInviteEmail.php
├── Notifications/
│   ├── Links/LinkCreatedNotification.php
│   ├── Auth/WelcomeNotification.php
│   ├── Subscriptions/SubscriptionConfirmedNotification.php
│   ├── Subscriptions/SubscriptionCancelledNotification.php
│   ├── Subscriptions/PaymentFailedNotification.php
│   ├── Subscriptions/TrialEndingNotification.php
│   ├── Teams/TeamInviteNotification.php
│   └── Admin/NewUserRegisteredNotification.php
├── Observers/
│   ├── LinkObserver.php
│   ├── UserObserver.php
│   └── SubscriptionObserver.php
├── Policies/
│   ├── LinkPolicy.php
│   ├── TeamPolicy.php
│   ├── SubscriptionPolicy.php
│   └── DomainPolicy.php
├── Http/
│   ├── Middleware/
│   │   ├── CheckSubscriptionFeature.php
│   │   ├── CheckLinkQuota.php
│   │   ├── HandleImpersonation.php
│   │   └── TrackReferrer.php
│   ├── Requests/
│   │   ├── Links/StoreLinkRequest.php
│   │   ├── Links/UpdateLinkRequest.php
│   │   ├── Teams/StoreTeamRequest.php
│   │   └── Subscriptions/CreateSubscriptionRequest.php
│   └── Controllers/
│       ├── SuperAdmin/
│       │   ├── DashboardController.php
│       │   ├── UserController.php
│       │   ├── TeamController.php
│       │   ├── PlanController.php
│       │   ├── SubscriptionController.php
│       │   ├── InvoiceController.php
│       │   ├── ImpersonationController.php
│       │   ├── SettingsController.php
│       │   └── AnalyticsController.php
│       ├── App/
│       │   ├── DashboardController.php
│       │   ├── LinkController.php
│       │   ├── AnalyticsController.php
│       │   ├── DomainController.php
│       │   ├── TeamController.php
│       │   ├── BillingController.php
│       │   ├── QrCodeController.php
│       │   └── ProfileController.php
│       └── RedirectController.php   ← public, handles short URL resolution
├── Models/
│   ├── User.php
│   ├── Team.php
│   ├── TeamMember.php
│   ├── Plan.php
│   ├── PlanFeature.php
│   ├── Subscription.php
│   ├── Invoice.php
│   ├── PaymentMethod.php
│   ├── Domain.php
│   ├── Link.php
│   ├── LinkClick.php
│   ├── QrCode.php
│   ├── LinkTag.php
│   └── Setting.php
└── Console/
    └── Commands/
        ├── ProcessExpiredLinks.php
        ├── SendTrialEndingReminders.php
        └── SyncSubscriptionStatuses.php
```

---

## PHASE 1 — FOUNDATION

**Task:** Generate the complete foundation.

1. Create all migrations in the order listed above.
2. Create all Eloquent Models with:
   - `$fillable`, `$casts`, `$hidden` arrays
   - Relationships (hasMany, belongsTo, morphTo, morphMany, etc.)
   - Scopes: `scopeActive()`, `scopeExpired()`, `scopeForUser($userId)`, `scopeForTeam($teamId)`
   - Accessors/Mutators where needed (e.g. `Link::getShortUrlAttribute()`)
   - `HasUlids` trait or custom trait for ULID generation
   - `SoftDeletes` where specified
3. Create `DatabaseSeeder` with:
   - Super admin user (email: `admin@platform.test`, password: `password`)
   - 3 plans: Free, Pro, Business — with all plan features seeded in `plan_features`
   - Spatie roles and permissions seeded
4. Create `PermissionsSeeder` — define ALL permissions and assign to roles.

---

## PHASE 2 — PAYMENT GATEWAY ABSTRACTION

**Task:** Build gateway-agnostic payment layer.

1. Create `PaymentGatewayInterface` with methods:
   ```php
   createCustomer(User|Team $billable): array
   createSubscription(Subscription $subscription, Plan $plan, array $options): array
   cancelSubscription(Subscription $subscription): bool
   getSubscriptionStatus(string $gatewaySubscriptionId): string
   createInvoice(array $data): array
   getInvoice(string $gatewayInvoiceId): array
   handleWebhook(Request $request): void
   verifyWebhookSignature(Request $request): bool
   ```

2. Create `RazorpayDriver.php` implementing this interface using the `razorpay/razorpay` package.

3. Create `PaymentGatewayManager.php` extending `Illuminate\Support\Manager` — resolves driver based on config `payment.default`.

4. Register in `AppServiceProvider`: `$this->app->singleton(PaymentGatewayInterface::class, fn() => app(PaymentGatewayManager::class)->driver())`

5. Create `config/payment.php`:
   ```php
   return [
       'default' => env('PAYMENT_GATEWAY', 'razorpay'),
       'gateways' => [
           'razorpay' => ['key' => env('RAZORPAY_KEY'), 'secret' => env('RAZORPAY_SECRET')],
           'stripe'   => ['key' => env('STRIPE_KEY'), 'secret' => env('STRIPE_SECRET')],
       ],
   ];
   ```

6. Webhook route: `POST /webhooks/{gateway}` → `WebhookController@handle` — verify signature, fire appropriate events.

---

## PHASE 3 — LINK CORE

**Task:** Build the URL shortening engine.

1. `CreateLinkAction`:
   - Validate destination URL (block private IPs, localhost, known spam domains)
   - Generate unique `short_code` (6 chars base62, retry on collision)
   - Check user/team link quota against plan feature `links_per_month`
   - If custom domain requested, verify `Domain` is owned by user/team and verified
   - Fire `LinkCreated` event
   - Return created `Link` model

2. `RedirectController@redirect` (public route `/{shortCode}`):
   - Find link by `short_code` (cache for 5 min using `Cache::remember`)
   - Check: active, not expired, correct password if protected
   - Dispatch `RecordLinkClickJob` (queued) — never block the redirect
   - Inject meta pixel / Google tag snippets into redirect response if enabled on plan
   - Return 301/302 redirect

3. `RecordClickAction` (called by queued job):
   - Parse User-Agent → device, OS, browser
   - GeoIP lookup (use `stevebauman/location` or `torann/geoip`)
   - Bot detection: check UA against known bot list + honeypot header
   - Spam detection: flag links with suspicious click velocity
   - Compute `session_hash = sha256(ip + ua + date)` for unique detection
   - Check if `session_hash` exists today for this link → set `is_unique`
   - Insert `LinkClick` record
   - Increment denormalized counters on `links` table: `DB::table('links')->increment()`

4. `QrCodeService`: use `SimpleSoftwareIO/simple-qrcode` package. Support PNG, SVG. Store in `storage/app/qrcodes/`. Return signed URL.

5. `CheckLinkQuota` middleware: on any link creation request, check remaining quota; abort 402 if exceeded.

---

## PHASE 4 — ANALYTICS SERVICE

**Task:** Build analytics aggregation.

1. `AnalyticsService` with methods:
   ```php
   getSummary(Link $link, Carbon $from, Carbon $to): array
   // Returns: total_clicks, unique_clicks, top_countries[], top_referrers[],
   //          top_devices[], clicks_over_time[], top_browsers[], top_os[]

   getTeamSummary(Team $team, Carbon $from, Carbon $to): array
   getUserSummary(User $user, Carbon $from, Carbon $to): array
   getPlatformSummary(Carbon $from, Carbon $to): array  // super admin only
   ```

2. Use **raw MySQL queries** (not Eloquent) for aggregations to keep them fast. Example:
   ```sql
   SELECT country, COUNT(*) as count FROM link_clicks
   WHERE link_id = ? AND clicked_at BETWEEN ? AND ? AND is_bot = 0
   GROUP BY country ORDER BY count DESC LIMIT 10
   ```

3. Cache analytics responses for 10 minutes using tagged cache: `Cache::tags(['analytics', "link:{$link->id}"])->remember(...)`. Clear tags on new click.

4. `GeoLocationService`: wrapper around your chosen GeoIP package, with fallback to `null` gracefully.

5. `BotDetectionService`:
   - Maintain a config array of known bot UA substrings in `config/bots.php`
   - Check for headless browser signatures
   - Check for datacenter IP ranges (optional — use MaxMind if available)
   - Return `['is_bot' => bool, 'bot_name' => string|null]`

---

## PHASE 5 — SUBSCRIPTION FEATURES CHECK

**Task:** Plan feature gating system.

1. `FeatureService` (or add to `Subscription` model):
   ```php
   can(string $featureKey): bool
   value(string $featureKey): mixed
   remaining(string $featureKey): int|null  // e.g. links remaining this month
   ```

2. `CheckSubscriptionFeature` middleware:
   ```php
   // Usage in routes: ->middleware('feature:qrcode')
   // Checks if current user/team subscription has the feature enabled
   ```

3. Add `HasSubscription` trait to `User` and `Team`:
   ```php
   public function subscription(): HasOne { ... }
   public function activePlan(): BelongsTo { ... }
   public function onTrial(): bool { ... }
   public function subscribed(): bool { ... }
   public function feature(string $key): mixed { ... }
   ```

4. Artisan commands:
   - `links:expire` — soft-delete expired links, fire `LinkExpired` event
   - `subscriptions:trial-reminders` — notify users 3 days before trial ends
   - `subscriptions:sync` — call gateway API to sync status (scheduled daily)
   - Schedule all in `Console/Kernel.php`

---

## PHASE 6 — SUPER ADMIN PANEL

**Task:** Build the super admin area.

All routes prefixed `/super-admin`, middleware: `auth`, `role:super_admin`.

### Controllers & Views

1. **DashboardController**: stats — total users, links, clicks today, MRR (monthly recurring revenue from active subscriptions), new signups chart.

2. **UserController**: list (searchable, filterable by plan/status), show (user detail with subscription, links), create, edit (name, email, role, plan override), toggle active, delete (soft).

3. **TeamController**: list, show (members, subscription, usage), edit, delete.

4. **PlanController**: CRUD for plans + nested CRUD for plan features. Toggle active/public. Reorder.

5. **SubscriptionController**: list all subscriptions with filters (status, plan, gateway). Show detail. Manual override (admin can change plan, reset period, cancel).

6. **InvoiceController**: list all invoices. Show. Download PDF (generate using `barryvdh/laravel-dompdf`). Void invoice.

7. **ImpersonationController**:
   ```php
   // POST /super-admin/impersonate/{user}
   public function impersonate(User $user): RedirectResponse
   {
       // Store original admin ID in session: session(['impersonator_id' => auth()->id()])
       // Login as target user: Auth::loginUsingId($user->id)
       // Set $user->impersonated_by = admin->id, save
       // Redirect to app dashboard
   }
   // DELETE /super-admin/impersonate (stop impersonating)
   public function stopImpersonating(): RedirectResponse
   {
       // Restore original admin from session
   }
   ```
   - Add `HandleImpersonation` middleware — injects a banner into every Blade view when impersonating.
   - Add `impersonating()` helper to `User` model.

8. **SettingsController**: Key-value settings UI grouped by category (general, mail, payment, features, limits). Use the `settings` table. Cache settings with `config()` override pattern.

9. **AnalyticsController**: platform-wide analytics — top links, top users, click geography heatmap data, device breakdown.

---

## PHASE 7 — USER/TEAM APP PANEL

**Task:** Build the user-facing application.

All routes prefixed `/app`, middleware: `auth`, `verified`, `CheckSubscriptionFeature`.

1. **DashboardController**: user's links stats, recent clicks, plan usage bar (links used / limit).

2. **LinkController**: 
   - `index` — paginated list with search, filter by tag/domain/status, sort
   - `create/store` — full link creation form: destination, custom slug, domain, expiry, password, UTM, meta/google tracking, tags
   - `show` — link detail + analytics summary
   - `edit/update`
   - `destroy` (soft delete)
   - `toggle` (activate/deactivate)

3. **AnalyticsController**: per-link analytics page with date range picker. Charts for: clicks over time (line), countries (map data), devices (donut), browsers, referrers, UTM campaigns.

4. **DomainController**: add custom domain, verify (DNS TXT record check), list, delete.

5. **TeamController**: create team, invite members (email invite with signed URL), manage roles, remove members.

6. **BillingController**: current plan display, upgrade/downgrade (shows plan comparison), payment method management, invoice list + download.

7. **QrCodeController**: generate QR for a link (POST), customize colors, download.

8. **ProfileController**: update name, email, password, avatar, timezone, 2FA setup.

---

## PHASE 8 — EVENTS, LISTENERS, NOTIFICATIONS (QUEUED)

**Task:** Wire up all async communication.

### EventServiceProvider — registerAll:
```php
LinkCreated::class => [SendLinkCreatedNotification::class],
LinkClicked::class => [RecordLinkClick::class],
SubscriptionCreated::class => [SendWelcomeEmail::class, NotifyAdminNewSubscription::class],
SubscriptionCancelled::class => [SendCancellationEmail::class],
SubscriptionRenewed::class => [SendRenewalReceiptEmail::class],
MemberInvited::class => [SendTeamInviteEmail::class],
UserRegistered::class => [SendWelcomeNotification::class, NotifyAdminNewUser::class],
```

All Listeners must `implement ShouldQueue` and define `public $queue = 'notifications'`.

### Notifications (all extend `Notification implements ShouldQueue`):
Each notification must implement both `toMail()` and `toDatabase()` channels.

- `WelcomeNotification` — sent on registration
- `SubscriptionConfirmedNotification` — with plan details and invoice link
- `SubscriptionCancelledNotification` — with end date
- `PaymentFailedNotification` — with retry link
- `TrialEndingNotification` — 3 days before trial end with upgrade CTA
- `TeamInviteNotification` — signed URL valid 48 hours
- `LinkCreatedNotification` — optional, only if user opts in
- `Admin\NewUserRegisteredNotification` — to super admin

### Queues:
In `.env` set `QUEUE_CONNECTION=database`. Create queue tables. Define these queue names in `config/queue.php`:
- `default` — general jobs
- `notifications` — email/notification jobs
- `analytics` — click recording jobs (high volume, separate worker)
- `webhooks` — payment webhook processing

---

## PHASE 9 — OBSERVERS

Register in `AppServiceProvider`:

**`LinkObserver`**:
- `creating`: generate `short_code` if not set, sanitize destination URL
- `created`: fire `LinkCreated` event
- `updated`: clear analytics cache for this link
- `deleted`: clear cache, fire `LinkDeleted` event

**`UserObserver`**:
- `created`: assign default role, fire `UserRegistered` event
- `updated`: if email changed, reset `email_verified_at`

**`SubscriptionObserver`**:
- `updated`: if status changed to `cancelled` fire `SubscriptionCancelled`, if changed to `active` fire `SubscriptionCreated`/`SubscriptionRenewed`

---

## PHASE 10 — ROUTES

```php
// Public
Route::get('/{shortCode}', [RedirectController::class, 'redirect'])->name('redirect');
Route::post('/{shortCode}/unlock', [RedirectController::class, 'unlock'])->name('redirect.unlock');

// Auth routes (Laravel Breeze/Fortify)

// Webhooks
Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle']);

// App (authenticated users)
Route::prefix('app')->middleware(['auth', 'verified'])->name('app.')->group(function () {
    // dashboard, links, analytics, domains, teams, billing, qrcodes, profile
});

// Super Admin
Route::prefix('super-admin')->middleware(['auth', 'role:super_admin'])->name('super-admin.')->group(function () {
    // dashboard, users, teams, plans, subscriptions, invoices, impersonate, settings, analytics
});
```

---

## BLADE VIEWS STRUCTURE

Use the stitch theme files in `resources/views/stitch_link_management_creation/` as reference for the design. Create layouts:

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← authenticated user layout
│   ├── super-admin.blade.php  ← admin layout
│   └── auth.blade.php         ← login/register
├── components/
│   ├── stats-card.blade.php
│   ├── link-card.blade.php
│   ├── plan-badge.blade.php
│   ├── impersonation-banner.blade.php
│   └── feature-gate.blade.php  ← shows upgrade prompt if feature locked
├── app/
│   ├── dashboard.blade.php
│   ├── links/ (index, create, edit, show)
│   ├── analytics/ (show)
│   ├── domains/ (index, create)
│   ├── teams/ (index, show, settings)
│   ├── billing/ (index, plans)
│   └── profile/ (edit)
└── super-admin/
    ├── dashboard.blade.php
    ├── users/ (index, show, edit)
    ├── teams/ (index, show)
    ├── plans/ (index, create, edit)
    ├── subscriptions/ (index, show)
    ├── invoices/ (index, show)
    └── settings/ (index)
```

---

## ADDITIONAL IMPLEMENTATION NOTES

### Spam Detection
In `SpamDetectionService`:
- Flag links whose destination is in a configurable blocklist (store in `settings` table under `spam.blocklist`)
- Flag links receiving >500 clicks from same IP in 1 hour (use Redis counter or DB query)
- Mark `links.is_spam_detected = true`, fire event, optionally deactivate

### Custom Subdomain Support
- If plan has `custom_subdomain = true`, allow user to choose `{slug}.platform.com`
- Store in `domains` table with `type = custom_subdomain`
- In `RedirectController`, resolve domain from `Request::getHost()` first, fall back to platform domain

### Config & Settings Helper
Create `app/Helpers/settings.php`:
```php
function setting(string $key, mixed $default = null): mixed {
    return Cache::rememberForever("setting:{$key}", fn() =>
        \App\Models\Setting::query()
            ->where('key', $key)->value('value') ?? $default
    );
}
```
Register in `composer.json` autoload files.

### Feature Flag Helper in Blade
```php
// In AppServiceProvider, share to all views:
View::composer('*', function ($view) {
    if (auth()->check()) {
        $view->with('currentSubscription', auth()->user()->subscription);
    }
});
```
Blade component `<x-feature-gate feature="qrcode">...</x-feature-gate>` checks plan feature and shows upgrade CTA if locked.

---

## PACKAGES TO INSTALL

Run these:
```bash
composer require spatie/laravel-permission         # already installed
composer require razorpay/razorpay
composer require simplesoftwareio/simple-qrcode
composer require stevebauman/location
composer require barryvdh/laravel-dompdf
composer require spatie/laravel-activitylog
composer require laravel/breeze                    # for auth scaffolding
```

---

## FINAL CHECKLIST BEFORE COMMITTING

- [ ] All migrations run cleanly: `php artisan migrate:fresh --seed`
- [ ] All relationships tested in Tinker
- [ ] Queued jobs dispatch without errors (`php artisan queue:work --queue=analytics,notifications,default`)
- [ ] `php artisan route:list` shows all expected routes
- [ ] Policies registered in `AuthServiceProvider`
- [ ] Observers registered in `AppServiceProvider`
- [ ] All Notifications have both `toMail` and `toDatabase` channels
- [ ] `.env.example` updated with all new keys (`RAZORPAY_KEY`, `RAZORPAY_SECRET`, `PAYMENT_GATEWAY`, `GEOIP_SERVICE`, etc.)
- [ ] `config/payment.php`, `config/bots.php` created
- [ ] No raw SQL except in `AnalyticsService` aggregations

---

## SESSION BREAKDOWN FOR COPILOT

Run Copilot Agent in separate sessions in this order:

| Session | Phases | Prompt Excerpt to Use |
|---|---|---|
| 1 | Migrations + Models | "Generate all migrations and Eloquent models per the schema in Phase 1..." |
| 2 | Seeders + Permissions | "Generate DatabaseSeeder and PermissionsSeeder per Phase 1..." |
| 3 | Payment Gateway | "Implement the payment gateway abstraction layer per Phase 2..." |
| 4 | Link Core + Redirect | "Implement CreateLinkAction, RedirectController, RecordClickAction per Phase 3..." |
| 5 | Analytics | "Implement AnalyticsService, BotDetectionService, GeoLocationService per Phase 4..." |
| 6 | Subscription Feature Gating | "Implement FeatureService, HasSubscription trait, middleware per Phase 5..." |
| 7 | Super Admin Panel | "Generate all Super Admin controllers and Blade views per Phase 6..." |
| 8 | User App Panel | "Generate all App controllers and Blade views per Phase 7..." |
| 9 | Events/Listeners/Notifications | "Wire up all events, listeners, notifications, and queues per Phase 8..." |
| 10 | Observers + Helpers + Commands | "Implement observers, artisan commands, helpers per Phases 9 and additional notes..." |
