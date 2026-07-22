<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Manages the admin "impersonate as user" session.
 */
class ImpersonationService
{
    private const SESSION_KEY = 'impersonator_id';

    /**
     * Start impersonating $target as $admin.
     */
    public function start(User $admin, User $target): void
    {
        // Persist who the original admin is.
        Session::put(self::SESSION_KEY, $admin->id);

        // Mark the target as being impersonated.
        $target->forceFill(['impersonated_by' => $admin->id])->save();

        // Switch the auth session to the target user.
        Auth::loginUsingId($target->id);
    }

    /**
     * Stop impersonation and restore the original admin.
     */
    public function stop(): void
    {
        $adminId = Session::pull(self::SESSION_KEY);

        if (! $adminId) {
            return;
        }

        // Clear the impersonated_by flag on the current (target) user.
        if ($current = Auth::user()) {
            $current->forceFill(['impersonated_by' => null])->save();
        }

        // Restore the admin.
        Auth::loginUsingId($adminId);
    }

    /**
     * Check whether the current session is an impersonation session.
     */
    public function active(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Return the original admin user if impersonating, or null.
     */
    public function impersonator(): ?User
    {
        $adminId = Session::get(self::SESSION_KEY);

        return $adminId ? User::find($adminId) : null;
    }
}
