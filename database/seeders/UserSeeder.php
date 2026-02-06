<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // Super Admin
        // =========================
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // =========================
        // Admin
        // =========================
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // =========================
        // Team Owner (Solo User)
        // =========================
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Team Owner',
                'password' => Hash::make('password'),
            ]
        );
        $owner->assignRole('team_owner');

        $team = Team::firstOrCreate([
            'owner_id' => $owner->id,
        ], [
            'name' => $owner->name . "'s Workspace",
        ]);

        $owner->teams()->syncWithoutDetaching($team->id);

        // =========================
        // Team Member
        // =========================
        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('password'),
            ]
        );
        $member->assignRole('team_member');

        $member->teams()->syncWithoutDetaching($team->id);
    }
}
