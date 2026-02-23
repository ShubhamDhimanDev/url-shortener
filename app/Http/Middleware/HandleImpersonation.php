<?php

namespace App\Http\Middleware;

use App\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects impersonation state into every Blade view so the banner
 * component can display when an admin is impersonating a user.
 */
class HandleImpersonation
{
    public function __construct(private readonly ImpersonationService $impersonation) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->impersonation->active()) {
            $impersonator = $this->impersonation->impersonator();

            // Share with ALL views via View::share so every layout can render the banner.
            View::share('impersonating', true);
            View::share('impersonator', $impersonator);
        } else {
            View::share('impersonating', false);
            View::share('impersonator', null);
        }

        return $next($request);
    }
}
