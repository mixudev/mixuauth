<?php

namespace App\Modules\Authorization;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Modules\Authorization\Models\Role;
use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Policies\RolePolicy;
use App\Modules\Authorization\Policies\PermissionPolicy;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Routes & Views ────────────────────────────────────────────────────
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(resource_path('views/admin/authorization'), 'authorization');

        // ── RBAC Gate Logic ───────────────────────────────────────────────────
        
        // Define policies specifically for this module
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        // Global check: super-admin bypasses everything,
        // map abilities that contain a dot (e.g. "users.edit") to permissions.
        Gate::before(static function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });

        // Backward-compatible generic gates
        Gate::define('access-admin-panel', static fn (User $user): bool => $user->can('dashboard.view'));
        Gate::define('access-admin-security', static fn (User $user): bool => $user->can('settings.security') || $user->can('errors.view'));
    }
}
