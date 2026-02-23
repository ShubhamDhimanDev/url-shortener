<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'link_id',
        'session_hash',
        'ip_address',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
        'device_type',
        'os',
        'os_version',
        'browser',
        'browser_version',
        'referrer_url',
        'referrer_domain',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'is_bot',
        'bot_name',
        'is_unique',
        'clicked_at',
    ];

    protected $casts = [
        'is_bot'     => 'boolean',
        'is_unique'  => 'boolean',
        'clicked_at' => 'datetime',
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeHuman($query)
    {
        return $query->where('is_bot', false);
    }

    public function scopeUnique($query)
    {
        return $query->where('is_unique', true);
    }

    public function scopeForLink($query, int $linkId)
    {
        return $query->where('link_id', $linkId);
    }

    public function scopeExpired($query)
    {
        // Not used directly but satisfies interface consistency
        return $query;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('link', fn ($q) => $q->where('user_id', $userId));
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->whereHas('link', fn ($q) => $q->where('team_id', $teamId));
    }
}
