<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpamDetectionService
{
    /** Clicks from a single IP per link within this window triggers flagging */
    private const VELOCITY_WINDOW_MINUTES = 60;
    private const VELOCITY_THRESHOLD      = 500;

    /**
     * Inspect a link after a new click and flag it as spam if thresholds are met.
     * This is called inside the queued RecordClickAction — non-blocking.
     */
    public function inspect(Link $link, string $ipAddress): void
    {
        // 1. Destination URL blocklist check
        if ($this->isBlocklistedDestination($link->destination_url)) {
            $this->flagLink($link, 'blocklist');
            return;
        }

        // 2. Click velocity per IP
        if ($this->exceedsVelocityThreshold($link->id, $ipAddress)) {
            $this->flagLink($link, 'velocity');
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function isBlocklistedDestination(string $url): bool
    {
        $raw = Cache::rememberForever('setting:spam.blocklist', fn () =>
            Setting::query()->where('group', 'spam')->where('key', 'blocklist')->value('value')
        );

        if (! $raw) {
            return false;
        }

        $domains = array_filter(array_map('trim', explode("\n", $raw)));

        $host = parse_url(strtolower($url), PHP_URL_HOST) ?? '';

        foreach ($domains as $blocked) {
            if (str_ends_with($host, strtolower($blocked))) {
                return true;
            }
        }

        return false;
    }

    private function exceedsVelocityThreshold(int $linkId, string $ipAddress): bool
    {
        $count = DB::table('link_clicks')
            ->where('link_id', $linkId)
            ->where('ip_address', $ipAddress)
            ->where('clicked_at', '>=', now()->subMinutes(self::VELOCITY_WINDOW_MINUTES))
            ->count();

        return $count >= self::VELOCITY_THRESHOLD;
    }

    private function flagLink(Link $link, string $reason): void
    {
        if ($link->is_spam_detected) {
            return; // already flagged
        }

        $link->updateQuietly(['is_spam_detected' => true]);

        Log::notice('Link flagged as spam', [
            'link_id'    => $link->id,
            'short_code' => $link->short_code,
            'reason'     => $reason,
        ]);
    }
}
