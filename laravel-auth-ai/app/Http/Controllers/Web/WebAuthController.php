<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TimezoneService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebAuthController extends Controller
{
    protected string $apiBase;

    // ── FIX BUG #1: inject TimezoneService lewat constructor ──────────────
    public function __construct(
        private readonly TimezoneService $timezoneService,
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

        $response = Http::withHeaders([
            'Accept'          => 'application/json',
            'X-Forwarded-For' => $request->ip(),
            'User-Agent'      => $request->userAgent(),
        ])->asForm()->post($this->apiBase . '/auth/login', [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $data = $response->json();

        // ── OTP required ──────────────────────────────────────────────────
        if ($response->status() === 202 && isset($data['requires_otp'])) {
            // Simpan timezone ke session OTP agar bisa dipakai setelah verify
            // _timezone dikirim dari hidden input form (lihat fix di blade)
            session([
                'otp_session_token' => $data['session_token'],
                'otp_expires_in'    => $data['expires_in'],
                'otp_email'         => $request->email,
                'otp_timezone'      => $request->input('_timezone'), // ← simpan untuk dipakai di verifyOtp
            ]);

            return redirect()->route('otp.verify')
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

            return redirect()->route('dashboard')
                ->with('success', $data['message']);
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

    public function showOtp()
    {
        if (! session('otp_session_token')) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post($this->apiBase . '/auth/otp/verify', [
            'session_token' => session('otp_session_token'),
            'otp_code'      => $request->otp_code,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['user'])) {
            $user = User::find($data['user']['id']);

            if ($user) {
                Auth::login($user);

                // ── FIX BUG #3: sync timezone setelah OTP berhasil ────────
                // Ambil timezone dari session yang disimpan saat form login
                // (karena OTP form tidak punya hidden input timezone)
                $timezoneFromSession = session('otp_timezone');
                $this->syncTimezoneAfterLogin($request, $user, $timezoneFromSession);
            }

            // Hapus semua data sesi OTP
            session()->forget([
                'otp_session_token',
                'otp_expires_in',
                'otp_email',
                'otp_timezone',
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Verifikasi berhasil. Selamat datang!');
        }

        return back()->withErrors([
            'otp_code' => $data['message'] ?? 'Kode OTP tidak valid atau sudah kedaluwarsa.',
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