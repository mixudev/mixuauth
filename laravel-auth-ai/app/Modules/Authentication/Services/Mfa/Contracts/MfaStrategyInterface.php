<?php

namespace App\Modules\Authentication\Services\Mfa\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface MfaStrategyInterface
{
    /**
     * Generate verifikasi baru (misal: kirim email OTP).
     *
     * @return array{session_token: string, message: string}
     */
    public function generate(User $user, Request $request, ?int $logId = null): array;

    /**
     * Verifikasi kode yang dimasukkan user.
     */
    public function verify(User $user, string $code, string $sessionToken): bool;

    /**
     * Identifikasi unik untuk strategi ini (misal: 'email', 'totp').
     */
    public function identifier(): string;
}
