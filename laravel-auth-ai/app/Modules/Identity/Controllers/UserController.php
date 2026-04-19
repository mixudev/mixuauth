<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request)
    {
        $search = $request->query('search');
        $users = $this->userService->getUsers(['search' => $search]);
        
        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'is_active' => 'boolean'
        ]);

        $this->userService->createUser($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password'  => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean'
        ]);

        $this->userService->updateUser($user, $validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->deleteUser($user);
            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus secara sistem (Soft Delete).');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
