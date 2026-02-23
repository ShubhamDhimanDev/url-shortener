<?php

namespace App\Events\Teams;

use App\Models\Team;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberInvited
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Team   $team,
        public readonly string $email,
        public readonly string $role,
    ) {}
}
