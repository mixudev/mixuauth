<?php

namespace Tests\Unit\Services;

use App\Modules\Authentication\Models\OtpVerification;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OtpService();
    }

    /**
     * generateOtp menghasilkan kode numerik dengan panjang yang benar.
     */
    public function test_generated_otp_has_correct_length(): void
    {
        $user = User::factory()->create();
        $data = $this->service->generateOtp($user, '127.0.0.1', str_repeat('a', 64));

        $this->assertMatchesRegularExpression(
            '/^\d{' . config('security.otp.length', 6) . '}$/',
            $data['otp_code']
        );
    }

    /**
     * Kode OTP tidak disimpan sebagai plain text di database.
     */
    public function test_otp_is_stored_as_hash_not_plaintext(): void
    {
        $user = User::factory()->create();
        $data = $this->service->generateOtp($user, '127.0.0.1', str_repeat('a', 64));

        $record = OtpVerification::where('session_token_hash', hash('sha256', $data['session_token']))->first();

        $this->assertNotNull($record);
        $this->assertNotSame($data['otp_code'], $record->token);
        $this->assertTrue(Hash::check($data['otp_code'], $record->token));
    }

    /**
     * OTP baru membatalkan OTP aktif sebelumnya milik pengguna yang sama.
     */
    public function test_generating_new_otp_invalidates_previous(): void
    {
        $user = User::factory()->create();

        $first  = $this->service->generateOtp($user, '127.0.0.1', str_repeat('a', 64));
        $second = $this->service->generateOtp($user, '127.0.0.1', str_repeat('b', 64));

        // OTP pertama seharusnya sudah tidak berlaku
        $result = $this->service->verifyOtp($first['session_token'], $first['otp_code']);

        $this->assertFalse($result['success']);
        $this->assertSame('invalid_session', $result['reason']);
    }

    /**
     * OTP yang sudah kedaluwarsa harus ditolak.
     */
    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create();
        $data = $this->service->generateOtp($user, '127.0.0.1', str_repeat('c', 64));

        // Paksa kedaluwarsa
        OtpVerification::where('session_token_hash', hash('sha256', $data['session_token']))
            ->update(['expires_at' => now()->subMinute()]);

        $result = $this->service->verifyOtp($data['session_token'], $data['otp_code']);

        $this->assertFalse($result['success']);
        $this->assertSame('expired', $result['reason']);
    }

    /**
     * Setelah melebihi batas percobaan, OTP harus diblokir.
     */
    public function test_otp_is_blocked_after_max_attempts(): void
    {
        $user    = User::factory()->create();
        $maxAttempts = config('security.otp.max_attempts', 3);
        $data    = $this->service->generateOtp($user, '127.0.0.1', str_repeat('d', 64));

        // Habiskan semua percobaan dengan kode salah
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->service->verifyOtp($data['session_token'], '000000');
        }

        // Percobaan berikutnya harus ditolak karena sudah habis
        $result = $this->service->verifyOtp($data['session_token'], $data['otp_code']);

        $this->assertFalse($result['success']);
        $this->assertSame('max_attempts_exceeded', $result['reason']);
    }

    /**
     * Verifikasi dengan kode yang benar harus berhasil.
     */
    public function test_correct_otp_code_verifies_successfully(): void
    {
        $user = User::factory()->create();
        $data = $this->service->generateOtp($user, '127.0.0.1', str_repeat('e', 64));

        $result = $this->service->verifyOtp($data['session_token'], $data['otp_code']);

        $this->assertTrue($result['success']);
        $this->assertSame('verified', $result['reason']);
        $this->assertSame($user->id, $result['user_id']);
    }
}
