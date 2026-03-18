<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class WebAuthController extends Controller
{
    protected string $apiBase;

    public function __construct()
    {
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

        if ($response->status() === 202 && isset($data['requires_otp'])) {
            // Perlu OTP
            session([
                'otp_session_token' => $data['session_token'],
                'otp_expires_in'    => $data['expires_in'],
                'otp_email'         => $request->email,
            ]);
            return redirect()->route('otp.verify')
                ->with('info', $data['message']);
        }

        if ($response->successful() && isset($data['user'])) {
            // Login langsung berhasil
            $user = User::find($data['user']['id']);
            if ($user) {
                Auth::login($user, $request->boolean('remember'));
            }
            return redirect()->route('dashboard')
                ->with('success', $data['message']);
        }

        if ($response->status() === 403) {
            return back()->withErrors(['email' => $data['message'] ?? 'Login diblokir karena aktivitas mencurigakan.']);
        }

        if ($response->status() === 429) {
            $retryAfter = $data['retry_after'] ?? 60;
            return back()
                ->withInput($request->only('email'))
                ->with('rate_limited', true)
                ->with('retry_after', $retryAfter)
                ->withErrors(['email' => $data['message'] ?? 'Terlalu banyak percobaan.']);
        }   
        
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $data['message'] ?? 'Email atau password salah.']);
    }

    public function showOtp()
    {
        if (!session('otp_session_token')) {
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
            session()->forget(['otp_session_token', 'otp_expires_in', 'otp_email']);
            $user = User::find($data['user']['id']);
            if ($user) {
                Auth::login($user);
            }
            return redirect()->route('dashboard')
                ->with('success', 'Verifikasi berhasil. Selamat datang!');
        }

        return back()->withErrors(['otp_code' => $data['message'] ?? 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
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
}
