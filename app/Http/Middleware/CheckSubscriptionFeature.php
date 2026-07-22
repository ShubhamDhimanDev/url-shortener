<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate a route behind a plan feature flag.
 *
 * Usage in routes:
 *   Route::get('/qrcodes', ...)->middleware('feature:qrcode');
 *   Route::get('/analytics', ...)->middleware('feature:analytics_level');
 *
 * The middleware resolves the entity (team context or logged-in user),
 * builds a FeatureService, and checks whether the feature is enabled.
 * Super admins always pass through.
 */
class CheckSubscriptionFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $featureKey  Plan feature key to check (passed via middleware param).
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        // Super admins bypass all feature gates.
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $entity = $this->resolveEntity($request, $user);

        $allowed = FeatureService::for($entity)->can($featureKey);

        if (! $allowed) {
            $message = "Your current plan does not include '{$featureKey}'. Please upgrade to access this feature.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => $message,
                    'feature'     => $featureKey,
                    'upgrade_url' => $this->upgradeUrl(),
                ], Response::HTTP_PAYMENT_REQUIRED);
            }

            return redirect()
                ->back()
                ->withErrors(['feature' => $message])
                ->with('upgrade_required', $featureKey);
        }

        return $next($request);
    }

    /**
     * Resolve the subscribable entity: prefer the team context when a
     * `team_id` is present in the request or bound as a route model.
     */
    private function resolveEntity(Request $request, $user): \App\Models\User|Team
    {
        $teamId = $request->input('team_id')
            ?? $request->route('team_id')
            ?? optional($request->route('team'))->id;

        if ($teamId) {
            $team = $user->teamMemberships()
                ->with('team')
                ->whereHas('team', fn ($q) => $q->where('id', $teamId)->where('is_active', true))
                ->first()
                ?->team;

            if ($team) {
                return $team;
            }
        }

        return $user;
    }

    /**
     * Return the billing/plans upgrade URL, falling back to a static path
     * if the named route does not yet exist.
     */
    private function upgradeUrl(): ?string
    {
        try {
            return route('app.billing.plans');
        } catch (\Throwable) {
            return url('/app/billing/plans');
        }
    }
}
