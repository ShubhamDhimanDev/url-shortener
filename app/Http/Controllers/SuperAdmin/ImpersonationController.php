<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private readonly ImpersonationService $impersonation) {}

    /**
     * Start impersonating the given user.
     *
     * POST /super-admin/impersonate/{user}
     */
    public function impersonate(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403, 'Cannot impersonate another super admin.');
        abort_if(session()->has('impersonator_id'), 422, 'Already impersonating a user. Stop first.');

        $this->impersonation->start(auth()->user(), $user);

        return redirect()->route('app.dashboard')
            ->with('info', "You are now impersonating {$user->name}.");
    }

    /**
     * Stop impersonating and restore the original admin session.
     *
     * DELETE /super-admin/impersonate
     */
    public function stopImpersonating(): RedirectResponse
    {
        abort_unless(session()->has('impersonator_id'), 403, 'No impersonation session active.');

        $this->impersonation->stop();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'Impersonation ended. Welcome back!');
    }
}
