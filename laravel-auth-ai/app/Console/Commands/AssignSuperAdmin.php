<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Authorization\Models\Role;
use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    protected $signature = 'user:assign-superadmin {email}';
    protected $description = 'Assign super-admin role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan");
            return 1;
        }

        try {
            // Check if super-admin role exists (using slug field)
            $superAdminRole = Role::where('slug', 'super-admin')->first();
            if (!$superAdminRole) {
                $this->error("Role 'super-admin' tidak ditemukan");
                return 1;
            }

            // Assign role using slug
            $user->roles()->sync([$superAdminRole->id]);

            $this->info("✓ Berhasil assign super-admin role");
            $this->line("User: {$user->name}");
            $this->line("Email: {$user->email}");
            $this->line("Roles: " . $user->roles()->pluck('slug')->join(', '));
            
            // Show permissions count
            $permCount = $user->roles->first()?->permissions()->count() ?? 0;
            $this->line("Permissions: {$permCount}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
