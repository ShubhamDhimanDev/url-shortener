<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    /**
     * A super_admin can do anything.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the link's analytics/detail page.
     */
    public function view(User $user, Link $link): bool
    {
        return $this->owns($user, $link);
    }

    /**
     * Determine whether the user can update the link.
     */
    public function update(User $user, Link $link): bool
    {
        return $this->owns($user, $link);
    }

    /**
     * Determine whether the user can delete the link.
     */
    public function delete(User $user, Link $link): bool
    {
        return $this->owns($user, $link);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Return true if $user directly owns the link OR is a member of the team
     * that owns it. The team membership check mirrors LinkController::findLink().
     */
    private function owns(User $user, Link $link): bool
    {
        if ($link->team_id !== null) {
            return $user->teamMemberships()
                ->where('team_id', $link->team_id)
                ->exists();
        }

        return (int) $link->user_id === (int) $user->id;
    }
}
