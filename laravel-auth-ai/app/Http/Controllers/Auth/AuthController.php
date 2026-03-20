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
use App\Services\Security\RiskFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

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
        private readonly BlockingService          $blockingService,   // ← tambah
    ) {}

    /**
     * POST /auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $ip          = $request->ip();
        $fingerprint = $this->fingerprintService->generate($request);

        // ── Langkah 0: Cek whitelist / blacklist sebelum apapun ──────────
        if ($this->blockingService->isIpWhitelisted($ip)) {
            Log::channel('security')->info('Login dari IP whitelist, skip AI', ['ip' => $ip]);
            // Lanjut ke validasi kredensial, nanti finalizeLogin langsung tanpa AI
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

        // Cek user block setelah kredensial valid
        if ($this->blockingService->isUserBlocked($user->id)) {
            Log::channel('security')->warning('Login ditolak: user diblokir', [
                'user_id' => $user->id,
                'ip'      => $ip,
            ]);
            return $this->blockedResponse('Akun Anda sedang diblokir sementara. Hubungi administrator.');
        }

        // ── Langkah berikutnya: AI Risk Assessment ────────────────────────
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

        // Jika BLOCK → trigger auto-block sistem
        if ($decision === 'BLOCK') {
            $this->blockingService->handleBlockDecision($user->id, $ip, $fingerprint);
        }

        return match ($decision) {
            'ALLOW' => $this->handleAllowDecision($request, $user, $assessment),
            'OTP'   => $this->handleOtpDecision($request, $user, $assessment),
            'BLOCK' => $this->handleBlockDecision($request, $user, $assessment),
            default => $this->handleBlockDecision($request, $user, $assessment),
        };
    }

    /**
     * POST /auth/otp/verify
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOtp(
            $request->input('session_token'),
            $request->input('otp_code')
        );

        if (! $result['success']) {
            $message = match ($result['reason']) {
                'expired'               => 'Kode OTP sudah kedaluwarsa. Silakan login ulang.',
                'max_attempts_exceeded' => 'Batas percobaan OTP terlampaui. Silakan login ulang.',
                'invalid_session'       => 'Sesi OTP tidak valid atau sudah digunakan.',
                default                 => 'Kode OTP yang Anda masukkan salah.',
            };

            $statusCode = in_array($result['reason'], ['expired', 'max_attempts_exceeded', 'invalid_session'])
                ? Response::HTTP_GONE
                : Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json([
                'message'    => $message,
                'error_code' => strtoupper($result['reason']),
            ], $statusCode);
        }

        $user = User::findOrFail($result['user_id']);
        return $this->finalizeLogin($request, $user);
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
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Anda berhasil keluar dari sistem.']);
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

    private function handleOtpDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordOtpRequired($request, $user, $assessment);

        $otpData = $this->otpService->generateOtp(
            $user,
            $request->ip(),
            $this->fingerprintService->generate($request)
        );

        $this->dispatchOtpNotification($user, $otpData['otp_code']);

        return response()->json([
            'message'       => 'Kode verifikasi telah dikirimkan.',
            'requires_otp'  => true,
            'session_token' => $otpData['session_token'],
            'expires_in'    => config('security.otp.expires_minutes') . ' menit',
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

    private function finalizeLogin(Request $request, User $user): JsonResponse
    {
        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->put('auth_device_fingerprint', $this->fingerprintService->generate($request));
        }

        $user->recordLogin($request->ip());
        $this->trustedDeviceRepo->trustDevice($user->id, $request);

        return response()->json([
            'message' => 'Login berhasil. Selamat datang kembali!',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    private function handleFailedCredentials(Request $request, string $email): void
    {
        $this->auditService->recordFailedPassword($request, $email);

        $user = User::where('email', $email)->first();
        if ($user) {
            $cacheKey = "failed_attempts:{$user->id}:{$request->ip()}";
            Cache::increment($cacheKey);
            Cache::put($cacheKey, Cache::get($cacheKey, 1), now()->addMinutes(30));
        }
    }

    private function clearFailedAttempts(Request $request, string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            Cache::forget("failed_attempts:{$user->id}:{$request->ip()}");
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