<?php

namespace App\Modules\Authentication\Services\Mfa\Strategies;

use App\Models\User;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Authentication\Services\Mfa\Contracts\MfaStrategyInterface;
use App\Modules\Authentication\Services\OtpService;
use App\Modules\Security\Services\DeviceFingerprintService;
use Illuminate\Http\Request;

class EmailMfaStrategy implements MfaStrategyInterface
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function generate(User $user, Request $request, ?int $logId = null): array
    {
        $ip          = $this->fingerprintService->getRealIp($request);
        $fingerprint = $this->fingerprintService->getDeviceSignature($request);

        $otpData = $this->otpService->generateOtp($user, $ip, $fingerprint, $logId);

        // Kirim notifikasi email
        $user->notify(new \App\Modules\Authentication\Notifications\OtpCodeNotification($otpData['otp_code']));

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
