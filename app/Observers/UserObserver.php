<?php

namespace App\Observers;

use App\Events\Auth\UserRegistered;
use App\Models\User;

/**
 * Observe Eloquent lifecycle hooks on the User model.
 *
 * Responsibilities
 * - created : assign the default 'individual' role + fire UserRegistered
 * - updated : if the email address was changed, revoke email verification
 *             so the user must re-confirm their new address
 */
class UserObserver
{
    // ─── created ─────────────────────────────────────────────────────────────

    /**
     * After a new user row is inserted:
     *
     * 1. Assign the 'individual' Spatie role as the default.
     *    (Seeders / admin controllers may override this immediately after.)
     * 2. Dispatch the UserRegistered domain event — listeners send the
     *    welcome notification and notify admins (both via queued listeners).
     */
    public function created(User $user): void
    {
        // Only assign a role when Spatie Permission is available and the user
        // does not already have a role (prevents double-assignment from seeders).
        if (method_exists($user, 'hasAnyRole') && ! $user->roles()->exists()) {
            $user->assignRole('individual');
        }

        UserRegistered::dispatch($user);
    }

    // ─── updated ─────────────────────────────────────────────────────────────

    /**
     * After a user row is updated: if the email changed, clear verification.
     *
     * The user will receive a new verification email via the standard Laravel
     * MustVerifyEmail flow once they next try to access a verified-only route.
     *
     * We use `getOriginal('email')` to detect the change because `isDirty()`
     * is already cleared at this point (the model is freshly persisted).
     */
    public function updated(User $user): void
    {
        $originalEmail = $user->getOriginal('email');

        if ($originalEmail !== null && $originalEmail !== $user->email) {
            // Directly update the column to avoid triggering the observer again.
            $user->withoutEvents(function () use ($user) {
                $user->forceFill(['email_verified_at' => null])->saveQuietly();
            });
        }
    }
}
