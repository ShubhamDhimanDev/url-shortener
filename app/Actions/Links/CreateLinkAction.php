<?php

namespace App\Actions\Links;

use App\Events\Links\LinkCreated;
use App\Models\Link;
use App\Models\Team;
use App\Models\User;
use App\Services\LinkService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CreateLinkAction
{
    public function __construct(
        private readonly LinkService $linkService,
    ) {}

    /**
     * Create a new shortened link.
     *
     * @param  array{
     *     destination_url: string,
     *     short_code?: string|null,
     *     domain_id?: int|null,
     *     title?: string|null,
     *     description?: string|null,
     *     og_image?: string|null,
     *     password?: string|null,
     *     expires_at?: string|null,
     *     utm_source?: string|null,
     *     utm_medium?: string|null,
     *     utm_campaign?: string|null,
     *     utm_term?: string|null,
     *     utm_content?: string|null,
     *     meta_pixel_id?: string|null,
     *     google_tag_id?: string|null,
     *     is_bot_protection_enabled?: bool,
     *     tag_ids?: int[],
     * } $data
     *
     * @throws \InvalidArgumentException  on URL validation or quota breach
     * @throws \RuntimeException          if short code cannot be generated
     */
    public function execute(User $user, ?Team $team, array $data): Link
    {
        // ── 1. Validate destination URL ──────────────────────────────────────
        $this->linkService->validateDestinationUrl($data['destination_url']);

        // ── 2. Quota check ───────────────────────────────────────────────────
        $this->checkQuota($user, $team);

        // ── 3. Short code ────────────────────────────────────────────────────
        $shortCode = $this->resolveShortCode($data['short_code'] ?? null);

        // ── 4. Custom domain ─────────────────────────────────────────────────
        $domainId = null;
        if (! empty($data['domain_id'])) {
            $domain   = $this->linkService->resolveCustomDomain((int) $data['domain_id'], $user, $team);
            $domainId = $domain->id;
        }

        // ── 5. Password ──────────────────────────────────────────────────────
        $password             = null;
        $isPasswordProtected  = false;
        if (! empty($data['password'])) {
            $password            = Hash::make($data['password']);
            $isPasswordProtected = true;
        }

        // ── 6. Persist ───────────────────────────────────────────────────────
        /** @var Link $link */
        $link = Link::create([
            'user_id'                  => $user->id,
            'team_id'                  => $team?->id,
            'domain_id'                => $domainId,
            'short_code'               => $shortCode,
            'destination_url'          => $data['destination_url'],
            'title'                    => $data['title']                    ?? null,
            'description'              => $data['description']              ?? null,
            'og_image'                 => $data['og_image']                 ?? null,
            'password'                 => $password,
            'is_password_protected'    => $isPasswordProtected,
            'is_active'                => true,
            'expires_at'               => $data['expires_at']               ?? null,
            'utm_source'               => $data['utm_source']               ?? null,
            'utm_medium'               => $data['utm_medium']               ?? null,
            'utm_campaign'             => $data['utm_campaign']             ?? null,
            'utm_term'                 => $data['utm_term']                 ?? null,
            'utm_content'              => $data['utm_content']              ?? null,
            'meta_pixel_id'            => $data['meta_pixel_id']            ?? null,
            'google_tag_id'            => $data['google_tag_id']            ?? null,
            'is_bot_protection_enabled'=> $data['is_bot_protection_enabled'] ?? false,
        ]);

        // ── 7. Attach tags ───────────────────────────────────────────────────
        if (! empty($data['tag_ids'])) {
            $link->tags()->sync($data['tag_ids']);
        }

        // ── 8. Fire event ────────────────────────────────────────────────────
        LinkCreated::dispatch($link);

        return $link;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function checkQuota(User $user, ?Team $team): void
    {
        // Determine the plan's monthly link limit
        $entity = $team ?? $user;

        $subscription = method_exists($entity, 'subscription')
            ? $entity->subscription()
            : null;

        $limit = $subscription?->plan
            ?->features()
            ->where('feature_key', 'links_per_month')
            ->value('feature_value');

        // No limit set (null / 0 / '-1') means unlimited
        if ($limit === null || (int) $limit <= 0) {
            return;
        }

        $used = $this->linkService->monthlyLinkCount($user, $team);

        if ($used >= (int) $limit) {
            throw new \InvalidArgumentException(
                "Monthly link quota of {$limit} has been reached. Please upgrade your plan."
            );
        }
    }

    private function resolveShortCode(?string $requested): string
    {
        if ($requested !== null && $requested !== '') {
            // Validate uniqueness of custom slug
            if (\Illuminate\Support\Facades\DB::table('links')->where('short_code', $requested)->exists()) {
                throw new \InvalidArgumentException("The custom slug '{$requested}' is already taken.");
            }
            return $requested;
        }

        return $this->linkService->generateShortCode();
    }
}
