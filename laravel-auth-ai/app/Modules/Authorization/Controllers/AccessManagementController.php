<?php

namespace App\Modules\Authorization\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authorization\Models\Role;
use App\Modules\Authorization\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AccessManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', Role::class);

        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all()->groupBy('group');
        $groups = Permission::distinct('group')->pluck('group');
        
        // Users for assignment section
        $userFilters = $request->only(['user_search', 'user_per_page']);
        $userSearch = $userFilters['user_search'] ?? '';
        $userPerPage = $userFilters['user_per_page'] ?? 10;

        $users = User::query()
            ->with('roles')
            ->when($userSearch, function($q) use ($userSearch) {
                $q->where('name', 'like', "%$userSearch%")
                  ->orWhere('email', 'like', "%$userSearch%");
            })
            ->paginate($userPerPage, ['*'], 'user_page')
            ->withQueryString();

        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users' => User::count(),
        ];

        return view('authorization::management.index', compact('roles', 'permissions', 'groups', 'stats', 'users', 'userFilters'));
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $search = $request->get('q');
        $users = User::query()
            ->with('roles')
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name', 'id')
                ];
            });

        return response()->json($users);
    }

    public function createRole(): View
    {
        $this->authorize('manage', Role::class);
        $permissions = Permission::all()->groupBy('group');
        return view('authorization::management.roles.create', compact('permissions'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->authorize('manage', Role::class);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('dashboard.access-management.index', ['tab' => 'roles'])
            ->with('success', 'Role berhasil dibuat.');
    }

    public function editRole(Role $role): View
    {
        $this->authorize('manage', Role::class);
        $permissions = Permission::all()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('authorization::management.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('manage', Role::class);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('dashboard.access-management.index', ['tab' => 'roles'])
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroyRole(Role $role): JsonResponse
    {
        $this->authorize('manage', Role::class);

        // Check if it's a system role
        if (in_array($role->slug, ['super-admin', 'admin', 'user', 'security-officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Role sistem tidak dapat dihapus.'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus.'
        ]);
    }

    public function assignRoles(Request $request): JsonResponse
    {
        $this->authorize('manage', Role::class);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'action' => 'required|in:sync,attach,detach'
        ]);

        $userIds = $validated['user_ids'];
        $roleIds = $validated['role_ids'];
        $action = $validated['action'];

        DB::beginTransaction();
        try {
            $users = User::whereIn('id', $userIds)->get();
            
            foreach ($users as $user) {
                if ($action === 'sync') {
                    $user->roles()->sync($roleIds);
                } elseif ($action === 'attach') {
                    $user->roles()->syncWithoutDetaching($roleIds);
                } elseif ($action === 'detach') {
                    $user->roles()->detach($roleIds);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui untuk ' . count($userIds) . ' pengguna.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role: ' . $e->getMessage()
            ], 500);
        }
    }
}
