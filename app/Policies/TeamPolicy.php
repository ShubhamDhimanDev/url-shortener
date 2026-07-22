<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Super admins bypass all checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * View a team — owner or any member may view it.
     */
    public function view(User $user, Team $team): bool
    {
        return $this->isMember($user, $team);
    }

    /**
     * Update team settings — owner or admin-role members only.
     */
    public function update(User $user, Team $team): bool
    {
        return $this->isAdminOrOwner($user, $team);
    }

    /**
     * Delete a team — owner only.
     */
    public function delete(User $user, Team $team): bool
    {
        return (int) $team->owner_id === (int) $user->id;
    }

    /**
     * Invite / remove members — owner or admin-role members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        return $this->isAdminOrOwner($user, $team);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * True if the user is the team owner OR appears in team_members.
     */
    private function isMember(User $user, Team $team): bool
    {
        if ((int) $team->owner_id === (int) $user->id) {
            return true;
        }

        return $team->members()->where('user_id', $user->id)->exists();
    }

    /**
     * True if the user is the team owner OR a member with role owner/admin.
     */
    private function isAdminOrOwner(User $user, Team $team): bool
    {
        if ((int) $team->owner_id === (int) $user->id) {
            return true;
        }

        return $team->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
}
