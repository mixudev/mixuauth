<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionVersionMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | [H-05 FIX] Session Version Enforcement
    |
    | Masalah sebelumnya:
    |   Jika session('auth_session_version') === null, middleware langsung
    |   menulis versi dari database dan MELANJUTKAN request. Ini memungkinkan
    |   session fixation attack: attacker yang menanam session ID dapat
    |   masuk karena null dianggap "sesi baru yang sah".
    |
    | Perbaikan:
    |   Sesi baru HANYA dianggap sah jika session_version sudah di-set secara
    |   eksplisit oleh AuthFlowService::completeAuthenticatedSession().
    |   Sesi tanpa version → langsung logout dan redirect ke login.
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user           = $request->user();
        $sessionVersion = $request->session()->get('auth_session_version');

        // [H-05 FIX] Sesi tanpa version → tidak valid, tolak akses
        if ($sessionVersion === null) {
            Log::channel('security')->warning('Sesi tanpa versi ditolak (potensi session fixation)', [
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message'    => 'Sesi tidak valid. Silakan login kembali.',
                    'error_code' => 'SESSION_INVALID',
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Sesi Anda tidak valid. Silakan login kembali.',
            ]);
        }

        // Versi sesi tidak cocok → sesi sudah dicabut
        if ((int) $sessionVersion !== (int) $user->session_version) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message'    => 'Sesi Anda sudah tidak berlaku. Silakan login kembali.',
                    'error_code' => 'SESSION_REVOKED',
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Sesi Anda sudah tidak berlaku. Silakan login kembali.',
            ]);
        }

        return $next($request);
    }
}
