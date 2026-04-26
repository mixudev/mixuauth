<?php

namespace App\Modules\SSO\Controllers\Admin;

use App\Modules\SSO\Models\SsoClient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController
{
    /**
     * Tampilkan daftar aplikasi yang terintegrasi beserta statistik user aktif.
     */
    public function index(): View
    {
        $clients = SsoClient::active()->get()->map(function ($client) {
            // Hitung user unik yang memiliki token aktif untuk client ini
            $client->active_users_count = DB::table('oauth_access_tokens')
                ->where('client_id', $client->oauth_client_id)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->distinct('user_id')
                ->count('user_id');

            // Ambil detail user yang sedang aktif (limit 5 untuk preview)
            $client->active_users_preview = User::whereIn('id', function($query) use ($client) {
                $query->select('user_id')
                    ->from('oauth_access_tokens')
                    ->where('client_id', $client->oauth_client_id)
                    ->where('revoked', false)
                    ->where('expires_at', '>', now());
            })->limit(5)->get();

            return $client;
        });

        return view('admin.sso.applications.index', compact('clients'));
    }

    /**
     * Tampilkan detail lengkap satu aplikasi.
     */
    public function show(SsoClient $client): View
    {
        $client->load('accessAreas');

        // 1. Data User Aktif (Memiliki Token Valid)
        $activeUsers = User::whereIn('id', function($query) use ($client) {
            $query->select('user_id')
                ->from('oauth_access_tokens')
                ->where('client_id', $client->oauth_client_id)
                ->where('revoked', false)
                ->where('expires_at', '>', now());
        })->get();

        // 2. Data User Terdaftar (Pernah Login ke App Ini tapi Token Expired/Revoked)
        $inactiveUsers = User::whereIn('id', function($query) use ($client) {
            $query->select('user_id')
                ->from('oauth_access_tokens')
                ->where('client_id', $client->oauth_client_id)
                ->where(function($q) {
                    $q->where('revoked', true)
                      ->orWhere('expires_at', '<=', now());
                });
        })
        ->whereNotIn('id', $activeUsers->pluck('id'))
        ->get();

        // 3. Statistik Token
        $stats = [
            'total_tokens'   => DB::table('oauth_access_tokens')->where('client_id', $client->oauth_client_id)->count(),
            'revoked_tokens' => DB::table('oauth_access_tokens')->where('client_id', $client->oauth_client_id)->where('revoked', true)->count(),
        ];

        return view('admin.sso.applications.show', compact('client', 'activeUsers', 'inactiveUsers', 'stats'));
    }
}
