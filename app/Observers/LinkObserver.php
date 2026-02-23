<?php

namespace App\Observers;

use App\Events\Links\LinkCreated;
use App\Events\Links\LinkDeleted;
use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Support\Facades\Cache;

/**
 * Observe Eloquent lifecycle hooks on the Link model.
 *
 * Responsibilities
 * - creating : generate short_code (fallback) + sanitize destination_url
 * - created  : fire LinkCreated event
 * - updated  : bust analytics cache so stale data is never served
 * - deleted  : bust cache + fire LinkDeleted event
 *
 * NOTE: CreateLinkAction already handles short_code generation and URL
 * validation through the dedicated LinkService.  The `creating` hook below
 * acts as a safety net for any direct model creation that bypasses the Action
 * (e.g. factories, seeders, scripts).
 */
class LinkObserver
{
    public function __construct(private readonly LinkService $linkService) {}

    // ─── creating ────────────────────────────────────────────────────────────

    /**
     * Before inserting: ensure every link has a short_code and a clean URL.
     */
    public function creating(Link $link): void
    {
        // Generate short_code only when not already provided.
        if (empty($link->short_code)) {
            $link->short_code = $this->linkService->generateShortCode();
        }

        // Normalise destination URL: trim whitespace and ensure a scheme.
        if (! empty($link->destination_url)) {
            $url = trim($link->destination_url);

            // Prepend https:// if the URL has no scheme at all.
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://' . $url;
            }

            $link->destination_url = $url;
        }
    }

    // ─── created ─────────────────────────────────────────────────────────────

    /**
     * After insert: broadcast the LinkCreated domain event.
     *
     * Listeners (e.g. SendLinkCreatedNotification) are queued so this never
     * blocks the HTTP response.
     */
    public function created(Link $link): void
    {
        LinkCreated::dispatch($link);
    }

    // ─── updated ─────────────────────────────────────────────────────────────

    /**
     * After update: clear any cached analytics and redirect-resolution data
     * so consumers always see fresh results.
     */
    public function updated(Link $link): void
    {
        $this->clearLinkCache($link);
    }

    // ─── deleted ─────────────────────────────────────────────────────────────

    /**
     * After soft-delete: clear caches and fire LinkDeleted so downstream
     * systems (e.g. cache invalidation, audit logs) can react asynchronously.
     */
    public function deleted(Link $link): void
    {
        $this->clearLinkCache($link);

        LinkDeleted::dispatch($link);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Bust all cache keys associated with this link.
     *
     * Keys cleared:
     *  - redirect resolution cache  : "link:short_code:{$code}"   (5 min TTL, set by RedirectController)
     *  - per-link analytics cache   : tagged  ['analytics', "link:{$id}"]  (set by AnalyticsService)
     */
    private function clearLinkCache(Link $link): void
    {
        // 1. Redirect-resolution cache (plain key — no tagging required).
        Cache::forget("link:short_code:{$link->short_code}");

        // 2. Analytics tagged cache (requires a tag-compatible driver such as Redis or Memcached).
        //    Silently swallow the exception when the driver does not support tags
        //    (e.g. the default "file" driver in development).
        try {
            Cache::tags(['analytics', "link:{$link->id}"])->flush();
        } catch (\BadMethodCallException) {
            // Tag-based cache not supported on current driver — no-op.
        }
    }
}
