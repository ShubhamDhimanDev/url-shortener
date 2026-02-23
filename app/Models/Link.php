<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Link extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'ulid',
        'user_id',
        'team_id',
        'domain_id',
        'short_code',
        'destination_url',
        'title',
        'description',
        'og_image',
        'password',
        'is_password_protected',
        'is_active',
        'expires_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'meta_pixel_id',
        'google_tag_id',
        'is_bot_protection_enabled',
        'is_spam_detected',
        'clicks_count',
        'unique_clicks_count',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_password_protected'    => 'boolean',
        'is_active'                => 'boolean',
        'is_bot_protection_enabled'=> 'boolean',
        'is_spam_detected'         => 'boolean',
        'expires_at'               => 'datetime',
        'clicks_count'             => 'integer',
        'unique_clicks_count'      => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LinkTag::class, 'link_tag_pivot', 'link_id', 'tag_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(fn ($q) =>
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now())
        );
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getShortUrlAttribute(): string
    {
        if ($this->domain) {
            return 'https://' . $this->domain->domain . '/' . $this->short_code;
        }

        return url('/' . $this->short_code);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isAccessible(): bool
    {
        return $this->is_active && ! $this->is_expired;
    }
}
