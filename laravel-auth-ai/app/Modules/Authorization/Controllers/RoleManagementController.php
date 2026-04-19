<?php

namespace App\Modules\Authorization\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authorization\Models\Role;
use App\Modules\Authorization\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('manage', Role::class);
        
        $filters = $request->only(['search', 'sort', 'per_page']);
        $perPage = $filters['per_page'] ?? 15;
        $search = $filters['search'] ?? '';
        $sort = $filters['sort'] ?? 'name';

        $roles = Role::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%")
                                        ->orWhere('description', 'like', "%$search%"))
            ->orderBy($sort === 'recent' ? 'created_at' : 'name', $sort === 'recent' ? 'desc' : 'asc')
            ->paginate($perPage);

        $stats = [
            'total' => Role::count(),
            'system' => Role::whereIn('slug', ['super-admin', 'admin'])->count(),
            'custom' => Role::whereNotIn('slug', ['super-admin', 'admin', 'user', 'security-officer'])->count(),
        ];

        return view('authorization::role.index', compact('roles', 'stats', 'filters'));
    }

    // ─── Create Modal ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorize('create', Role::class);
        $permissions = Permission::all()->groupBy('group');
        return view('authorization::role.create', compact('permissions'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse | RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles|max:50',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $slug = strtolower(str_replace(' ', '-', $validated['name']));
            
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
            ]);

            if (!empty($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Role '{$role->name}' berhasil dibuat."])
                : redirect()->route('dashboard.roles.index')
                    ->with('success', "Role '{$role->display_name}' berhasil dibuat.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal membuat role.'], 500)
                : redirect()->back()->with('error', 'Gagal membuat role.')
                    ->withInput();
        }
    }

    // ─── Edit Modal ─────────────────────────────────────────────────────────────

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);
        $permissions = Permission::all()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('authorization::role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Role $role): JsonResponse | RedirectResponse
    {
        $this->authorize('update', $role);

        if (in_array($role->slug, ['super-admin', 'admin', 'user', 'security-officer'])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Role bawaan sistem tidak dapat diubah.'], 403)
                : redirect()->back()->with('error', 'Role bawaan sistem tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $role->permissions()->sync($validated['permissions'] ?? []);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Role '{$role->name}' berhasil diperbarui."])
                : redirect()->route('dashboard.roles.index')
                    ->with('success', "Role '{$role->name}' berhasil diperbarui.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal memperbarui role.'], 500)
                : redirect()->back()->with('error', 'Gagal memperbarui role.')
                    ->withInput();
        }
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, Role $role): JsonResponse | RedirectResponse
    {
        $this->authorize('delete', $role);

        if (in_array($role->slug, ['super-admin', 'admin', 'user', 'security-officer'])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Role bawaan sistem tidak dapat dihapus.'], 403)
                : redirect()->back()->with('error', 'Role bawaan sistem tidak dapat dihapus.');
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => "Total {$userCount} pengguna masih menggunakan role ini."], 422)
                : redirect()->back()->with('error', "Total {$userCount} pengguna masih menggunakan role ini.");
        }

        try {
            $roleName = $role->name;
            $role->permissions()->detach();
            $role->delete();

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Role '{$roleName}' berhasil dihapus."])
                : redirect()->route('dashboard.roles.index')
                    ->with('success', "Role '{$roleName}' berhasil dihapus.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal menghapus role.'], 500)
                : redirect()->back()->with('error', 'Gagal menghapus role.');
        }
    }

    // ─── Get Permissions by Group (AJAX) ────────────────────────────────────────

    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all()->groupBy('group');
        return response()->json($permissions);
    }
}
