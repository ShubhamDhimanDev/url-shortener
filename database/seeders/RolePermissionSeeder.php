<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================
        // Permissions
        // ========================

        $permissions = [
            // System / Admin
            'access_super_admin_dashboard',
            'manage_system_settings',
            'view_all_users',
            'suspend_users',

            // Teams & Users
            'create_team',
            'manage_team',
            'invite_users',
            'remove_users',
            'view_team_users',

            // Links
            'create_links',
            'edit_links',
            'delete_links',
            'view_links',

            // Domains
            'add_custom_domains',
            'verify_domains',
            'remove_domains',
            'view_domains',

            // Analytics
            'view_analytics',
            'export_analytics',

            // Billing & Subscriptions
            'view_plans',
            'manage_subscription',
            'view_transactions',
            'download_invoices',

            // API
            'access_api',
            'manage_api_keys',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ========================
        // Roles
        // ========================

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $teamOwner  = Role::firstOrCreate(['name' => 'team_owner']);
        $teamMember = Role::firstOrCreate(['name' => 'team_member']);

        // ========================
        // Assign Permissions
        // ========================

        // Super Admin → ALL permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin → Most except critical system ops
        $admin->givePermissionTo([
            'view_all_users',
            'manage_system_settings',
            'suspend_users',

            'create_team',
            'manage_team',
            'invite_users',
            'remove_users',
            'view_team_users',

            'create_links',
            'edit_links',
            'delete_links',
            'view_links',

            'add_custom_domains',
            'verify_domains',
            'remove_domains',
            'view_domains',

            'view_analytics',
            'export_analytics',

            'view_plans',
            'manage_subscription',
            'view_transactions',
            'download_invoices',

            'access_api',
            'manage_api_keys',
        ]);

        // Team Owner → Full control of own workspace
        $teamOwner->givePermissionTo([
            'create_team',
            'manage_team',
            'invite_users',
            'remove_users',
            'view_team_users',

            'create_links',
            'edit_links',
            'delete_links',
            'view_links',

            'add_custom_domains',
            'verify_domains',
            'remove_domains',
            'view_domains',

            'view_analytics',
            'export_analytics',

            'view_plans',
            'manage_subscription',
            'view_transactions',
            'download_invoices',

            'access_api',
            'manage_api_keys',
        ]);

        // Team Member → Limited access
        $teamMember->givePermissionTo([
            'create_links',
            'edit_links',
            'view_links',

            'view_domains',

            'view_analytics',

            'access_api',
        ]);
    }
}
