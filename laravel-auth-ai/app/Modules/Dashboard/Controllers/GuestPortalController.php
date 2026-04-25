<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestPortalController extends Controller
{
    /**
     * Tampilkan halaman informasi untuk user yang sudah login
     * namun tidak memiliki akses ke dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Load relasi yang dibutuhkan
        $user->load(['roles.permissions', 'loginLogs' => function ($query) {
            $query->orderBy('login_logs.occurred_at', 'desc')->limit(5);
        }]);

        $roles       = $user->roles;
        $permissions = $user->permissions();
        $recentLogs  = $user->loginLogs;

        return view('guest.portal', compact('user', 'roles', 'permissions', 'recentLogs'));
    }
}
