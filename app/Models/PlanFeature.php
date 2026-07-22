<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_value',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getBoolValue(): bool
    {
        return filter_var($this->feature_value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getIntValue(): int
    {
        return (int) $this->feature_value;
    }

    /**
     * Human-readable label for displaying the feature in plan cards.
     */
    public function getDescriptionAttribute(): string
    {
        $value = $this->feature_value;
        $unlimited = $value === '-1';
        $enabled   = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return match ($this->feature_key) {
            'links_per_month'          => $unlimited ? 'Unlimited links/month' : number_format((int) $value) . ' links/month',
            'custom_domains_count'     => $unlimited ? 'Unlimited custom domains' : ((int) $value === 1 ? '1 custom domain' : number_format((int) $value) . ' custom domains'),
            'domain_whitelist'         => $enabled   ? 'Domain whitelist'        : 'No domain whitelist',
            'custom_subdomain'         => $enabled   ? 'Custom subdomain'        : 'No custom subdomain',
            'meta_tracking'            => $enabled   ? 'Meta pixel tracking'     : 'No meta tracking',
            'google_tracking'          => $enabled   ? 'Google Analytics'        : 'No Google Analytics',
            'analytics_level'          => ucfirst($value) . ' analytics',
            'qrcode'                   => $enabled   ? 'QR code generation'      : 'No QR codes',
            'password_protected_links' => $enabled   ? 'Password-protected links': 'No password protection',
            'geo_metrics'              => $enabled   ? 'Geo metrics'             : 'No geo metrics',
            'bot_detection'            => $enabled   ? 'Bot detection'           : 'No bot detection',
            'spam_detection'           => $enabled   ? 'Spam detection'          : 'No spam detection',
            'team_members_count'       => $unlimited ? 'Unlimited team members'  : ((int) $value === 0 ? 'Personal use only' : number_format((int) $value) . ' team members'),
            'link_expiry'              => $enabled   ? 'Link expiry'             : 'No link expiry',
            'bulk_links'               => $enabled   ? 'Bulk link creation'      : 'No bulk creation',
            'campaign_tracking'        => $enabled   ? 'Campaign tracking'       : 'No campaign tracking',
            default                    => ucwords(str_replace('_', ' ', $this->feature_key)),
        };
    }
}
