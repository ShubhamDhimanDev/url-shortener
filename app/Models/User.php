<?php

namespace App\Models;

use App\Traits\HasSubscription;
use App\Traits\HasUlid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, HasUlid, HasSubscription, SoftDeletes;

    protected $fillable = [
        'ulid',
        'name',
        'email',
        'password',
        'avatar',
        'timezone',
        'locale',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'impersonated_by',
        'last_login_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'two_factor_recovery_codes' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
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

    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonated_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->whereHas('teamMemberships', fn ($q) => $q->where('team_id', $teamId));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isImpersonating(): bool
    {
        return session()->has('impersonator_id');
    }
}
