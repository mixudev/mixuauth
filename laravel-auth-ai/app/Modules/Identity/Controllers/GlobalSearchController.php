<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    /**
     * Search for users and roles globally for the Command Palette.
     */
    public function search(Request $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->hasPermission('users.view')) {
            return response()->json([]);
        }

        $query = trim((string) $request->get('q', ''));
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Users
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'title' => $user->name,
                'url' => route('dashboard.users.index') . '?search=' . $user->email,
                'category' => 'Users',
                'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
            ];
        }

        // Search Roles (Basic internal check if permission exists)
        // You can expand this logic if you have a Role model
        
        return response()->json($results);
    }
}
