<?php

namespace App\Providers;

use App\Events\Auth\UserRegistered;
use App\Events\Links\LinkClicked;
use App\Events\Links\LinkCreated;
use App\Events\Subscriptions\PaymentFailed;
use App\Events\Subscriptions\SubscriptionCancelled;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionRenewed;
use App\Events\Teams\MemberInvited;
use App\Listeners\Auth\NotifyAdminNewUser;
use App\Listeners\Auth\SendWelcomeNotification;
use App\Listeners\Links\RecordLinkClick;
use App\Listeners\Links\SendLinkCreatedNotification;
use App\Listeners\Subscriptions\HandlePaymentFailed;
use App\Listeners\Subscriptions\NotifyAdminNewSubscription;
use App\Listeners\Subscriptions\SendCancellationEmail;
use App\Listeners\Subscriptions\SendRenewalReceiptEmail;
use App\Listeners\Subscriptions\SendWelcomeEmail;
use App\Listeners\Teams\SendTeamInviteEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event-to-listener mappings for the application.
     *
     * All listeners implement ShouldQueue and route to named queues:
     *   - notifications  → email / notification workers
     *   - analytics      → high-volume click recording workers
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ── Links ─────────────────────────────────────────────────────────────
        LinkCreated::class => [
            SendLinkCreatedNotification::class,  // optional, opt-in per user
        ],

        LinkClicked::class => [
            RecordLinkClick::class,              // dispatches RecordLinkClickJob → analytics queue
        ],

        // ── Subscriptions ─────────────────────────────────────────────────────
        SubscriptionCreated::class => [
            SendWelcomeEmail::class,             // confirmation / trial-start email to subscriber
            NotifyAdminNewSubscription::class,   // alert all super_admins
        ],

        SubscriptionCancelled::class => [
            SendCancellationEmail::class,        // cancellation confirmation to subscriber
        ],

        PaymentFailed::class => [
            HandlePaymentFailed::class,          // payment-failed email with retry link
        ],

        SubscriptionRenewed::class => [
            SendRenewalReceiptEmail::class,      // renewal receipt to subscriber
        ],

        // ── Teams ─────────────────────────────────────────────────────────────
        MemberInvited::class => [
            SendTeamInviteEmail::class,          // 48-hour signed-URL invite email
        ],

        // ── Auth ──────────────────────────────────────────────────────────────
        UserRegistered::class => [
            SendWelcomeNotification::class,      // welcome email to new user
            NotifyAdminNewUser::class,           // alert all super_admins
        ],
    ];

    /**
     * Determine if events and listeners should be auto-discovered.
     * We keep this false and use the explicit $listen map above for
     * full control and performance in production.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
