<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'is_active',
        'is_public',
        'sort_order',
        'trial_days',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly'  => 'decimal:2',
        'is_active'     => 'boolean',
        'is_public'     => 'boolean',
        'sort_order'    => 'integer',
        'trial_days'    => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isFree(): bool
    {
        return $this->price_monthly == 0 && $this->price_yearly == 0;
    }

    public function feature(string $key): mixed
    {
        return $this->features()->where('feature_key', $key)->value('feature_value');
    }
}
