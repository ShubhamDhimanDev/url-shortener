<?php

namespace App\Actions\Teams;

use App\Events\Teams\MemberInvited;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

class InviteMemberAction
{
    /**
     * Invite a user (by email) to a team. If the user exists, create membership.
     * Either way, fire MemberInvited event so the notification is queued.
     *
     * @param  string  $role   'admin' or 'member'
     */
    public function execute(Team $team, string $email, string $role = 'member'): void
    {
        $invitee = User::where('email', $email)->first();

        if ($invitee) {
            // Prevent duplicate memberships
            $existing = TeamMember::where('team_id', $team->id)
                ->where('user_id', $invitee->id)
                ->first();

            if (! $existing) {
                TeamMember::create([
                    'team_id'   => $team->id,
                    'user_id'   => $invitee->id,
                    'role'      => $role,
                    'joined_at' => now(),
                ]);
            }
        }

        // Fire event regardless — Listener will send invite email
        event(new MemberInvited($team, $email, $role));
    }
}
