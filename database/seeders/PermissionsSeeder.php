<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * All platform permissions grouped by resource.
     */
    private array $permissions = [
        // Links
        'links.view',
        'links.create',
        'links.edit',
        'links.delete',
        'links.bulk',
        'links.export',

        // Analytics
        'analytics.view',
        'analytics.view_advanced',
        'analytics.export',

        // Domains
        'domains.view',
        'domains.manage',
        'domains.delete',

        // Teams
        'teams.view',
        'teams.create',
        'teams.edit',
        'teams.delete',
        'teams.manage_members',
        'teams.invite_members',

        // Billing
        'billing.view',
        'billing.manage',
        'billing.download_invoices',

        // QR Codes
        'qrcodes.generate',
        'qrcodes.customize',

        // Profile
        'profile.view',
        'profile.edit',
        'profile.two_factor',

        // Super Admin — Users
        'super_admin.users.view',
        'super_admin.users.create',
        'super_admin.users.edit',
        'super_admin.users.delete',
        'super_admin.users.impersonate',

        // Super Admin — Teams
        'super_admin.teams.view',
        'super_admin.teams.edit',
        'super_admin.teams.delete',

        // Super Admin — Plans
        'super_admin.plans.view',
        'super_admin.plans.create',
        'super_admin.plans.edit',
        'super_admin.plans.delete',

        // Super Admin — Subscriptions
        'super_admin.subscriptions.view',
        'super_admin.subscriptions.edit',
        'super_admin.subscriptions.cancel',

        // Super Admin — Invoices
        'super_admin.invoices.view',
        'super_admin.invoices.void',
        'super_admin.invoices.download',

        // Super Admin — Settings
        'super_admin.settings.view',
        'super_admin.settings.edit',

        // Super Admin — Analytics
        'super_admin.analytics.view',
    ];

    /**
     * Role → permission mappings.
     */
    private array $rolePermissions = [
        'super_admin' => '*', // All permissions

        'team_owner' => [
            'links.view', 'links.create', 'links.edit', 'links.delete', 'links.bulk', 'links.export',
            'analytics.view', 'analytics.view_advanced', 'analytics.export',
            'domains.view', 'domains.manage', 'domains.delete',
            'teams.view', 'teams.create', 'teams.edit', 'teams.delete', 'teams.manage_members', 'teams.invite_members',
            'billing.view', 'billing.manage', 'billing.download_invoices',
            'qrcodes.generate', 'qrcodes.customize',
            'profile.view', 'profile.edit', 'profile.two_factor',
        ],

        'team_member' => [
            'links.view', 'links.create', 'links.edit', 'links.delete',
            'analytics.view',
            'qrcodes.generate',
            'profile.view', 'profile.edit', 'profile.two_factor',
        ],

        'individual' => [
            'links.view', 'links.create', 'links.edit', 'links.delete', 'links.bulk', 'links.export',
            'analytics.view', 'analytics.view_advanced', 'analytics.export',
            'domains.view', 'domains.manage', 'domains.delete',
            'billing.view', 'billing.manage', 'billing.download_invoices',
            'qrcodes.generate', 'qrcodes.customize',
            'profile.view', 'profile.edit', 'profile.two_factor',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach ($this->rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === '*') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
