<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\WelcomeSocialUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Modules\Authentication\Services\AuthFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Mail\SocialAccountLinked;
use Exception;

use App\Modules\Timezone\Services\TimezoneService;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly AuthFlowService $authFlowService,
        private readonly TimezoneService $timezoneService
    ) {}

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle(Request $request)
    {
        if ($request->has('tz')) {
            session(['temp_timezone' => $request->tz]);
        }
        
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->route('login')->with('error', 'Gagal login via Google. Silakan coba lagi.');
        }

        // Cari user berdasarkan google_id
        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
            // Update token jika perlu
            $user->update([
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            $this->authFlowService->completeAuthenticatedSession($request, $user);
            
            // Sync timezone jika tersedia di session
            if (session()->has('temp_timezone')) {
                $user->update(['timezone' => session('temp_timezone')]);
                session(['user_timezone' => session('temp_timezone')]);
            }

            return redirect()->route('dashboard');
        }

        // Jika tidak ada google_id, cari berdasarkan email
        $existingUser = User::where('email', $googleUser->email)->first();

        if ($existingUser) {
            // Link akun google ke user yang sudah ada
            $existingUser->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            // Kirim email notifikasi bahwa login Google telah diaktifkan (tanpa kirim password baru)
            try {
                Mail::to($existingUser->email)->send(new SocialAccountLinked($existingUser->name, $existingUser->email));
            } catch (Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim email linked account ke ' . $existingUser->email . ': ' . $e->getMessage());
            }

            $this->authFlowService->completeAuthenticatedSession($request, $existingUser);

            // Sync timezone jika tersedia di session
            if (session()->has('temp_timezone')) {
                $existingUser->update(['timezone' => session('temp_timezone')]);
                session(['user_timezone' => session('temp_timezone')]);
            }

            return redirect()->route('dashboard');
        }

        // Registrasi otomatis untuk user baru
        $randomPassword = Str::random(16);
        
        $newUser = User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
            'password' => $randomPassword, // [FIX] Hapus Hash::make karena Model sudah punya cast 'hashed'
            'email_verified_at' => now(), // Google emails are verified
            'is_active' => true,
            'session_version' => 0, // Penting untuk sistem keamanan sesi
        ]);

        // Berikan role default 'user' agar bisa mengakses dashboard dasar
        $newUser->assignRole('user');

        // Sync timezone jika tersedia di session
        if (session()->has('temp_timezone')) {
            $newUser->update(['timezone' => session('temp_timezone')]);
            session(['user_timezone' => session('temp_timezone')]);
        }

        // Generate Signed URLs untuk keamanan ekstra (Magic Links)
        $expiresAt = now()->addHour();
        
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            $expiresAt,
            ['token' => Str::random(60), 'email' => $newUser->email]
        );

        $magicLoginUrl = URL::temporarySignedRoute(
            'auth.magic_login',
            $expiresAt,
            ['user_id' => $newUser->id]
        );

        // Kirim email kredensial ke user baru dengan aman
        try {
            Mail::to($newUser->email)->send(new WelcomeSocialUser(
                $newUser->name, 
                $newUser->email, 
                $randomPassword,
                $resetUrl,
                $magicLoginUrl,
                $this->timezoneService->format($expiresAt, 'H:i')
            ));
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email welcome ke ' . $newUser->email . ': ' . $e->getMessage());
        }

        $this->authFlowService->completeAuthenticatedSession($request, $newUser->fresh());

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil dibuat. Silakan cek email untuk detail login Anda.');
    }

    /**
     * Handle magic login from signed URL.
     */
    public function magicLogin(Request $request)
    {
        // 1. Validasi Signature & Expiration
        if (! $request->hasValidSignature()) {
            return view('auth.reset-link-expired', [
                'message' => 'Link akses cepat ini tidak valid atau sudah kadaluarsa.'
            ]);
        }

        // 2. Validasi One-Time Use (Cek apakah sudah pernah digunakan)
        // Kita gunakan hash signature sebagai key di cache
        $signature = $request->query('signature');
        $cacheKey  = 'magic_link_used:' . sha1($signature);

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return view('auth.reset-link-expired', [
                'message' => 'Link ini sudah pernah digunakan sebelumnya. Untuk alasan keamanan, link akses cepat hanya berlaku satu kali.'
            ]);
        }

        $user = User::findOrFail($request->user_id);
        
        if (! $user->isActive()) {
            return redirect()->route('login')->with('error', 'Akun ini sedang tidak aktif.');
        }

        // 3. Tandai link sebagai sudah digunakan (simpan di cache selama 1 jam sesuai expiry link)
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHour());

        $this->authFlowService->completeAuthenticatedSession($request, $user);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk menggunakan link akses cepat.');
    }
}
