<?php

namespace App\Modules\Authentication\Services\Mfa\Strategies;

use App\Models\User;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Authentication\Services\Mfa\Contracts\MfaStrategyInterface;
use App\Modules\Security\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Illuminate\Support\Str;

class TotpMfaStrategy implements MfaStrategyInterface
{
    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function generate(User $user, Request $request, ?int $logId = null): array
    {
        $existing = OtpVerification::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($existing && $existing->created_at && $existing->created_at->diffInSeconds(now()) < (int) config('security.otp.cooldown_seconds', 60)) {
            throw new \RuntimeException('Terlalu sering meminta verifikasi MFA. Silakan tunggu sebentar.');
        }

        // Batalkan semua sesi MFA aktif sebelumnya untuk mengurangi surface area token.
        OtpVerification::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['verified_at' => now()]);

        $sessionToken     = Str::random(64);
        $sessionTokenHash = hash('sha256', $sessionToken);

        OtpVerification::create([
            'user_id'            => $user->id,
            'token'              => 'TOTP_SESSION', // Placeholder
            'session_token_hash' => $sessionTokenHash,
            'ip_address'         => $this->fingerprintService->getRealIp($request),
            'device_fingerprint' => $this->fingerprintService->getDeviceSignature($request),
            'expires_at'         => now()->addMinutes(10),
            'attempts'           => 0,
        ]);

        return [
            'session_token' => $sessionToken,
            'message'       => 'Silakan masukkan kode dari aplikasi Authenticator Anda.',
        ];
    }

    public function verify(User $user, string $code, string $sessionToken): bool
    {
        $otpRecord = OtpVerification::where('session_token_hash', hash('sha256', $sessionToken))
            ->whereNull('verified_at')
            ->first();

        if (!$otpRecord || $otpRecord->isExpired() || $otpRecord->isExhausted()) {
            return false;
        }

        $otpRecord->incrementAttempts();

        // 1. Coba verifikasi kode TOTP standar
        // [H-04 FIX] totp_secret sudah di-decrypt otomatis oleh Eloquent 'encrypted' cast
        $secret  = $user->totp_secret; // ← sudah plaintext berkat cast 'encrypted'
        $isValid = Google2FA::verifyKey($secret, $code, 0);

        // 2. Jika gagal, coba verifikasi sebagai Backup Code
        if (!$isValid && strlen($code) >= 8) { // Backup code biasanya lebih panjang
            $isValid = $user->useBackupCode(strtoupper($code));

            if ($isValid) {
                Log::channel('security')->info('Backup code digunakan untuk login', [
                    'user_id' => $user->id,
                    'ip'      => $otpRecord->ip_address,
                ]);
            }
        }

        if ($isValid) {
            $otpRecord->markAsVerified();
        }

        return $isValid;
    }

    public function identifier(): string
    {
        return 'totp';
    }
}
