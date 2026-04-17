<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Repositories\TrustedDeviceRepository;
use App\Services\Security\AiRiskClientService;
use App\Services\Auth\BlockingService;
use App\Services\Security\DeviceFingerprintService;
use App\Services\Auth\LoginAuditService;
use App\Services\Auth\LoginRiskService;
use App\Services\Auth\OtpService;
use App\Services\Auth\PasswordResetService;
use App\Services\Security\RiskFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SecurityNotification;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginRiskService         $riskService,
        private readonly AiRiskClientService      $aiClient,
        private readonly RiskFallbackService      $fallbackService,
        private readonly OtpService               $otpService,
        private readonly LoginAuditService        $auditService,
        private readonly TrustedDeviceRepository  $trustedDeviceRepo,
        private readonly DeviceFingerprintService $fingerprintService,
        private readonly BlockingService          $blockingService,
        private readonly PasswordResetService     $passwordResetService,
        private readonly \App\Services\Auth\Mfa\MfaManager $mfaManager,
    ) {}

    /**
     * POST /auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $ip          = $this->fingerprintService->getRealIp($request);
        $fingerprint = $this->fingerprintService->generate($request);

        try {
            // ── Langkah 0: Cek whitelist / blacklist sebelum apapun ──────────
            if ($this->blockingService->isIpWhitelisted($ip)) {
                Log::channel('security')->info('Login dari IP whitelist, skip AI', ['ip' => $ip]);
                return $this->loginWithWhitelistedIp($request);
            }

            if ($this->blockingService->isIpBlocked($ip)) {
                return $this->blockedResponse('IP Anda sedang diblokir. Hubungi administrator.');
            }

            if ($this->blockingService->isDeviceBlocked($fingerprint)) {
                return $this->blockedResponse('Perangkat ini tidak diizinkan untuk login.');
            }

            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                $this->handleFailedCredentials($request, $credentials['email']);

                return response()->json([
                    'message'    => 'Email atau password yang Anda masukkan salah.',
                    'error_code' => 'INVALID_CREDENTIALS',
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (! $user->isActive()) {
                return response()->json([
                    'message'    => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                    'error_code' => 'ACCOUNT_INACTIVE',
                ], Response::HTTP_FORBIDDEN);
            }

            if ($this->blockingService->isUserBlocked($user->id)) {
                Log::channel('security')->warning('Login ditolak: user diblokir', [
                    'user_id' => $user->id,
                    'ip'      => $ip,
                ]);
                return $this->blockedResponse('Akun Anda sedang diblokir sementara. Hubungi administrator.');
            }

            // ── Langkah: Cek Preferensi Keamanan User (OTP) ──
            if ($user->otp_preference === User::OTP_ALWAYS) {
                Log::channel('security')->info('MFA dipaksa oleh pengaturan user (Always OTP).', ['user_id' => $user->id]);
                return $this->handleMfaDecision($request, $user, new \App\DTOs\RiskAssessmentResult(
                    riskScore: 100,
                    decision: 'MFA',
                    reasonFlags: ['user_preference_always'],
                    rawResponse: ['source' => 'user_settings'],
                    payload: []
                ));
            }

            if ($user->otp_preference === User::OTP_DISABLED) {
                Log::channel('security')->info('OTP diabaikan oleh pengaturan user (Disabled OTP).', ['user_id' => $user->id]);
                return $this->handleAllowDecision($request, $user, (object)[
                    'riskScore'   => 0.0, 
                    'decision'    => 'ALLOW', 
                    'reasonFlags' => ['user_preference_disabled'],
                    'payload'     => []
                ]);
            }

            // ── Langkah Baru: Fast-Track Perangkat Terpercaya (Whitelist Fingerprint) ──
            // Jika perangkat sudah pernah diverifikasi (Trusted Device) dan masih aktif,
            // kita langsung izinkan login tanpa perlu bertanya pada AI (Skip AI).
            if ($this->trustedDeviceRepo->isTrusted($user->id, $fingerprint)) {
                Log::channel('security')->info('Fast-Track: Login dari perangkat terpercaya, bypass AI.', [
                    'user_id'     => $user->id,
                    'fingerprint' => substr($fingerprint, 0, 8) . '...',
                    'ip'          => $request->ip(),
                ]);

                return $this->handleTrustedBypass($request, $user);
            }

            $riskPayload = $this->riskService->prepareRiskPayload($request, $user);

            try {
                $assessment = $this->aiClient->sendToFastApi($riskPayload);
            } catch (\RuntimeException $e) {
                Log::channel('security')->error('AI tidak tersedia, fallback aktif', [
                    'error'   => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                $assessment = $this->fallbackService->assess($riskPayload);
            }

            $decision = $assessment->decision;
            if ($decision === 'OTP' && !config('security.otp.enabled')) {
                $decision = 'ALLOW';
            }

            if ($decision === 'BLOCK') {
                $this->blockingService->handleBlockDecision($user->id, $ip, $fingerprint);
            }

            return match ($decision) {
                'ALLOW' => $this->handleAllowDecision($request, $user, $assessment),
                'OTP'   => $this->handleMfaDecision($request, $user, $assessment),
                'MFA'   => $this->handleMfaDecision($request, $user, $assessment),
                'BLOCK' => $this->handleBlockDecision($request, $user, $assessment),
                default => $this->handleBlockDecision($request, $user, $assessment),
            };

        } catch (Throwable $e) {
            // Log ke file standar
            Log::channel('security')->critical('Kesalahan sistem fatal saat login', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'ip'        => $ip,
                'email'     => $request->email,
            ]);

            // Log ke DATABASE agar User bisa melihat di dashboard admin
            SecurityNotification::create([
                'user_id'    => null, // System-wide alert
                'type'       => 'error',
                'event'      => 'auth.system_error',
                'title'      => 'Kesalahan Sistem Login',
                'message'    => "Terjadi error internal: " . $e->getMessage(),
                'meta'       => [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'email'     => $request->email,
                    'trace'     => substr($e->getTraceAsString(), 0, 1000) // Ambil cuplikan saja
                ],
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message'    => 'Terjadi kesalahan sistem saat memproses login Anda. Tim teknis telah diberitahu.',
                'error_code' => 'INTERNAL_SYSTEM_ERROR',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /auth/mfa/verify
     */
    public function verifyMfa(Request $request): JsonResponse
    {
        $request->validate([
            'session_token' => 'required|string',
            'code'          => 'required|string',
        ]);

        $user = null;
        // Cari user berdasarkan session token di tabel otp_verifications
        $otpRecord = \App\Models\OtpVerification::where('session_token', $request->session_token)->first();
        if ($otpRecord) {
            $user = User::find($otpRecord->user_id);
        }

        if (!$user) {
            return response()->json([
                'message'    => 'Sesi verifikasi tidak valid.',
                'error_code' => 'INVALID_SESSION',
            ], Response::HTTP_GONE);
        }

        // ── RELAXED HARDENING: Validasi Perangkat (Signature) ──────────
        $currentSignature = $this->fingerprintService->getDeviceSignature($request);
        $currentIp        = $this->fingerprintService->getRealIp($request);

        if ($otpRecord->device_fingerprint !== $currentSignature) {
            Log::channel('security')->warning('MFA Binding Anomalie: Sesi MFA dicoba dari tanda tangan perangkat berbeda.', [
                'user_id'             => $user->id,
                'original_ip'         => $otpRecord->ip_address,
                'current_ip'          => $currentIp,
                'original_sig'        => $otpRecord->device_fingerprint,
                'current_sig'         => $currentSignature,
                'session_token_start' => substr($request->session_token, 0, 10),
            ]);

            // NOTE: Kita hanya log peringatan saja (tidak blokir) untuk menghindari False Positive 
            // akibat konfigurasi Proxy/Docker yang tidak stabil.
        }

        // Pantau jika IP berubah tapi perangkat sama (Log saja, jangan blokir)
        if ($otpRecord->ip_address !== $currentIp) {
            Log::channel('security')->info('MFA IP Shift: User beralih IP saat proses MFA (Normal for mobile).', [
                'user_id' => $user->id,
                'old_ip'  => $otpRecord->ip_address,
                'new_ip'  => $currentIp,
            ]);
        }

        $isValid = $this->mfaManager->verify($user, $request->code, $request->session_token);

        if (!$isValid) {
            return response()->json([
                'message'    => 'Kode verifikasi salah atau sudah kedaluwarsa.',
                'error_code' => 'INVALID_CODE',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->finalizeLogin($request, $user, true);
    }

    /**
     * POST /auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $userId = Auth::id();
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Log::channel('security')->info('Pengguna logout', [
            'user_id'    => $userId,
            'ip_address' => $this->fingerprintService->getRealIp($request),
        ]);

        return response()->json(['message' => 'Anda berhasil keluar dari sistem.']);
    }

    /**
     * POST /auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Keamanan: Jangan beri tahu jika email tidak ada (mencegah enumerasi)
        if ($user) {
            $token = $this->passwordResetService->createToken($user->email);
            $user->notify(new \App\Notifications\ResetPasswordNotification($token, $user->email));
            
            Log::channel('security')->info('Permintaan reset password dikirim', [
                'email' => $user->email,
                'ip'    => $this->fingerprintService->getRealIp($request)
            ]);
        }

        return response()->json([
            'message' => 'Jika email terdaftar di sistem kami, instruksi reset telah dikirim.',
        ]);
    }

    /**
     * POST /auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Validasi Token
        $validation = $this->passwordResetService->validateToken($request->email, $request->token);

        if (! $validation['success']) {
            $message = match ($validation['reason']) {
                'expired' => 'Link reset password sudah kedaluwarsa.',
                default   => 'Link reset password tidak valid.',
            };

            return response()->json([
                'message'    => $message,
                'error_code' => 'INVALID_RESET_TOKEN',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2. Update Password
        $user = User::where('email', $request->email)->firstOrFail();
        
        // Laravel 11 otomatis hash karena cast 'hashed' di Model User
        $user->update(['password' => $request->password]);

        // 3. Invalidasi Token & Logout Sesi Lain
        $this->passwordResetService->deleteToken($user->email);

        Log::channel('security')->info('Password berhasil direset', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $this->fingerprintService->getRealIp($request)
        ]);

        return response()->json([
            'message' => 'Password Anda berhasil diperbarui. Silakan login kembali.',
        ]);
    }

    /**
     * GET /auth/reset-password/validate
     */
    public function validateResetToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $validation = $this->passwordResetService->validateToken($request->email, $request->token);

        if (! $validation['success']) {
            return response()->json([
                'valid'   => false,
                'message' => match ($validation['reason']) {
                    'expired' => 'Link reset password sudah kedaluwarsa.',
                    default   => 'Link reset password tidak valid.',
                },
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['valid' => true]);
    }

    // -----------------------------------------------------------------------
    // Private: Whitelist Flow
    // -----------------------------------------------------------------------

    private function loginWithWhitelistedIp(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->handleFailedCredentials($request, $credentials['email']);
            return response()->json([
                'message'    => 'Email atau password yang Anda masukkan salah.',
                'error_code' => 'INVALID_CREDENTIALS',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->isActive()) {
            return response()->json([
                'message'    => 'Akun Anda telah dinonaktifkan.',
                'error_code' => 'ACCOUNT_INACTIVE',
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->finalizeLogin($request, $user);
    }

    // -----------------------------------------------------------------------
    // Private: Penanganan Keputusan AI
    // -----------------------------------------------------------------------

    private function handleAllowDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordSuccess($request, $user, $assessment);
        $this->clearFailedAttempts($request, $user->email);
        return $this->finalizeLogin($request, $user);
    }

    /**
     * Penanganan khusus untuk login yang melewati jalur 'Fast-Track' perangkat terpercaya.
     */
    private function handleTrustedBypass(Request $request, User $user): JsonResponse
    {
        // Buat objek dummy assessment menggunakan DTO resmi untuk logging audit
        $assessment = new \App\DTOs\RiskAssessmentResult(
            riskScore: 0,
            decision: 'ALLOW',
            reasonFlags: ['trusted_device_bypass'],
            rawResponse: ['source' => 'fast_track_whitelist'],
            payload: []
        );

        $this->auditService->recordSuccess($request, $user, $assessment);
        $this->clearFailedAttempts($request, $user->email);
        
        // Tandai sesi bahwa login ini melewati jalur Fast-Track
        session(['login_bypass_ai' => true]);

        return $this->finalizeLogin($request, $user);
    }

    private function handleMfaDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $log = $this->auditService->recordOtpRequired($request, $user, $assessment);

        $mfaData = $this->mfaManager->initiate($user, $request);

        return response()->json([
            'message'        => $mfaData['message'],
            'requires_mfa'   => true,
            'requires_otp'   => true, // Legacy support
            'mfa_type'       => $user->mfa_type ?? 'email',
            'session_token'  => $mfaData['session_token'],
            'expires_in'     => config('security.otp.expires_minutes') . ' menit',
        ], Response::HTTP_ACCEPTED);
    }

    private function handleBlockDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordBlocked($request, $user, $assessment);
        return $this->blockedResponse();
    }

    private function blockedResponse(string $message = null): JsonResponse
    {
        return response()->json([
            'message'    => $message ?? 'Login tidak dapat dilanjutkan karena aktivitas mencurigakan terdeteksi. Hubungi administrator jika ini adalah kesalahan.',
            'error_code' => 'LOGIN_BLOCKED',
        ], Response::HTTP_FORBIDDEN);
    }

    // -----------------------------------------------------------------------
    // Private: Helper Methods
    // -----------------------------------------------------------------------

    private function finalizeLogin(Request $request, User $user, bool $isOtpFlow = false, ?int $logId = null): JsonResponse
    {
        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->put('auth_device_fingerprint', $this->fingerprintService->generate($request));
        }

        // Catat success di audit log hanyak jika login via OTP (untuk flow biasa sudah dicatat di handleAllowDecision)
        if ($isOtpFlow) {
            $this->auditService->recordOtpSuccess($request, $user, $logId);
        }

        $ip = $this->fingerprintService->getRealIp($request);
        $user->recordLogin(
            $ip,
            $this->fingerprintService->getCountry($ip),
            $request->userAgent(),
            $this->fingerprintService->buildDeviceLabel($request)
        );

        // Ambil ID perangkat yang sudah diinisialisasi oleh middleware
        $deviceId = $request->cookie('device_trust_id');
        
        // Daftarkan perangkat sebagai terpercaya
        if ($deviceId) {
            $this->trustedDeviceRepo->trustDevice($user->id, $request, $deviceId);
        }

        $response = response()->json([
            'message' => 'Login berhasil. Selamat datang kembali!',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);

        // Perbarui masa berlaku cookie (30 hari) dengan atribut keamanan tinggi
        return $response->withCookie(cookie(
            'device_trust_id', 
            $deviceId, 
            60 * 24 * 30, // 30 hari
            '/', 
            null, 
            $request->isSecure(), // Secure jika HTTPS
            true, // HttpOnly
            false, // Raw (false karena dikecualikan dari enkripsi)
            'Lax' // SameSite
        ));
    }

    private function handleFailedCredentials(Request $request, string $email): void
    {
        $this->auditService->recordFailedPassword($request, $email);

        $user = User::where('email', $email)->first();
        if ($user) {
            $cacheKey = "failed_attempts:{$user->id}:{$this->fingerprintService->getRealIp($request)}";
            Cache::increment($cacheKey);
            Cache::put($cacheKey, Cache::get($cacheKey, 1), now()->addMinutes(30));
        }
    }

    private function clearFailedAttempts(Request $request, string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            Cache::forget("failed_attempts:{$user->id}:{$this->fingerprintService->getRealIp($request)}");
        }
    }

    private function dispatchOtpNotification(User $user, string $otpCode): void
    {
        $channel = config('security.otp.channel', 'email');
        if ($channel === 'email') {
            $user->notify(new \App\Notifications\OtpCodeNotification($otpCode));
        }
    }
}