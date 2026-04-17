<?php

namespace App\Services\Auth\Mfa\Strategies;

use App\Models\User;
use App\Models\OtpVerification;
use App\Services\Auth\Mfa\Contracts\MfaStrategyInterface;
use App\Services\Security\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Illuminate\Support\Str;

class TotpMfaStrategy implements MfaStrategyInterface
{
    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function generate(User $user, Request $request): array
    {
        // Untuk TOTP, kita tidak mengirim kode, tetapi kita buat record sesi
        // untuk melacak percobaan verifikasi (rate limiting).
        $sessionToken = Str::random(64);
        
        OtpVerification::create([
            'user_id'            => $user->id,
            'token'              => 'TOTP_SESSION', // Placeholder
            'session_token'      => $sessionToken,
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
        $otpRecord = OtpVerification::where('session_token', $sessionToken)
            ->whereNull('verified_at')
            ->first();

        if (!$otpRecord || $otpRecord->isExpired() || $otpRecord->isExhausted()) {
            return false;
        }

        $otpRecord->incrementAttempts();

        // 1. Coba verifikasi kode TOTP standar
        $secret = Crypt::decryptString($user->totp_secret);
        $isValid = Google2FA::verifyKey($secret, $code);

        // 2. Jika gagal, coba verifikasi sebagai Backup Code
        if (!$isValid && strlen($code) >= 8) { // Backup code biasanya lebih panjang
            $isValid = $user->useBackupCode(strtoupper($code));
            
            if ($isValid) {
                \Illuminate\Support\Facades\Log::channel('security')->info('Backup code digunakan untuk login', [
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
