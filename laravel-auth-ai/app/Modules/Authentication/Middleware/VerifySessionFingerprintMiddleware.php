<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Security\Services\DeviceFingerprintService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySessionFingerprintMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware untuk memverifikasi bahwa sesi aktif berasal dari
    | perangkat yang sama dengan saat login.
    |
    | Mencegah session hijacking: jika fingerprint berubah,
    | sesi dianggap tidak valid dan pengguna diarahkan login ulang.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Lewati jika tidak ada sesi yang aktif
        if (! Auth::check()) {
            return $next($request);
        }

        // Lewati jika fitur ini dinonaktifkan di konfigurasi
        if (! config('security.session.bind_to_fingerprint', true)) {
            return $next($request);
        }

        $currentFingerprint = $this->fingerprintService->generate($request);
        $storedFingerprint  = session('auth_device_fingerprint');

        // Jika sesi belum memiliki fingerprint (sesi lama), simpan sekarang
        if (empty($storedFingerprint)) {
            session(['auth_device_fingerprint' => $currentFingerprint]);
            return $next($request);
        }

        // Bandingkan fingerprint menggunakan perbandingan yang aman dari timing attack
        if (! hash_equals($storedFingerprint, $currentFingerprint)) {
            $userId = Auth::id();

            Log::channel('security')->warning('Sesi tidak valid: fingerprint berubah', [
                'user_id'    => $userId,
                'ip_address' => $this->fingerprintService->getRealIp($request),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Sesi Anda tidak valid. Silakan login kembali.',
                ]);
            }

            return response()->json([
                'message'    => 'Sesi Anda tidak valid. Silakan login kembali.',
                'error_code' => 'SESSION_INVALID',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
