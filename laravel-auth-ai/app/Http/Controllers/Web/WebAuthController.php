<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TimezoneService;
use App\Models\User;
use App\Services\Security\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebAuthController extends Controller
{
    protected string $apiBase;

    public function __construct(
        private readonly TimezoneService $timezoneService,
        private readonly DeviceFingerprintService $fingerprintService,
    ) {
        // Gunakan host Nginx internal Docker untuk koneksi server-to-server
        $this->apiBase = 'http://nginx/api';
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Target host untuk cookie (sesuai host apiBase)
        $apiHost = parse_url($this->apiBase, PHP_URL_HOST);

        $response = Http::withHeaders([
            'Accept'          => 'application/json',
            'X-Forwarded-For' => $this->fingerprintService->getRealIp($request),
            'User-Agent'      => $request->userAgent(),
        ])
        ->withCookies($request->cookies->all(), $apiHost)
        ->asForm()
        ->post($this->apiBase . '/auth/login', [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $data = $response->json();

        // ── MFA required ──────────────────────────────────────────────────
        if ($response->status() === 202 && (isset($data['requires_mfa']) || isset($data['requires_otp']))) {
            // Simpan data MFA ke session
            session([
                'mfa_session_token' => $data['session_token'],
                'mfa_expires_in'    => $data['expires_in'],
                'mfa_type'          => $data['mfa_type'] ?? 'email',
                'mfa_email'         => $request->email,
                'mfa_timezone'      => $request->input('_timezone'),
            ]);

            return redirect()->route('auth.mfa.verify')
                ->with('info', $data['message']);
        }

        // ── Login langsung berhasil ───────────────────────────────────────
        if ($response->successful() && isset($data['user'])) {
            $user = User::find($data['user']['id']);

            if ($user) {
                Auth::login($user, $request->boolean('remember'));

                // ── FIX BUG #2: sync timezone SETELAH Auth::login,
                //    timezone tersedia dari _timezone (hidden input form)
                $this->syncTimezoneAfterLogin($request, $user);
            }

            $redirect = redirect()->route('dashboard')
                ->with('success', $data['message']);

            // ── PROPAGASI COOKIE: Ambil device_trust_id dari API dan teruskan ke Browser ──
            $apiCookie = null;
            foreach ($response->cookies() as $cookie) {
                if ($cookie->getName() === 'device_trust_id') {
                    $apiCookie = $cookie->getValue();
                    break;
                }
            }

            if ($apiCookie) {
                $redirect->withCookie(cookie(
                    'device_trust_id',
                    $apiCookie,
                    60 * 24 * 30, // 30 hari
                    '/',
                    null,
                    $request->isSecure(),
                    true,
                    false,
                    'Lax'
                ));
            }

            return $redirect;
        }

        // ── Error responses ───────────────────────────────────────────────
        if ($response->status() === 403) {
            return back()->withErrors([
                'email' => $data['message'] ?? 'Login diblokir karena aktivitas mencurigakan.',
            ]);
        }

        if ($response->status() === 429) {
            return back()
                ->withInput($request->only('email'))
                ->with('rate_limited', true)
                ->with('retry_after', $data['retry_after'] ?? 60)
                ->withErrors(['email' => $data['message'] ?? 'Terlalu banyak percobaan.']);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $data['message'] ?? 'Email atau password salah.']);
    }

    public function showMfaVerify()
    {
        if (! session('mfa_session_token')) {
            return redirect()->route('login');
        }

        return view('auth.mfa', [
            'email'      => session('mfa_email'),
            'type'       => session('mfa_type', 'email'),
            'expires_in' => session('mfa_expires_in'),
        ]);
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $apiHost = parse_url($this->apiBase, PHP_URL_HOST);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
        ->withCookies($request->cookies->all(), $apiHost)
        ->asForm()
        ->post($this->apiBase . '/auth/mfa/verify', [
            'session_token' => session('mfa_session_token'),
            'code'          => $request->code,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['user'])) {
            $user = User::find($data['user']['id']);

            if ($user) {
                Auth::login($user);
                $this->syncTimezoneAfterLogin($request, $user, session('mfa_timezone'));
            }

            session()->forget([
                'mfa_session_token',
                'mfa_type',
                'mfa_expires_in',
                'mfa_email',
                'mfa_timezone',
            ]);

            $redirect = redirect()->route('dashboard')
                ->with('success', 'Verifikasi berhasil. Selamat datang!');

            // Propagasi trusted device cookie
            $apiCookie = null;
            foreach ($response->cookies() as $cookie) {
                if ($cookie->getName() === 'device_trust_id') {
                    $apiCookie = $cookie->getValue();
                    break;
                }
            }

            if ($apiCookie) {
                $redirect->withCookie(cookie(
                    'device_trust_id', $apiCookie, 60 * 24 * 30, '/', null, $request->isSecure(), true, false, 'Lax'
                ));
            }

            return $redirect;
        }

        return back()->withErrors([
            'code' => $data['message'] ?? 'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
        ]);
    }

    public function logout(Request $request)
    {
        Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($this->apiBase . '/auth/logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post($this->apiBase . '/auth/forgot-password', [
            'email' => $request->email,
        ]);

        return back()->with('success', $response->json()['message'] ?? 'Permintaan reset telah dikirim.');
    }

    public function showResetPassword(Request $request, $token)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($this->apiBase . '/auth/reset-password/validate', [
            'email' => $request->email,
            'token' => $token,
        ]);

        if (! $response->successful()) {
            return view('auth.reset-link-expired', [
                'message' => $response->json()['message'] ?? 'Link reset password tidak valid atau kadaluarsa.'
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post($this->apiBase . '/auth/reset-password', [
            'email'                 => $request->email,
            'token'                 => $request->token,
            'password'              => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if ($response->successful()) {
            return redirect()->route('login')->with('success', $response->json()['message']);
        }

        return back()->withErrors(['email' => $response->json()['message'] ?? 'Gagal meriset password.']);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    /**
     * Sinkronisasi timezone browser ke DB dan session setelah login berhasil.
     *
     * Urutan prioritas sumber timezone:
     *  1. $overrideTimezone  → dikirim dari verifyOtp (disimpan saat form login)
     *  2. _timezone          → hidden input form yang diisi JS sebelum submit
     *  3. Kolom timezone DB  → dari login sebelumnya (jangan timpa dengan UTC)
     *
     * @param Request     $request
     * @param User        $user
     * @param string|null $overrideTimezone  Timezone dari luar (misal: dari session OTP)
     */
    private function syncTimezoneAfterLogin(
        Request $request,
        User $user,
        ?string $overrideTimezone = null
    ): void {
        // Prioritas 1: override dari caller (dipakai saat OTP flow)
        $tz = $overrideTimezone;

        // Prioritas 2: dari hidden input _timezone di form login
        if (! $tz) {
            $tz = $request->input('_timezone');
        }

        // Prioritas 3: dari header X-Timezone (fetch JS — jarang ikut di form submit biasa)
        if (! $tz) {
            $tz = $request->header('X-Timezone');
        }

        // Validasi
        if (! $tz || ! $this->timezoneService->isValid($tz)) {
            // Tidak ada timezone baru yang valid — gunakan yang sudah ada di DB
            // agar session tetap konsisten setelah login
            if ($user->timezone && $this->timezoneService->isValid($user->timezone)) {
                session(['user_timezone' => $user->timezone]);
            }

            return;
        }

        // Simpan ke DB — gunakan direct assignment agar tidak bergantung $fillable
        $user->timezone = $tz;
        $user->save();

        // Simpan ke session baru
        session(['user_timezone' => $tz]);

        Log::debug('[Timezone] Synced after login', [
            'user_id'  => $user->id,
            'timezone' => $tz,
            'source'   => $overrideTimezone ? 'otp_session' : ($request->input('_timezone') ? 'form_input' : 'header'),
        ]);
    }
}