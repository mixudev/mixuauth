<?php

namespace App\Services\Auth;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpService
{
    /*
    |--------------------------------------------------------------------------
    | Layanan manajemen OTP untuk verifikasi dua langkah.
    |
    | Kode OTP mentah tidak pernah disimpan ke database.
    | Hanya hash Bcrypt yang tersimpan.
    |--------------------------------------------------------------------------
    */

    /**
     * Buat entri OTP baru dan kembalikan kode mentah untuk dikirim ke pengguna.
     *
     * @return array{otp_code: string, session_token: string}
     */
    public function generateOtp(User $user, string $ipAddress, string $deviceFingerprint): array
    {
        $config     = config('security.otp');
        $otpLength  = (int) ($config['length'] ?? 6);
        $expiresMins = (int) ($config['expires_minutes'] ?? 5);

        // Batalkan semua OTP aktif sebelumnya milik pengguna ini
        $this->invalidatePreviousOtps($user->id);

        // Hasilkan kode OTP numerik acak yang kuat secara kriptografis
        $otpCode      = $this->generateSecureNumericCode($otpLength);
        $sessionToken = Str::random(64);

        OtpVerification::create([
            'user_id'            => $user->id,
            'token'              => Hash::make($otpCode), // Simpan hash, bukan kode asli
            'session_token'      => $sessionToken,
            'ip_address'         => $ipAddress,
            'device_fingerprint' => $deviceFingerprint,
            'expires_at'         => now()->addMinutes($expiresMins),
            'attempts'           => 0,
        ]);

        Log::channel('security')->info('OTP berhasil dibuat', [
            'user_id'       => $user->id,
            'expires_at'    => now()->addMinutes($expiresMins)->toIso8601String(),
            'ip_address'    => $ipAddress,
        ]);

        return [
            'otp_code'      => $otpCode,      // Dikirim ke pengguna via email/SMS
            'session_token' => $sessionToken, // Dikembalikan ke klien sebagai ID sesi OTP
        ];
    }

    /**
     * Verifikasi kode OTP yang dikirimkan pengguna.
     *
     * @return array{success: bool, reason: string}
     */
    public function verifyOtp(string $sessionToken, string $submittedCode): array
    {
        $otpRecord = OtpVerification::where('session_token', $sessionToken)
            ->whereNull('verified_at')
            ->first();

        // Pastikan rekaman OTP ditemukan
        if (! $otpRecord) {
            Log::channel('security')->warning('Verifikasi OTP: sesi tidak ditemukan', [
                'session_token_prefix' => substr($sessionToken, 0, 8),
            ]);
            return ['success' => false, 'reason' => 'invalid_session'];
        }

        // Periksa apakah batas percobaan sudah habis
        if ($otpRecord->isExhausted()) {
            Log::channel('security')->warning('Verifikasi OTP: percobaan habis', [
                'user_id'  => $otpRecord->user_id,
                'attempts' => $otpRecord->attempts,
            ]);
            return ['success' => false, 'reason' => 'max_attempts_exceeded'];
        }

        // Periksa kedaluwarsa sebelum memvalidasi kode
        if ($otpRecord->isExpired()) {
            Log::channel('security')->info('Verifikasi OTP: sudah kedaluwarsa', [
                'user_id'    => $otpRecord->user_id,
                'expired_at' => $otpRecord->expires_at->toIso8601String(),
            ]);
            return ['success' => false, 'reason' => 'expired'];
        }

        // Tambah hitungan percobaan sebelum memverifikasi (anti-brute force)
        $otpRecord->incrementAttempts();

        // Verifikasi kode menggunakan perbandingan hash yang aman
        if (! Hash::check($submittedCode, $otpRecord->token)) {
            Log::channel('security')->info('Verifikasi OTP: kode salah', [
                'user_id'           => $otpRecord->user_id,
                'remaining_attempts' => config('security.otp.max_attempts') - $otpRecord->fresh()->attempts,
            ]);
            return ['success' => false, 'reason' => 'invalid_code'];
        }

        // OTP valid — tandai sebagai terverifikasi
        $otpRecord->markAsVerified();

        Log::channel('security')->info('Verifikasi OTP berhasil', [
            'user_id' => $otpRecord->user_id,
        ]);

        return ['success' => true, 'reason' => 'verified', 'user_id' => $otpRecord->user_id];
    }

    /**
     * Batalkan semua OTP aktif yang belum diverifikasi untuk pengguna tertentu.
     * Dipanggil sebelum membuat OTP baru.
     */
    private function invalidatePreviousOtps(int $userId): void
    {
        OtpVerification::where('user_id', $userId)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['verified_at' => now()]); // Tandai sebagai kadaluwarsa paksa
    }

    /**
     * Hasilkan kode numerik acak menggunakan generator angka acak yang
     * kuat secara kriptografis (random_int dari PHP).
     */
    private function generateSecureNumericCode(int $length): string
    {
        $min  = (int) str_pad('1', $length, '0');       // misal 6 digit: 100000
        $max  = (int) str_repeat('9', $length);          // misal 6 digit: 999999
        $code = random_int($min, $max);

        return str_pad((string) $code, $length, '0', STR_PAD_LEFT);
    }
}
