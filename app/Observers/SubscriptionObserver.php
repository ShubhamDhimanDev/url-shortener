<?php

namespace App\Observers;

use App\Events\Subscriptions\SubscriptionCancelled;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionRenewed;
use App\Models\Subscription;

/**
 * Observe Eloquent lifecycle hooks on the Subscription model.
 *
 * Responsibilities
 * - updated : detect status transitions and fire the appropriate domain event:
 *
 *   → cancelled  : always fire SubscriptionCancelled
 *   → active     : fire SubscriptionRenewed when the previous status was
 *                  also active/past_due (i.e. a billing cycle renewed),
 *                  otherwise fire SubscriptionCreated (new or reactivated).
 */
class SubscriptionObserver
{
    /**
     * The statuses that indicate a subscription was already live before the
     * current update — used to distinguish "renewal" from "fresh activation".
     */
    private const RENEWAL_STATUSES = ['active', 'past_due'];

    // ─── updated ─────────────────────────────────────────────────────────────

    public function updated(Subscription $subscription): void
    {
        // Only react when the status column has actually changed.
        if (! $subscription->wasChanged('status')) {
            return;
        }

        $newStatus      = $subscription->status;
        $previousStatus = $subscription->getOriginal('status');

        match ($newStatus) {
            'cancelled' => $this->handleCancelled($subscription),
            'active'    => $this->handleActivated($subscription, $previousStatus),
            default     => null,   // trialing / past_due / expired — no domain event here
        };
    }

    // ─── Private Handlers ────────────────────────────────────────────────────

    private function handleCancelled(Subscription $subscription): void
    {
        SubscriptionCancelled::dispatch($subscription);
    }

    /**
     * Determine whether the transition to 'active' represents a brand-new
     * subscription becoming active or an existing one being renewed/restored.
     *
     * Decision matrix:
     *  previous: trialing / cancelled / expired / null → SubscriptionCreated
     *  previous: active / past_due                    → SubscriptionRenewed
     */
    private function handleActivated(Subscription $subscription, ?string $previousStatus): void
    {
        if (in_array($previousStatus, self::RENEWAL_STATUSES, true)) {
            SubscriptionRenewed::dispatch($subscription);
        } else {
            SubscriptionCreated::dispatch($subscription);
        }
    }
}
