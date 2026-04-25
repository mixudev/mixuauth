<?php

namespace App\Modules\Authentication\Services;

use App\Shared\DTO\RiskAssessmentResult;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Security\Models\SecurityNotification;
use App\Models\User;
use App\Modules\Security\Repositories\TrustedDeviceRepository;
use App\Modules\Authentication\Services\BlockingService;
use App\Modules\Authentication\Services\LoginAuditService;
use App\Modules\Authentication\Services\LoginRiskService;
use App\Modules\Authentication\Services\Mfa\MfaManager;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Security\Services\AiRiskClientService;
use App\Modules\Security\Services\DeviceFingerprintService;
use App\Modules\Security\Services\RiskFallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthFlowService
{
    public function __construct(
        private readonly LoginRiskService $riskService,
        private readonly AiRiskClientService $aiClient,
        private readonly RiskFallbackService $fallbackService,
        private readonly LoginAuditService $auditService,
        private readonly TrustedDeviceRepository $trustedDeviceRepo,
        private readonly DeviceFingerprintService $fingerprintService,
        private readonly BlockingService $blockingService,
        private readonly PasswordResetService $passwordResetService,
        private readonly MfaManager $mfaManager,
    ) {}

    public function attemptLogin(Request $request, bool $remember = false): array
    {
        $ip          = $this->fingerprintService->getRealIp($request);
        $fingerprint = $this->fingerprintService->generate($request);

        try {
            if ($this->blockingService->isIpWhitelisted($ip)) {
                return $this->loginWithWhitelistedIp($request, $remember);
            }

            if ($this->blockingService->isIpBlocked($ip)) {
                return $this->error('blocked', 'IP Anda sedang diblokir. Hubungi administrator.', Response::HTTP_FORBIDDEN, 'LOGIN_BLOCKED');
            }

            if ($this->blockingService->isDeviceBlocked($fingerprint)) {
                return $this->error('blocked', 'Perangkat ini tidak diizinkan untuk login.', Response::HTTP_FORBIDDEN, 'DEVICE_BLOCKED');
            }

            $credentials = $request->only('email', 'password');
            $user        = User::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                $this->handleFailedCredentials($request, $credentials['email']);
                return $this->error('invalid_credentials', 'Email atau password yang Anda masukkan salah.', Response::HTTP_UNAUTHORIZED, 'INVALID_CREDENTIALS');
            }

            if (! $user->isActive()) {
                return $this->error('account_inactive', 'Akun Anda telah dinonaktifkan. Hubungi administrator.', Response::HTTP_FORBIDDEN, 'ACCOUNT_INACTIVE');
            }

            if ($this->blockingService->isUserBlocked($user->id)) {
                return $this->error('blocked', 'Akun Anda sedang diblokir sementara. Hubungi administrator.', Response::HTTP_FORBIDDEN, 'USER_BLOCKED');
            }

            if ($user->otp_preference === User::OTP_ALWAYS) {
                return $this->handleMfaDecision($request, $user, new RiskAssessmentResult(
                    riskScore:   0,
                    decision:    'MFA',
                    reasonFlags: ['user_preference_always'],
                    rawResponse: ['source' => 'user_settings', 'enforcement' => 'always_otp'],
                    payload:     []
                ));
            }

            if ($user->otp_preference === User::OTP_DISABLED) {
                return $this->handleAllowDecision($request, $user, new RiskAssessmentResult(
                    riskScore:   0,
                    decision:    'ALLOW',
                    reasonFlags: ['user_preference_disabled'],
                    rawResponse: ['source' => 'user_settings'],
                    payload:     []
                ), $remember);
            }

            if ($this->trustedDeviceRepo->isTrusted($user->id, $fingerprint)) {
                return $this->handleTrustedBypass($request, $user, $remember);
            }

            $riskPayload = $this->riskService->prepareRiskPayload($request, $user);

            try {
                $assessment = $this->aiClient->assess($riskPayload);
            } catch (\RuntimeException $e) {
                Log::channel('security')->error('AI tidak tersedia, fallback aktif', [
                    'error'   => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                $assessment = $this->fallbackService->assess($riskPayload);
            }

            $decision = $assessment->decision;
            if ($decision === 'OTP' && ! config('security.otp.enabled')) {
                $decision = 'ALLOW';
            }

            if ($decision === 'BLOCK') {
                $this->blockingService->handleBlockDecision($user->id, $ip, $fingerprint);
            }

            return match ($decision) {
                'ALLOW'      => $this->handleAllowDecision($request, $user, $assessment, $remember),
                'OTP', 'MFA' => $this->handleMfaDecision($request, $user, $assessment),
                default      => $this->handleBlockDecision($request, $user, $assessment),
            };
        } catch (Throwable $e) {
            Log::channel('security')->critical('Kesalahan sistem fatal saat login', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'ip'        => $ip,
                'email'     => $request->input('email'),
            ]);

            try {
                SecurityNotification::create([
                    'user_id'    => null,
                    'type'       => 'error',
                    'event'      => 'auth.system_error',
                    'title'      => 'Kesalahan Sistem Login',
                    'message'    => 'Terjadi error internal saat login.',
                    'meta'       => [
                        'exception' => get_class($e),
                        'email'     => $request->input('email'),
                    ],
                    'ip_address' => $ip,
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable $notifError) {
                Log::channel('security')->error('Gagal menyimpan notifikasi keamanan saat fatal error.', [
                    'error' => $notifError->getMessage(),
                ]);
            }

            return $this->error('internal_error', 'Terjadi kesalahan sistem saat memproses login Anda. Tim teknis telah diberitahu.', Response::HTTP_INTERNAL_SERVER_ERROR, 'INTERNAL_SYSTEM_ERROR');
        }
    }

    public function verifyMfa(Request $request, string $sessionToken, string $code): array
    {
        $otpRecord = OtpVerification::where('session_token_hash', hash('sha256', $sessionToken))
            ->whereNull('verified_at')
            ->first();

        if (! $otpRecord) {
            return $this->error('invalid_session', 'Sesi verifikasi tidak valid.', Response::HTTP_GONE, 'INVALID_SESSION');
        }

        $user = User::find($otpRecord->user_id);
        if (! $user) {
            return $this->error('invalid_session', 'Sesi verifikasi tidak valid.', Response::HTTP_GONE, 'INVALID_SESSION');
        }

        // [C-03 FIX] Verifikasi device signature secara konsisten
        $storedSignature = (string) $otpRecord->device_fingerprint;
        if (! empty($storedSignature)) {
            $currentSignature = $this->fingerprintService->getDeviceSignature($request);

            if (! hash_equals($storedSignature, $currentSignature)) {
                Log::channel('security')->warning('MFA ditolak: device signature tidak cocok (potensi replay/hijack).', [
                    'user_id'              => $user->id,
                    'session_token_prefix' => substr($sessionToken, 0, 10) . '...',
                    'stored_prefix'        => substr($storedSignature, 0, 8) . '...',
                    'current_prefix'       => substr($currentSignature, 0, 8) . '...',
                    'ip'                   => $this->fingerprintService->getRealIp($request),
                ]);

                // Invalidasi OTP record agar tidak bisa dicoba ulang
                $otpRecord->update(['verified_at' => now()]);

                return $this->error('mfa_device_mismatch', 'Sesi MFA tidak valid untuk perangkat ini.', Response::HTTP_FORBIDDEN, 'MFA_DEVICE_MISMATCH');
            }
        }

        $isValid = $this->mfaManager->verify($user, $code, $sessionToken);
        if (! $isValid) {
            return $this->error('invalid_code', 'Kode verifikasi salah atau sudah kedaluwarsa.', Response::HTTP_UNPROCESSABLE_ENTITY, 'INVALID_CODE');
        }

        $result = $this->completeAuthenticatedSession($request, $user, false, true, $otpRecord->id);

        return [
            'status'      => 'authenticated',
            'http_status' => Response::HTTP_OK,
            'message'     => 'Verifikasi berhasil. Selamat datang!',
            'user'        => $this->serializeUser($result),
        ];
    }

    /**
     * Verifikasi login menggunakan Backup Code.
     */
    public function verifyRecoveryCode(Request $request, string $sessionToken, string $recoveryCode): array
    {
        $otpRecord = OtpVerification::where('session_token_hash', hash('sha256', $sessionToken))
            ->whereNull('verified_at')
            ->first();

        if (! $otpRecord) {
            return $this->error('invalid_session', 'Sesi verifikasi tidak valid.', Response::HTTP_GONE, 'INVALID_SESSION');
        }

        $user = User::find($otpRecord->user_id);
        if (! $user) {
            return $this->error('invalid_session', 'Sesi verifikasi tidak valid.', Response::HTTP_GONE, 'INVALID_SESSION');
        }

        // Gunakan fungsi useBackupCode yang ada di model User (otomatis hapus kode jika cocok)
        if (! $user->useBackupCode($recoveryCode)) {
            return $this->error('invalid_code', 'Kode cadangan tidak valid atau sudah pernah digunakan.', Response::HTTP_UNPROCESSABLE_ENTITY, 'INVALID_CODE');
        }

        // Catat penggunaan kode cadangan di log keamanan
        Log::channel('security')->warning('User login menggunakan Backup Code (Fail-safe triggered)', [
            'user_id' => $user->id,
            'ip'      => $this->fingerprintService->getRealIp($request),
        ]);

        $result = $this->completeAuthenticatedSession($request, $user, false, true, $otpRecord->id);

        return [
            'status'      => 'authenticated',
            'http_status' => Response::HTTP_OK,
            'message'     => 'Berhasil masuk menggunakan kode cadangan. Harap segera periksa aplikasi autentikator Anda.',
            'user'        => $this->serializeUser($result),
        ];
    }

    public function sendResetLink(Request $request, string $email): array
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = $this->passwordResetService->createToken($user->email);
            $user->notify(new \App\Modules\Authentication\Notifications\ResetPasswordNotification($token, $user->email));

            Log::channel('security')->info('Permintaan reset password dikirim', [
                'email' => $user->email,
                'ip'    => $this->fingerprintService->getRealIp($request),
            ]);
        }

        return [
            'status'      => 'ok',
            'http_status' => Response::HTTP_OK,
            'message'     => 'Jika email terdaftar di sistem kami, instruksi reset telah dikirim.',
        ];
    }

    public function validateResetToken(string $email, string $token): array
    {
        return $this->passwordResetService->validateToken($email, $token);
    }

    public function resetPassword(Request $request, string $email, string $token, string $password): array
    {
        $validation = $this->passwordResetService->validateToken($email, $token);

        if (! $validation['success']) {
            return $this->error(
                'invalid_reset_token',
                $validation['reason'] === 'expired' ? 'Link reset password sudah kedaluwarsa.' : 'Link reset password tidak valid.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'INVALID_RESET_TOKEN'
            );
        }

        $user = User::where('email', $email)->firstOrFail();

        // [H-02] Pastikan password baru tidak sama dengan password lama
        if (Hash::check($password, $user->password)) {
            return $this->error(
                'same_password',
                'Password baru tidak boleh sama dengan password sebelumnya.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'SAME_PASSWORD'
            );
        }

        $user->forceFill([
            'password'       => $password,
            'remember_token' => Str::random(60),
        ])->save();

        $this->passwordResetService->deleteToken($user->email);
        $this->revokeUserSessions($user);

        Log::channel('security')->info('Password berhasil direset', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $this->fingerprintService->getRealIp($request),
        ]);

        return [
            'status'      => 'ok',
            'http_status' => Response::HTTP_OK,
            'message'     => 'Password Anda berhasil diperbarui. Silakan login kembali.',
        ];
    }

    public function revokeUserSessions(User $user, ?string $keepSessionId = null): void
    {
        DB::transaction(function () use ($user, $keepSessionId) {
            $user->forceFill([
                'session_version' => $user->session_version + 1,
                'remember_token'  => Str::random(60),
            ])->save();

            if (DB::getSchemaBuilder()->hasTable('sessions')) {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->when($keepSessionId, fn ($query) => $query->where('id', '!=', $keepSessionId))
                    ->delete();
            }
        });
    }

    private function loginWithWhitelistedIp(Request $request, bool $remember): array
    {
        $credentials = $request->only('email', 'password');
        $user        = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->handleFailedCredentials($request, $credentials['email']);
            return $this->error('invalid_credentials', 'Email atau password yang Anda masukkan salah.', Response::HTTP_UNAUTHORIZED, 'INVALID_CREDENTIALS');
        }

        if (! $user->isActive()) {
            return $this->error('account_inactive', 'Akun Anda telah dinonaktifkan.', Response::HTTP_FORBIDDEN, 'ACCOUNT_INACTIVE');
        }

        // [C-04 FIX] IP whitelist HANYA membypass AI risk scoring.
        if ($this->blockingService->isUserBlocked($user->id)) {
            return $this->error('blocked', 'Akun Anda sedang diblokir. Hubungi administrator.', Response::HTTP_FORBIDDEN, 'USER_BLOCKED');
        }

        // Jika user memiliki MFA aktif, tetap wajibkan verifikasi
        if ($user->hasMfaEnabled()) {
            return $this->handleMfaDecision($request, $user, new RiskAssessmentResult(
                riskScore:   0,
                decision:    'MFA',
                reasonFlags: ['ip_whitelist_mfa_enforced'],
                rawResponse: ['source' => 'ip_whitelist'],
                payload:     []
            ));
        }

        return $this->handleAllowDecision($request, $user, new RiskAssessmentResult(
            riskScore:   0,
            decision:    'ALLOW',
            reasonFlags: ['ip_whitelist'],
            rawResponse: ['source' => 'ip_whitelist'],
            payload:     []
        ), $remember);
    }

    private function handleAllowDecision(Request $request, User $user, RiskAssessmentResult $assessment, bool $remember): array
    {
        $this->auditService->recordSuccess($request, $user, $assessment);
        $this->clearFailedAttempts($request, $user->email);
        $user = $this->completeAuthenticatedSession($request, $user, $remember);

        return [
            'status'      => 'authenticated',
            'http_status' => Response::HTTP_OK,
            'message'     => 'Login berhasil. Selamat datang kembali!',
            'user'        => $this->serializeUser($user),
        ];
    }

    private function handleTrustedBypass(Request $request, User $user, bool $remember): array
    {
        $assessment = new RiskAssessmentResult(
            riskScore:   0,
            decision:    'ALLOW',
            reasonFlags: ['trusted_device_bypass'],
            rawResponse: ['source' => 'trusted_device'],
            payload:     []
        );

        return $this->handleAllowDecision($request, $user, $assessment, $remember);
    }

    private function handleMfaDecision(Request $request, User $user, RiskAssessmentResult $assessment): array
    {
        $log = $this->auditService->recordOtpRequired($request, $user, $assessment);

        try {
            $mfaData = $this->mfaManager->initiate($user, $request, $log->id);
        } catch (\RuntimeException $e) {
            return $this->error('mfa_rate_limited', $e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS, 'MFA_RATE_LIMITED');
        }

        return [
            'status'       => 'mfa_required',
            'http_status'  => Response::HTTP_ACCEPTED,
            'message'      => $mfaData['message'],
            'requires_mfa' => true,
            'requires_otp' => true,
            'mfa_type'     => $user->mfa_type ?? 'email',
            'session_token' => $mfaData['session_token'],
            'expires_in'   => config('security.otp.expires_minutes') . ' menit',
        ];
    }

    private function handleBlockDecision(Request $request, User $user, RiskAssessmentResult $assessment): array
    {
        $this->auditService->recordBlocked($request, $user, $assessment);

        return $this->error(
            'blocked',
            'Login tidak dapat dilanjutkan karena aktivitas mencurigakan terdeteksi. Hubungi administrator jika ini adalah kesalahan.',
            Response::HTTP_FORBIDDEN,
            'LOGIN_BLOCKED'
        );
    }

    public function completeAuthenticatedSession(
        Request $request,
        User    $user,
        bool    $remember = false,
        bool    $isOtpFlow = false,
        ?int    $logId = null
    ): User {
        Auth::login($user, $remember);

        if ($request->hasSession()) {
            $request->session()->regenerate();
            
            // [S-01 FIX] Ambil fingerprint saat ini
            $fingerprint = $this->fingerprintService->generate($request);
            
            // [M-02 FIX] Untuk perangkat baru, rotate cookie UUID setelah login
            $isNewDevice = ! $this->trustedDeviceRepo->isTrusted($user->id, $fingerprint);
            
            if ($isNewDevice) {
                $newDeviceId = $this->fingerprintService->generateNewDeviceId();
                $cookieMinutes = (int) config('security.session.trusted_device_cookie_minutes', 60 * 24 * 30);

                \Illuminate\Support\Facades\Cookie::queue(
                    \App\Modules\Security\Middleware\DeviceIdentifierMiddleware::COOKIE_NAME,
                    $newDeviceId,
                    $cookieMinutes,
                    '/',
                    null,
                    $request->isSecure(),
                    true,   // httpOnly
                    false,
                    'Lax'
                );

                // Update fingerprint untuk sesi agar sesuai dengan cookie baru yang akan dikirim
                $fingerprint = hash('sha256', $newDeviceId);
            }

            $request->session()->put('auth_device_fingerprint', $fingerprint);
            $request->session()->put('auth_session_version', (int) $user->session_version);
        }

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

        $this->trustedDeviceRepo->trustDevice($user->id, $request);

        return $user->fresh();
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

    private function serializeUser(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    private function error(string $status, string $message, int $httpStatus, string $errorCode): array
    {
        return [
            'status'      => $status,
            'http_status' => $httpStatus,
            'message'     => $message,
            'error_code'  => $errorCode,
        ];
    }
}
