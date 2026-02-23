<?php

namespace App\Traits;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Subscription helper methods shared between User and Team models.
 *
 * Both models use a polymorphic `subscriptions` relationship, so the
 * methods here work identically for both entities.
 */
trait HasSubscription
{
    // ─── Relationships ────────────────────────────────────────────────────

    /**
     * All subscriptions (historical + active) for this entity.
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscribable');
    }

    /**
     * Eloquent relationship: the single active/trialing subscription.
     * Returning a MorphOne satisfies Eloquent's magic __get so that
     * both `$model->subscription` (property) and eager-loading work
     * correctly without the "must return a relationship instance" error.
     */
    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable')
            ->whereIn('status', ['active', 'trialing'])
            ->latest();
    }

    // ─── Computed helpers ─────────────────────────────────────────────────

    /**
     * Return the currently active or trialing Subscription model, or null.
     * Use this for direct access to the model in PHP code.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    /**
     * Return the Plan associated with the active subscription, or null.
     */
    public function activePlan(): ?Plan
    {
        return $this->activeSubscription()?->plan;
    }

    /**
     * Whether the entity is currently in a free trial period.
     */
    public function onTrial(): bool
    {
        $sub = $this->activeSubscription();

        return $sub !== null
            && $sub->status === 'trialing'
            && $sub->trial_ends_at?->isFuture();
    }

    /**
     * Whether the entity has an active (or trialing) subscription.
     */
    public function subscribed(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Return the raw feature value from the active plan for the given key,
     * or null if the entity has no active subscription / feature not set.
     */
    public function feature(string $key): mixed
    {
        return $this->activePlan()
            ?->features()
            ->where('feature_key', $key)
            ->value('feature_value');
    }

    /**
     * Return a FeatureService instance scoped to this entity.
     * Use this when you need `can()`, `value()`, or `remaining()`.
     *
     * @example
     *   $user->features()->can('qrcode');
     *   $user->features()->remaining('links_per_month');
     */
    public function features(): FeatureService
    {
        return FeatureService::for($this);
    }
}
