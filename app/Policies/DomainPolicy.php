<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
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
     * View a domain — owner or team member.
     */
    public function view(User $user, Domain $domain): bool
    {
        return $this->owns($user, $domain);
    }

    /**
     * Update / verify a domain — owner or team member.
     */
    public function update(User $user, Domain $domain): bool
    {
        return $this->owns($user, $domain);
    }

    /**
     * Delete a domain — owner or team member.
     */
    public function delete(User $user, Domain $domain): bool
    {
        return $this->owns($user, $domain);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * True if the user directly created the domain OR is a member of the
     * team that owns it.
     */
    private function owns(User $user, Domain $domain): bool
    {
        if ($domain->team_id !== null) {
            return $user->teamMemberships()
                ->where('team_id', $domain->team_id)
                ->exists();
        }

        return (int) $domain->user_id === (int) $user->id;
    }
}
