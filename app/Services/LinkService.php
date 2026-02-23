<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Link;
use App\Models\Plan;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LinkService
{
    private const PRIVATE_IP_PATTERNS = [
        '/^127\./',                         // loopback
        '/^10\./',                          // RFC 1918
        '/^192\.168\./',                    // RFC 1918
        '/^172\.(1[6-9]|2\d|3[01])\./',    // RFC 1918
        '/^::1$/',                          // IPv6 loopback
        '/^fc00:/i',                        // IPv6 ULA
    ];

    private const BLOCKED_HOSTS = [
        'localhost',
        '0.0.0.0',
    ];

    /**
     * Validate a destination URL — blocks private IPs, localhost and blank.
     *
     * @throws \InvalidArgumentException
     */
    public function validateDestinationUrl(string $url): void
    {
        $parsed = parse_url($url);

        if (! $parsed || empty($parsed['host'])) {
            throw new \InvalidArgumentException('Invalid destination URL.');
        }

        $host = strtolower($parsed['host']);

        if (in_array($host, self::BLOCKED_HOSTS, true)) {
            throw new \InvalidArgumentException("Destination '{$host}' is not allowed.");
        }

        foreach (self::PRIVATE_IP_PATTERNS as $pattern) {
            if (preg_match($pattern, $host)) {
                throw new \InvalidArgumentException('Destination points to a private network address.');
            }
        }
    }

    /**
     * Generate a unique base-62 short code of the given length.
     * Retries on DB collision up to $maxAttempts times.
     *
     * @throws \RuntimeException
     */
    public function generateShortCode(int $length = 6, int $maxAttempts = 10): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $alphabet[random_int(0, 61)];
            }

            if (! DB::table('links')->where('short_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Could not generate a unique short code after ' . $maxAttempts . ' attempts.');
    }

    /**
     * Count how many links a user/team has created in the current calendar month.
     */
    public function monthlyLinkCount(User $user, ?Team $team = null): int
    {
        $query = DB::table('links')
            ->whereNull('deleted_at')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        if ($team) {
            $query->where('team_id', $team->id);
        } else {
            $query->where('user_id', $user->id)->whereNull('team_id');
        }

        return (int) $query->count();
    }

    /**
     * Resolve and validate a custom domain against the requesting user/team.
     *
     * @throws \InvalidArgumentException
     */
    public function resolveCustomDomain(int $domainId, User $user, ?Team $team = null): Domain
    {
        $query = Domain::query()->where('id', $domainId)->where('is_verified', true)->where('is_active', true);

        if ($team) {
            $query->where('team_id', $team->id);
        } else {
            $query->where('user_id', $user->id);
        }

        $domain = $query->first();

        if (! $domain) {
            throw new \InvalidArgumentException('The selected domain is not verified or does not belong to you.');
        }

        return $domain;
    }
}
