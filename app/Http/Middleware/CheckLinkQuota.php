<?php

namespace App\Http\Middleware;

use App\Services\LinkService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLinkQuota
{
    public function __construct(
        private readonly LinkService $linkService
    ) {}

    /**
     * Abort with 402 Payment Required when the user's/team's monthly link
     * quota has been exhausted.
     *
     * Applied to any route that creates a new link.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Determine the active team context (if applicable)
        $team = null;
        if ($request->has('team_id')) {
            $team = $user->teamMemberships()
                ->with('team')
                ->whereHas('team', fn ($q) => $q->where('id', $request->input('team_id')))
                ->first()
                ?->team;
        }

        $entity       = $team ?? $user;
        $subscription = method_exists($entity, 'activeSubscription') ? $entity->activeSubscription() : null;

        $limit = $subscription?->plan
            ?->features()
            ->where('feature_key', 'links_per_month')
            ->value('feature_value');

        // Unlimited (0 or null means no cap)
        if ($limit === null || (int) $limit <= 0) {
            return $next($request);
        }

        $used = $this->linkService->monthlyLinkCount($user, $team);

        if ($used >= (int) $limit) {
            $message = "You have reached your monthly link limit of {$limit}. Please upgrade your plan.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_PAYMENT_REQUIRED);
            }

            abort(Response::HTTP_PAYMENT_REQUIRED, $message);
        }

        return $next($request);
    }
}
