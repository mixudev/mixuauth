<?php

namespace App\Services\Auth\Mfa\Strategies;

use App\Models\User;
use App\Models\OtpVerification;
use App\Services\Auth\Mfa\Contracts\MfaStrategyInterface;
use App\Services\Auth\OtpService;
use App\Services\Security\DeviceFingerprintService;
use Illuminate\Http\Request;

class EmailMfaStrategy implements MfaStrategyInterface
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function generate(User $user, Request $request): array
    {
        $ip = $this->fingerprintService->getRealIp($request);
        $fingerprint = $this->fingerprintService->getDeviceSignature($request);

        $otpData = $this->otpService->generateOtp($user, $ip, $fingerprint);

        // Kirim notifikasi email
        $user->notify(new \App\Notifications\OtpCodeNotification($otpData['otp_code']));

        return [
            'session_token' => $otpData['session_token'],
            'message'       => 'Kode verifikasi telah dikirim ke email Anda.',
        ];
    }

    public function verify(User $user, string $code, string $sessionToken): bool
    {
        $result = $this->otpService->verifyOtp($sessionToken, $code);
        return $result['success'];
    }

    public function identifier(): string
    {
        return 'email';
    }
}
