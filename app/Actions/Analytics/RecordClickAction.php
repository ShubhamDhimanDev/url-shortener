<?php

namespace App\Actions\Analytics;

use App\Models\Link;
use App\Models\LinkClick;
use App\Services\Analytics\BotDetectionService;
use App\Services\Analytics\GeoLocationService;
use App\Services\SpamDetectionService;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class RecordClickAction
{
    public function __construct(
        private readonly BotDetectionService  $botDetector,
        private readonly GeoLocationService   $geo,
        private readonly SpamDetectionService $spam,
    ) {}

    /**
     * Parse, enrich and persist a single link click.
     * Called exclusively from RecordLinkClickJob (queued, 'analytics' queue).
     */
    public function execute(
        Link    $link,
        string  $ipAddress,
        string  $userAgent,
        ?string $referrerUrl,
        array   $utmParams        = [],
        ?string $honeypotHeader   = null,
    ): void {
        // ── 1. Bot detection ────────────────────────────────────────────────
        $botResult = $this->botDetector->detect($userAgent, $honeypotHeader);
        $isBot     = $botResult['is_bot'];
        $botName   = $botResult['bot_name'];

        // ── 2. Skip if bot-protection is ON for this link and it IS a bot ──
        if ($link->is_bot_protection_enabled && $isBot) {
            return;
        }

        // ── 3. UA parsing ───────────────────────────────────────────────────
        ['device' => $device, 'os' => $os, 'osVersion' => $osVersion,
         'browser' => $browser, 'browserVersion' => $browserVersion]
            = $this->parseUserAgent($userAgent);

        // ── 4. Geo lookup ───────────────────────────────────────────────────
        $geoData = $this->geo->lookup($ipAddress);

        // ── 5. Unique detection via session hash ────────────────────────────
        $today       = now()->format('Y-m-d');
        $sessionHash = hash('sha256', $ipAddress . $userAgent . $today);

        $isUnique = ! LinkClick::where('link_id', $link->id)
            ->where('session_hash', $sessionHash)
            ->whereDate('clicked_at', $today)
            ->exists();

        // ── 6. Referrer ──────────────────────────────────────────────────────
        $referrerDomain = null;
        if ($referrerUrl) {
            $referrerDomain = parse_url($referrerUrl, PHP_URL_HOST) ?: null;
        }

        // ── 7. Persist the click ─────────────────────────────────────────────
        LinkClick::create([
            'link_id'          => $link->id,
            'session_hash'     => $sessionHash,
            'ip_address'       => $ipAddress,
            'country'          => $geoData['country']      ?? null,
            'country_code'     => $geoData['country_code'] ?? null,
            'region'           => $geoData['region']       ?? null,
            'city'             => $geoData['city']         ?? null,
            'latitude'         => $geoData['latitude']     ?? null,
            'longitude'        => $geoData['longitude']    ?? null,
            'device_type'      => $isBot ? 'bot' : $device,
            'os'               => $os,
            'os_version'       => $osVersion,
            'browser'          => $browser,
            'browser_version'  => $browserVersion,
            'referrer_url'     => $referrerUrl,
            'referrer_domain'  => $referrerDomain,
            'utm_source'       => $utmParams['utm_source']   ?? null,
            'utm_medium'       => $utmParams['utm_medium']   ?? null,
            'utm_campaign'     => $utmParams['utm_campaign'] ?? null,
            'utm_term'         => $utmParams['utm_term']     ?? null,
            'utm_content'      => $utmParams['utm_content']  ?? null,
            'is_bot'           => $isBot,
            'bot_name'         => $botName,
            'is_unique'        => $isUnique,
            'clicked_at'       => now(),
        ]);

        // ── 8. Increment denormalised counters ───────────────────────────────
        DB::table('links')->where('id', $link->id)->increment('clicks_count');

        if ($isUnique) {
            DB::table('links')->where('id', $link->id)->increment('unique_clicks_count');
        }

        // ── 9. Spam detection ────────────────────────────────────────────────
        if (! $isBot) {
            $this->spam->inspect($link, $ipAddress);
        }
    }

    // ─── UA Parsing ──────────────────────────────────────────────────────────

    /**
     * Parse a UA string into device/browser/OS metadata.
     * Uses simple heuristics without requiring the jenssegers/agent package.
     */
    private function parseUserAgent(string $ua): array
    {
        $ua = $ua ?: '';

        // Device type
        $device = 'desktop';
        $uaLower = strtolower($ua);
        if (str_contains($uaLower, 'mobile') || str_contains($uaLower, 'android') && str_contains($uaLower, 'mobile')) {
            $device = 'mobile';
        } elseif (str_contains($uaLower, 'tablet') || str_contains($uaLower, 'ipad')) {
            $device = 'tablet';
        }

        // OS detection
        $os        = 'Unknown';
        $osVersion = null;

        if (preg_match('/Windows NT ([\d.]+)/i', $ua, $m)) {
            $os        = 'Windows';
            $osVersion = $this->windowsVersion($m[1]);
        } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) {
            $os        = 'macOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android ([\d.]+)/i', $ua, $m)) {
            $os        = 'Android';
            $osVersion = $m[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) {
            $os        = 'iOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (str_contains($uaLower, 'linux')) {
            $os = 'Linux';
        }

        // Browser detection
        $browser        = 'Unknown';
        $browserVersion = null;

        if (preg_match('/Edg\/([\d.]+)/i', $ua, $m)) {
            $browser        = 'Edge';
            $browserVersion = $m[1];
        } elseif (preg_match('/OPR\/([\d.]+)/i', $ua, $m)) {
            $browser        = 'Opera';
            $browserVersion = $m[1];
        } elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $m) && ! str_contains($uaLower, 'chromium')) {
            $browser        = 'Chrome';
            $browserVersion = $m[1];
        } elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $m)) {
            $browser        = 'Firefox';
            $browserVersion = $m[1];
        } elseif (preg_match('/Safari\/([\d.]+)/i', $ua, $m) && ! str_contains($uaLower, 'chrome')) {
            $browser        = 'Safari';
            $browserVersion = $m[1];
        }

        return compact('device', 'os', 'osVersion', 'browser', 'browserVersion');
    }

    private function windowsVersion(string $nt): string
    {
        return match ($nt) {
            '10.0' => '10/11',
            '6.3'  => '8.1',
            '6.2'  => '8',
            '6.1'  => '7',
            default => $nt,
        };
    }
}
