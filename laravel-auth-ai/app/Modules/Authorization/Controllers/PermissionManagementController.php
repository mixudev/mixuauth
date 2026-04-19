<?php

namespace App\Modules\Authorization\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authorization\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class PermissionManagementController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('manage', Permission::class);
        
        $filters = $request->only(['search', 'group', 'sort', 'per_page']);
        $perPage = $filters['per_page'] ?? 20;
        $search = $filters['search'] ?? '';
        $group = $filters['group'] ?? '';
        $sort = $filters['sort'] ?? 'name';

        $permissions = Permission::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%")
                                        ->orWhere('description', 'like', "%$search%"))
            ->when($group, fn($q) => $q->where('group', $group))
            ->orderBy($sort === 'recent' ? 'created_at' : 'name', $sort === 'recent' ? 'desc' : 'asc')
            ->paginate($perPage);

        $groups = Permission::distinct('group')->pluck('group');
        
        $stats = [
            'total' => Permission::count(),
            'groups' => Permission::distinct('group')->count(),
            'by_group' => Permission::selectRaw('`group`, COUNT(*) as count')
                ->groupBy('group')
                ->pluck('count', 'group'),
        ];

        return view('authorization::permission.index', 
            compact('permissions', 'stats', 'filters', 'groups'));
    }

    // ─── Create Modal ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorize('create', Permission::class);
        $groups = Permission::distinct('group')->pluck('group');
        return view('authorization::permission.create', compact('groups'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse | RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:100',
            'description' => 'nullable|string|max:255',
            'group' => 'required|string|max:50',
        ]);

        try {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $i = 2;
            while (Permission::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $permission = Permission::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'group' => $validated['group'],
            ]);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Permission '{$permission->name}' berhasil dibuat."])
                : redirect()->route('dashboard.permissions.index')
                    ->with('success', "Permission '{$permission->name}' berhasil dibuat.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal membuat permission.'], 500)
                : redirect()->back()->with('error', 'Gagal membuat permission.')
                    ->withInput();
        }
    }

    // ─── Edit Modal ─────────────────────────────────────────────────────────────

    public function edit(Permission $permission): View
    {
        $this->authorize('update', $permission);
        $groups = Permission::distinct('group')->pluck('group');
        return view('authorization::permission.edit', 
            compact('permission', 'groups'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Permission $permission): JsonResponse | RedirectResponse
    {
        $this->authorize('update', $permission);

        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'group' => 'required|string|max:50',
        ]);

        try {
            $permission->update([
                'description' => $validated['description'] ?? null,
                'group' => $validated['group'],
            ]);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Permission '{$permission->name}' berhasil diperbarui."])
                : redirect()->route('dashboard.permissions.index')
                    ->with('success', "Permission '{$permission->name}' berhasil diperbarui.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal memperbarui permission.'], 500)
                : redirect()->back()->with('error', 'Gagal memperbarui permission.')
                    ->withInput();
        }
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, Permission $permission): JsonResponse | RedirectResponse
    {
        $this->authorize('delete', $permission);

        $roleCount = $permission->roles()->count();
        if ($roleCount > 0) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => "Total {$roleCount} role masih menggunakan permission ini."], 422)
                : redirect()->back()->with('error', "Total {$roleCount} role masih menggunakan permission ini.");
        }

        try {
            $permName = $permission->name;
            $permission->delete();

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => "Permission '{$permName}' berhasil dihapus."])
                : redirect()->route('dashboard.permissions.index')
                    ->with('success', "Permission '{$permName}' berhasil dihapus.");
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Gagal menghapus permission.'], 500)
                : redirect()->back()->with('error', 'Gagal menghapus permission.');
        }
    }
}
