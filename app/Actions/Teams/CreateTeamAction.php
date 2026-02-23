<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTeamAction
{
    public function execute(User $owner, array $data): Team
    {
        $team = Team::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'owner_id'    => $owner->id,
            'description' => $data['description'] ?? null,
            'avatar'      => $data['avatar'] ?? null,
            'is_active'   => true,
        ]);

        // Add owner as team member with 'owner' role
        TeamMember::create([
            'team_id'   => $team->id,
            'user_id'   => $owner->id,
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        // Assign team_owner role if not already set
        if (! $owner->hasRole('team_owner')) {
            $owner->assignRole('team_owner');
        }

        return $team->refresh();
    }
}
