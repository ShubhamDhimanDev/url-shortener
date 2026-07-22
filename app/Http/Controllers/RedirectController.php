<?php

namespace App\Http\Controllers;

use App\Jobs\RecordLinkClickJob;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RedirectController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    // ─────────────────────────────────────────────────────────────────────────
    // Public redirect  GET /{shortCode}
    // ─────────────────────────────────────────────────────────────────────────

    public function redirect(Request $request, string $shortCode): \Symfony\Component\HttpFoundation\Response
    {
        /** @var Link|null $link */
        $link = Cache::remember(
            "link:short:{$shortCode}",
            self::CACHE_TTL,
            fn () => Link::with('domain')
                ->where('short_code', $shortCode)
                ->whereNull('deleted_at')
                ->first()
        );

        // 404 — link not found or soft-deleted
        if (! $link) {
            abort(404);
        }

        // 410 Gone — link deactivated
        if (! $link->is_active) {
            abort(410, 'This link has been deactivated.');
        }

        // 410 Gone — link expired
        if ($link->is_expired) {
            abort(410, 'This link has expired.');
        }

        // 403 — password-protected: show unlock form
        if ($link->is_password_protected) {
            return $this->renderPasswordForm($link);
        }

        // Dispatch async click recording — never blocks the redirect
        $this->dispatchClickJob($request, $link);

        // Build the redirect response, optionally injecting tracking snippets
        return $this->buildRedirectResponse($link);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Password unlock  POST /{shortCode}/unlock
    // ─────────────────────────────────────────────────────────────────────────

    public function unlock(Request $request, string $shortCode): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate(['password' => ['required', 'string']]);

        /** @var Link|null $link */
        $link = Cache::remember(
            "link:short:{$shortCode}",
            self::CACHE_TTL,
            fn () => Link::where('short_code', $shortCode)
                ->whereNull('deleted_at')
                ->first()
        );

        if (! $link || ! $link->is_active || $link->is_expired) {
            abort(404);
        }

        if (! Hash::check($request->input('password'), $link->password)) {
            return back()->withErrors(['password' => 'Incorrect password. Please try again.']);
        }

        // Store unlocked state in session for this link
        $request->session()->put("link_unlocked:{$link->id}", true);

        $this->dispatchClickJob($request, $link);

        return $this->buildRedirectResponse($link, 302);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildRedirectResponse(Link $link, int $statusCode = 301): \Symfony\Component\HttpFoundation\Response
    {
        $destination = $this->appendUtmParams($link);

        // If the link has meta pixel or Google tag enabled (and plan supports it),
        // we render a tiny HTML interstitial that fires the trackers before
        // performing a JS redirect — this avoids losing the pixel fire on 301.
        if ($link->meta_pixel_id || $link->google_tag_id) {
            return response($this->buildTrackingHtml($link, $destination), 200)
                ->header('Content-Type', 'text/html');
        }

        return redirect()->away($destination, $statusCode);
    }

    private function appendUtmParams(Link $link): string
    {
        $url = $link->destination_url;

        $utms = array_filter([
            'utm_source'   => $link->utm_source,
            'utm_medium'   => $link->utm_medium,
            'utm_campaign' => $link->utm_campaign,
            'utm_term'     => $link->utm_term,
            'utm_content'  => $link->utm_content,
        ]);

        if (empty($utms)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($utms);
    }

    private function buildTrackingHtml(Link $link, string $destination): string
    {
        $metaScript   = '';
        $googleScript = '';
        $dest         = htmlspecialchars($destination, ENT_QUOTES, 'UTF-8');

        if ($link->meta_pixel_id) {
            $pixelId    = htmlspecialchars($link->meta_pixel_id, ENT_QUOTES, 'UTF-8');
            $metaScript = <<<HTML
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');fbq('track', 'PageView');
</script>
HTML;
        }

        if ($link->google_tag_id) {
            $tagId        = htmlspecialchars($link->google_tag_id, ENT_QUOTES, 'UTF-8');
            $googleScript = <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$tagId}"></script>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());gtag('config','{$tagId}');
</script>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="0;url={$dest}">
{$metaScript}
{$googleScript}
</head>
<body>
<script>window.location.replace("{$dest}");</script>
<p>Redirecting… <a href="{$dest}">Click here</a> if not redirected automatically.</p>
</body>
</html>
HTML;
    }

    private function renderPasswordForm(Link $link): Response
    {
        return response()->view('redirect.password', ['link' => $link], 200);
    }

    private function dispatchClickJob(Request $request, Link $link): void
    {
        $honeypotHeaderName = config('bots.honeypot_header', 'X-Shortener-Bot');

        $utmParams = $request->only([
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        ]);

        RecordLinkClickJob::dispatch(
            link:           $link,
            ipAddress:      $request->ip(),
            userAgent:      $request->userAgent() ?? '',
            referrerUrl:    $request->headers->get('referer'),
            utmParams:      $utmParams,
            honeypotHeader: $request->headers->get($honeypotHeaderName),
        );
    }
}
