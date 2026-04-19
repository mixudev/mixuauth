<?php

namespace Database\Seeders;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Jalankan seeder untuk membuat roles dan permissions.
     */
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. CREATE PERMISSIONS (Grouped by module)
        // ─────────────────────────────────────────────────────────────

        // Users Management Permissions
        $permissions = [
            // Users Management
            ['name' => 'Lihat Users',              'slug' => 'users.view',           'group' => 'users'],
            ['name' => 'Buat User',                'slug' => 'users.create',         'group' => 'users'],
            ['name' => 'Edit User',                'slug' => 'users.edit',           'group' => 'users'],
            ['name' => 'Hapus User',               'slug' => 'users.delete',         'group' => 'users'],

            // Login Logs Permissions
            ['name' => 'Lihat Login Logs',         'slug' => 'login-logs.view',      'group' => 'login-logs'],
            ['name' => 'Export Login Logs',        'slug' => 'login-logs.export',    'group' => 'login-logs'],
            ['name' => 'Hapus Login Logs',         'slug' => 'login-logs.delete',    'group' => 'login-logs'],

            // Trusted Devices Permissions
            ['name' => 'Lihat Trusted Devices',    'slug' => 'devices.view',         'group' => 'devices'],
            ['name' => 'Revoke Device',            'slug' => 'devices.revoke',       'group' => 'devices'],

            // OTP Management Permissions
            ['name' => 'Lihat OTP Verifications',  'slug' => 'otp.view',             'group' => 'otp'],
            ['name' => 'Hapus OTP',                'slug' => 'otp.delete',           'group' => 'otp'],

            // IP Blacklist/Whitelist Permissions
            ['name' => 'Lihat IP List',            'slug' => 'ip-list.view',         'group' => 'ip-list'],
            ['name' => 'Kelola IP Blacklist',      'slug' => 'ip-list.blacklist',    'group' => 'ip-list'],
            ['name' => 'Kelola IP Whitelist',      'slug' => 'ip-list.whitelist',    'group' => 'ip-list'],

            // Security Settings Permissions
            ['name' => 'Lihat Security Settings',  'slug' => 'settings.security',    'group' => 'settings'],
            ['name' => 'Edit Security Settings',   'slug' => 'settings.security.edit', 'group' => 'settings'],

            // Monitoring & Analytics Permissions
            ['name' => 'Lihat Dashboard',          'slug' => 'dashboard.view',       'group' => 'dashboard'],
            ['name' => 'Lihat Statistics',         'slug' => 'analytics.view',       'group' => 'analytics'],
            ['name' => 'Lihat System Errors',      'slug' => 'errors.view',          'group' => 'errors'],

            // Roles & Permissions Management
            ['name' => 'Lihat Roles',              'slug' => 'roles.view',           'group' => 'rbac'],
            ['name' => 'Buat Role',                'slug' => 'roles.create',         'group' => 'rbac'],
            ['name' => 'Edit Role',                'slug' => 'roles.edit',           'group' => 'rbac'],
            ['name' => 'Hapus Role',               'slug' => 'roles.delete',         'group' => 'rbac'],
            ['name' => 'Lihat Permissions',        'slug' => 'permissions.view',     'group' => 'rbac'],
            ['name' => 'Buat Permission',          'slug' => 'permissions.create',   'group' => 'rbac'],
            ['name' => 'Edit Permission',          'slug' => 'permissions.edit',     'group' => 'rbac'],
            ['name' => 'Hapus Permission',         'slug' => 'permissions.delete',   'group' => 'rbac'],
            ['name' => 'Assign Permissions',       'slug' => 'permissions.assign',   'group' => 'rbac'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 2. CREATE ROLES
        // ─────────────────────────────────────────────────────────────

        // Super Admin Role - Akses penuh ke semua fitur
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name'        => 'Super Administrator',
                'description' => 'Akses penuh ke semua fitur sistem',
            ]
        );

        // Admin Role - Kelola user dan monitoring
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name'        => 'Administrator',
                'description' => 'Kelola user, monitoring login, dan keamanan',
            ]
        );

        // Security Officer Role - Kelola keamanan dan monitoring
        $securityRole = Role::firstOrCreate(
            ['slug' => 'security-officer'],
            [
                'name'        => 'Security Officer',
                'description' => 'Monitoring keamanan, IP list, dan perangkat',
            ]
        );

        // User Role - Akses user umum (read-only)
        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name'        => 'User',
                'description' => 'User biasa dengan akses terbatas',
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // 3. ASSIGN PERMISSIONS TO ROLES
        // ─────────────────────────────────────────────────────────────

        // Super Admin: Akses semua permission
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissionIds);

        // Admin: Kelola users, lihat logs, kelola devices, kelola settings
        $adminPermissions = Permission::whereIn('slug', [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'login-logs.view', 'login-logs.export',
            'devices.view', 'devices.revoke',
            'otp.view',
            'dashboard.view', 'analytics.view',
            'settings.security',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        ])->pluck('id')->toArray();
        $adminRole->permissions()->sync($adminPermissions);

        // Security Officer: Monitoring keamanan
        $securityPermissions = Permission::whereIn('slug', [
            'users.view',
            'login-logs.view', 'login-logs.export', 'login-logs.delete',
            'devices.view', 'devices.revoke',
            'otp.view',
            'ip-list.view', 'ip-list.blacklist', 'ip-list.whitelist',
            'dashboard.view', 'analytics.view', 'errors.view',
            'settings.security',
        ])->pluck('id')->toArray();
        $securityRole->permissions()->sync($securityPermissions);

        // User: Limited access (read-only)
        $userPermissions = Permission::whereIn('slug', [
            'dashboard.view',
        ])->pluck('id')->toArray();
        $userRole->permissions()->sync($userPermissions);
    }
}
