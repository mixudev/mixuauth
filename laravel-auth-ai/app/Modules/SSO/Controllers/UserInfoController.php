<?php

namespace App\Modules\SSO\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserInfoController
{
    /**
     * GET /api/user
     *
     * Mengembalikan profil user yang terautentikasi via Passport Bearer token.
     * Format response sesuai yang diharapkan oleh package mixu/sso-auth client.
     *
     * Security Layer [5.10]:
     * Cek apakah OAuth Client yang mengeluarkan token ini masih aktif di sso_clients.
     * Jika client sudah dinonaktifkan setelah token diterbitkan → tolak request.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // ── [5.10] Client Inactive Guard ──────────────────────────────────────
        // Cek status aktif client dari token yang dipakai request ini.
        // Ini melindungi dari case dimana client dicabut izinnya tapi user
        // masih punya token valid yang belum expired.
        $token = $request->user()->token();
        if ($token) {
            $clientId = $token->client_id;

            $ssoClientActive = DB::table('sso_clients')
                ->where('oauth_client_id', $clientId)
                ->where('is_active', true)
                ->exists();

            // Jika client terdaftar di sso_clients tapi sudah nonaktif → tolak
            $clientIsRegistered = DB::table('sso_clients')
                ->where('oauth_client_id', $clientId)
                ->exists();

            if ($clientIsRegistered && ! $ssoClientActive) {
                // Revoke token ini sekaligus agar client tidak bisa coba lagi
                $token->revoke();

                return response()->json([
                    'error'   => 'client_disabled',
                    'message' => 'The application you are using has been disabled. Please contact your administrator.',
                ], 403);
            }
        }

        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'avatar'       => $user->avatar_url ?? null,
            'is_active'    => (bool) $user->is_active,
            // Roles dari sistem Authorization (slug-based)
            'roles'        => $user->roles()->pluck('slug')->toArray(),
            // Access areas dari sistem SSO (slug-based, tabel terpisah)
            'access_areas' => $user->accessAreas()->pluck('slug')->toArray(),
        ]);
    }
}
