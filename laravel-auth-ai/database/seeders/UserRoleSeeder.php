<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Assign roles ke users yang sudah ada.
     */
    public function run(): void
    {
        // Hardcode admin emails untuk seeding
        $adminEmails = [
            'lazamediamxt@gmail.com',
            'lazamart357@gmail.com',
        ];

        $this->command->info("Admin emails: " . json_encode($adminEmails));

        // Ambil roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        if (!$superAdminRole || !$adminRole || !$userRole) {
            $this->command->error('Roles not found. Run RolePermissionSeeder first.');
            return;
        }

        // Assign Super Admin role ke admin emails
        $adminUsers = User::whereIn('email', $adminEmails)->get();
        $this->command->info("Found " . $adminUsers->count() . " super admin users");
        foreach ($adminUsers as $user) {
            $user->syncRoles(['super-admin']);
            $this->command->info("User '{$user->email}' assigned to Super Admin role");
        }

        // Assign User role ke users lainnya
        $regularUsers = User::whereNotIn('email', $adminEmails)->get();
        $this->command->info("Found " . $regularUsers->count() . " regular users");
        foreach ($regularUsers as $user) {
            $user->syncRoles(['user']);
            $this->command->info("User '{$user->email}' assigned to User role");
        }
    }
}
