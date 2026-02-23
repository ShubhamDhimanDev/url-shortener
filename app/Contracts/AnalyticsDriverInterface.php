<?php

namespace App\Contracts;

use App\Models\Link;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

interface AnalyticsDriverInterface
{
    /**
     * Aggregate click statistics for a single link over a date range.
     *
     * @return array{
     *   total_clicks: int,
     *   unique_clicks: int,
     *   top_countries: array,
     *   top_referrers: array,
     *   top_devices: array,
     *   clicks_over_time: array,
     *   top_browsers: array,
     *   top_os: array
     * }
     */
    public function getSummary(Link $link, Carbon $from, Carbon $to): array;

    /**
     * Aggregate click statistics for all links belonging to a team.
     */
    public function getTeamSummary(Team $team, Carbon $from, Carbon $to): array;

    /**
     * Aggregate click statistics for all links belonging to a user.
     */
    public function getUserSummary(User $user, Carbon $from, Carbon $to): array;

    /**
     * Platform-wide aggregation (super admin only).
     */
    public function getPlatformSummary(Carbon $from, Carbon $to): array;

    /**
     * Bust all cached analytics for a given link.
     */
    public function clearLinkCache(int|Link $link): void;

    /**
     * Bust all cached analytics for a given team.
     */
    public function clearTeamCache(int|Team $team): void;

    /**
     * Bust all cached analytics for a given user.
     */
    public function clearUserCache(int|User $user): void;
}
