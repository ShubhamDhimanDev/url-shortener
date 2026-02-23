<?php

namespace App\Services\Analytics;

use App\Contracts\AnalyticsDriverInterface;
use App\Models\Link;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService implements AnalyticsDriverInterface
{
    /** Cache TTL in seconds (10 minutes). */
    private const CACHE_TTL = 600;

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Per-link summary for a given date range.
     *
     * Cached for 10 min under tags ['analytics', 'link:{id}'].
     * The tag 'link:{id}' is flushed by clearLinkCache() which is called from
     * RecordClickAction after every new click.
     */
    public function getSummary(Link $link, Carbon $from, Carbon $to): array
    {
        $key = "summary:link:{$link->id}:{$from->timestamp}:{$to->timestamp}";

        return Cache::tags(['analytics', "link:{$link->id}"])
            ->remember($key, self::CACHE_TTL, fn () => $this->buildLinkSummary($link->id, $from, $to));
    }

    /**
     * Aggregated summary across all links that belong to a team.
     */
    public function getTeamSummary(Team $team, Carbon $from, Carbon $to): array
    {
        $key = "summary:team:{$team->id}:{$from->timestamp}:{$to->timestamp}";

        return Cache::tags(['analytics', "team:{$team->id}"])
            ->remember($key, self::CACHE_TTL, fn () => $this->buildTeamSummary($team->id, $from, $to));
    }

    /**
     * Aggregated summary across all links that belong to a user (personal links).
     */
    public function getUserSummary(User $user, Carbon $from, Carbon $to): array
    {
        $key = "summary:user:{$user->id}:{$from->timestamp}:{$to->timestamp}";

        return Cache::tags(['analytics', "user:{$user->id}"])
            ->remember($key, self::CACHE_TTL, fn () => $this->buildUserSummary($user->id, $from, $to));
    }

    /**
     * Platform-wide aggregation (super admin only).
     */
    public function getPlatformSummary(Carbon $from, Carbon $to): array
    {
        $key = "summary:platform:{$from->timestamp}:{$to->timestamp}";

        return Cache::tags(['analytics', 'platform'])
            ->remember($key, self::CACHE_TTL, fn () => $this->buildPlatformSummary($from, $to));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cache invalidation helpers
    // ──────────────────────────────────────────────────────────────────────────

    public function clearLinkCache(int|Link $link): void
    {
        $id = $link instanceof Link ? $link->id : $link;
        Cache::tags(["link:{$id}"])->flush();
    }

    public function clearTeamCache(int|Team $team): void
    {
        $id = $team instanceof Team ? $team->id : $team;
        Cache::tags(["team:{$id}"])->flush();
    }

    public function clearUserCache(int|User $user): void
    {
        $id = $user instanceof User ? $user->id : $user;
        Cache::tags(["user:{$id}"])->flush();
    }

    public function clearPlatformCache(): void
    {
        Cache::tags(['platform'])->flush();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal builders — raw MySQL for performance
    // ──────────────────────────────────────────────────────────────────────────

    private function buildLinkSummary(int $linkId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();
        $base    = [$linkId, $fromStr, $toStr];

        // ── Totals ───────────────────────────────────────────────────────────
        $totals = DB::selectOne(
            'SELECT
                COUNT(*)              AS total_clicks,
                SUM(is_unique)        AS unique_clicks
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0',
            $base
        );

        // ── Top countries ────────────────────────────────────────────────────
        $topCountries = DB::select(
            'SELECT country, country_code, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND country IS NOT NULL
             GROUP BY country, country_code
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Top referrers ────────────────────────────────────────────────────
        $topReferrers = DB::select(
            'SELECT COALESCE(referrer_domain, \'(direct)\') AS referrer_domain,
                    COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY referrer_domain
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Device breakdown ─────────────────────────────────────────────────
        $topDevices = DB::select(
            'SELECT device_type, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY device_type
             ORDER BY count DESC',
            $base
        );

        // ── Top browsers ─────────────────────────────────────────────────────
        $topBrowsers = DB::select(
            'SELECT browser, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND browser IS NOT NULL
             GROUP BY browser
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Top operating systems ────────────────────────────────────────────
        $topOs = DB::select(
            'SELECT os, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND os IS NOT NULL
             GROUP BY os
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Clicks over time (daily buckets) ─────────────────────────────────
        $clicksOverTime = DB::select(
            'SELECT DATE(clicked_at) AS date, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY DATE(clicked_at)
             ORDER BY date ASC',
            $base
        );

        // ── UTM campaigns ─────────────────────────────────────────────────────
        $topCampaigns = DB::select(
            'SELECT utm_source, utm_medium, utm_campaign, COUNT(*) AS count
             FROM link_clicks
             WHERE link_id = ?
               AND clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND utm_campaign IS NOT NULL
             GROUP BY utm_source, utm_medium, utm_campaign
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        return [
            'total_clicks'     => (int) ($totals->total_clicks  ?? 0),
            'unique_clicks'    => (int) ($totals->unique_clicks ?? 0),
            'top_countries'    => $this->toArray($topCountries),
            'top_referrers'    => $this->toArray($topReferrers),
            'top_devices'      => $this->toArray($topDevices),
            'top_browsers'     => $this->toArray($topBrowsers),
            'top_os'           => $this->toArray($topOs),
            'clicks_over_time' => $this->toArray($clicksOverTime),
            'top_campaigns'    => $this->toArray($topCampaigns),
        ];
    }

    private function buildTeamSummary(int $teamId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();
        $base    = [$teamId, $fromStr, $toStr];

        $totals = DB::selectOne(
            'SELECT
                COUNT(*)          AS total_clicks,
                SUM(lc.is_unique) AS unique_clicks
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0',
            $base
        );

        $topLinks = DB::select(
            'SELECT l.id, l.ulid, l.short_code, l.title, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY l.id, l.ulid, l.short_code, l.title
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topCountries = DB::select(
            'SELECT lc.country, lc.country_code, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.country IS NOT NULL
             GROUP BY lc.country, lc.country_code
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topDevices = DB::select(
            'SELECT lc.device_type, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY lc.device_type
             ORDER BY count DESC',
            $base
        );

        $clicksOverTime = DB::select(
            'SELECT DATE(lc.clicked_at) AS date, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY DATE(lc.clicked_at)
             ORDER BY date ASC',
            $base
        );

        $topBrowsers = DB::select(
            'SELECT lc.browser, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.browser IS NOT NULL
             GROUP BY lc.browser
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topOs = DB::select(
            'SELECT lc.os, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.team_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.os IS NOT NULL
             GROUP BY lc.os
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        return [
            'total_clicks'     => (int) ($totals->total_clicks  ?? 0),
            'unique_clicks'    => (int) ($totals->unique_clicks ?? 0),
            'top_links'        => $this->toArray($topLinks),
            'top_countries'    => $this->toArray($topCountries),
            'top_devices'      => $this->toArray($topDevices),
            'top_browsers'     => $this->toArray($topBrowsers),
            'top_os'           => $this->toArray($topOs),
            'clicks_over_time' => $this->toArray($clicksOverTime),
        ];
    }

    private function buildUserSummary(int $userId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();
        $base    = [$userId, $fromStr, $toStr];

        $totals = DB::selectOne(
            'SELECT
                COUNT(*)          AS total_clicks,
                SUM(lc.is_unique) AS unique_clicks
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0',
            $base
        );

        $topLinks = DB::select(
            'SELECT l.id, l.ulid, l.short_code, l.title, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY l.id, l.ulid, l.short_code, l.title
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topCountries = DB::select(
            'SELECT lc.country, lc.country_code, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.country IS NOT NULL
             GROUP BY lc.country, lc.country_code
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topDevices = DB::select(
            'SELECT lc.device_type, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY lc.device_type
             ORDER BY count DESC',
            $base
        );

        $topBrowsers = DB::select(
            'SELECT lc.browser, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.browser IS NOT NULL
             GROUP BY lc.browser
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topOs = DB::select(
            'SELECT lc.os, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
               AND lc.os IS NOT NULL
             GROUP BY lc.os
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $topReferrers = DB::select(
            'SELECT COALESCE(lc.referrer_domain, \'(direct)\') AS referrer_domain,
                    COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY lc.referrer_domain
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        $clicksOverTime = DB::select(
            'SELECT DATE(lc.clicked_at) AS date, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE l.user_id = ?
               AND lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY DATE(lc.clicked_at)
             ORDER BY date ASC',
            $base
        );

        return [
            'total_clicks'     => (int) ($totals->total_clicks  ?? 0),
            'unique_clicks'    => (int) ($totals->unique_clicks ?? 0),
            'top_links'        => $this->toArray($topLinks),
            'top_countries'    => $this->toArray($topCountries),
            'top_devices'      => $this->toArray($topDevices),
            'top_browsers'     => $this->toArray($topBrowsers),
            'top_os'           => $this->toArray($topOs),
            'top_referrers'    => $this->toArray($topReferrers),
            'clicks_over_time' => $this->toArray($clicksOverTime),
        ];
    }

    private function buildPlatformSummary(Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();
        $base    = [$fromStr, $toStr];

        // ── Overall totals ───────────────────────────────────────────────────
        $totals = DB::selectOne(
            'SELECT
                COUNT(*)       AS total_clicks,
                SUM(is_unique) AS unique_clicks
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0',
            $base
        );

        // ── New links created in period ───────────────────────────────────────
        $newLinks = DB::selectOne(
            'SELECT COUNT(*) AS count
             FROM links
             WHERE created_at BETWEEN ? AND ?
               AND deleted_at IS NULL',
            $base
        );

        // ── New users registered in period ───────────────────────────────────
        $newUsers = DB::selectOne(
            'SELECT COUNT(*) AS count
             FROM users
             WHERE created_at BETWEEN ? AND ?
               AND deleted_at IS NULL',
            $base
        );

        // ── Top performing links ──────────────────────────────────────────────
        $topLinks = DB::select(
            'SELECT l.id, l.ulid, l.short_code, l.title, COUNT(*) AS count
             FROM link_clicks lc
             INNER JOIN links l ON l.id = lc.link_id
             WHERE lc.clicked_at BETWEEN ? AND ?
               AND lc.is_bot = 0
             GROUP BY l.id, l.ulid, l.short_code, l.title
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Top countries ─────────────────────────────────────────────────────
        $topCountries = DB::select(
            'SELECT country, country_code, COUNT(*) AS count
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND country IS NOT NULL
             GROUP BY country, country_code
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Device breakdown ──────────────────────────────────────────────────
        $deviceBreakdown = DB::select(
            'SELECT device_type, COUNT(*) AS count
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY device_type
             ORDER BY count DESC',
            $base
        );

        // ── Bot vs. human ratio ───────────────────────────────────────────────
        $botStats = DB::selectOne(
            'SELECT
                SUM(is_bot = 0)  AS human_clicks,
                SUM(is_bot = 1)  AS bot_clicks
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?',
            $base
        );

        // ── Clicks over time ──────────────────────────────────────────────────
        $clicksOverTime = DB::select(
            'SELECT DATE(clicked_at) AS date, COUNT(*) AS count
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY DATE(clicked_at)
             ORDER BY date ASC',
            $base
        );

        // ── Top referrer domains ───────────────────────────────────────────────
        $topReferrers = DB::select(
            'SELECT COALESCE(referrer_domain, \'(direct)\') AS referrer_domain,
                    COUNT(*) AS count
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0
             GROUP BY referrer_domain
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Top browsers ──────────────────────────────────────────────────────
        $topBrowsers = DB::select(
            'SELECT browser, COUNT(*) AS count
             FROM link_clicks
             WHERE clicked_at BETWEEN ? AND ?
               AND is_bot = 0
               AND browser IS NOT NULL
             GROUP BY browser
             ORDER BY count DESC
             LIMIT 10',
            $base
        );

        // ── Active subscriptions by plan ──────────────────────────────────────
        $subscriptionsByPlan = DB::select(
            'SELECT p.name AS plan_name, p.slug, COUNT(*) AS count
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.status IN (\'active\', \'trialing\')
               AND s.deleted_at IS NULL
             GROUP BY p.id, p.name, p.slug
             ORDER BY count DESC',
            []
        );

        return [
            'total_clicks'          => (int) ($totals->total_clicks  ?? 0),
            'unique_clicks'         => (int) ($totals->unique_clicks ?? 0),
            'new_links'             => (int) ($newLinks->count       ?? 0),
            'new_users'             => (int) ($newUsers->count       ?? 0),
            'human_clicks'          => (int) ($botStats->human_clicks ?? 0),
            'bot_clicks'            => (int) ($botStats->bot_clicks   ?? 0),
            'top_links'             => $this->toArray($topLinks),
            'top_countries'         => $this->toArray($topCountries),
            'top_referrers'         => $this->toArray($topReferrers),
            'top_browsers'          => $this->toArray($topBrowsers),
            'device_breakdown'      => $this->toArray($deviceBreakdown),
            'clicks_over_time'      => $this->toArray($clicksOverTime),
            'subscriptions_by_plan' => $this->toArray($subscriptionsByPlan),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Convert an array of stdClass DB results to plain arrays.
     */
    private function toArray(array $rows): array
    {
        return array_map(static fn ($row) => (array) $row, $rows);
    }
}
