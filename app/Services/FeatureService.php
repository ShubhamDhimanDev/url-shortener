<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Resolves plan feature flags and quotas for a given subscribable entity
 * (User or Team). Designed for use from middleware, controllers and Blade.
 *
 * Usage:
 *   $features = new FeatureService($user);
 *   $features->can('qrcode');              // true / false
 *   $features->value('analytics_level');   // 'advanced'
 *   $features->remaining('links_per_month'); // 47
 */
class FeatureService
{
    private ?Subscription $subscription;
    private ?Plan $plan;

    public function __construct(private readonly User|Team $entity)
    {
        $this->subscription = $entity->subscription();
        $this->plan = $this->subscription?->plan;
    }

    // ─── Factory ──────────────────────────────────────────────────────────

    /**
     * Convenience static constructor.
     */
    public static function for(User|Team $entity): static
    {
        return new static($entity);
    }

    // ─── Core API ─────────────────────────────────────────────────────────

    /**
     * Check whether a boolean or gated feature is enabled.
     * Always returns true for super_admin users.
     *
     * @param  string  $featureKey  One of the feature_key enum values.
     */
    public function can(string $featureKey): bool
    {
        // Super admins bypass all feature gates.
        if ($this->entity instanceof User && $this->entity->hasRole('super_admin')) {
            return true;
        }

        $raw = $this->value($featureKey);

        if ($raw === null) {
            return false;
        }

        // Numeric features (e.g. links_per_month, team_members_count) — enabled when > 0
        if (is_numeric($raw)) {
            return (int) $raw > 0;
        }

        // Boolean string features
        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Return the raw feature value from the active plan, or null if not set.
     */
    public function value(string $featureKey): mixed
    {
        if ($this->plan === null) {
            return null;
        }

        return $this->plan
            ->features()
            ->where('feature_key', $featureKey)
            ->value('feature_value');
    }

    /**
     * Return how many units of a countable feature remain for the current
     * billing period, or null if the feature is unlimited (value = 0 / null).
     *
     * Currently supports: `links_per_month`, `team_members_count`.
     */
    public function remaining(string $featureKey): ?int
    {
        $limit = (int) $this->value($featureKey);

        // 0 or missing means unlimited.
        if ($limit <= 0) {
            return null;
        }

        $used = match ($featureKey) {
            'links_per_month' => $this->usedLinksThisMonth(),
            'team_members_count' => $this->usedTeamMembers(),
            default => 0,
        };

        return max(0, $limit - $used);
    }

    /**
     * Whether the entity has an active (or trialing) subscription.
     */
    public function isSubscribed(): bool
    {
        return $this->subscription !== null && $this->subscription->isActive();
    }

    /**
     * Return the active subscription, or null.
     */
    public function subscription(): ?Subscription
    {
        return $this->subscription;
    }

    /**
     * Return the active plan, or null.
     */
    public function plan(): ?Plan
    {
        return $this->plan;
    }

    // ─── Private helpers ──────────────────────────────────────────────────

    private function usedLinksThisMonth(): int
    {
        $query = DB::table('links')
            ->whereNull('deleted_at')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        if ($this->entity instanceof Team) {
            $query->where('team_id', $this->entity->id);
        } else {
            $query->where('user_id', $this->entity->id)->whereNull('team_id');
        }

        return (int) $query->count();
    }

    private function usedTeamMembers(): int
    {
        if ($this->entity instanceof Team) {
            return $this->entity->members()->count();
        }

        // For a user, count members of teams they own.
        return (int) DB::table('team_members')
            ->whereIn('team_id', function ($q) {
                $q->select('id')->from('teams')->where('owner_id', $this->entity->id);
            })
            ->count();
    }
}
