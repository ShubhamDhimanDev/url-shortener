<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject authenticated users whose account has been deactivated (is_active = false).
 * Logs them out and redirects to the suspended page rather than aborting with 403,
 * so they always see a user-friendly explanation.
 */
class CheckUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.suspended');
        }

        return $next($request);
    }
}
