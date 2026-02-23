<?php

namespace App\Models;

use App\Traits\HasSubscription;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, HasUlid, HasSubscription, SoftDeletes;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'owner_id',
        'avatar',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TeamMember::class, 'team_id', 'id', 'id', 'user_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    // subscriptions(), subscription(), activePlan(), onTrial(), subscribed(),
    // feature(), features() — provided by HasSubscription trait.

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'subscribable');
    }

    public function paymentMethods(): MorphMany
    {
        return $this->morphMany(PaymentMethod::class, 'subscribable');
    }

    public function linkTags(): HasMany
    {
        return $this->hasMany(LinkTag::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('owner_id', $userId)
            ->orWhereHas('members', fn ($q) => $q->where('user_id', $userId));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    // activePlan(), onTrial(), subscribed(), feature(), features() — see HasSubscription.
}
