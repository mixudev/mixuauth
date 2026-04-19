<?php

namespace Tests\Feature\Auth;

use App\DTOs\RiskAssessmentResult;
use App\Modules\Authentication\Models\OtpVerification;
use App\Models\User;
use App\Modules\Security\Services\DeviceFingerprintService;
use App\Modules\Security\Services\AiRiskClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Alur lengkap OTP: login → terima session_token → verifikasi OTP → login berhasil.
     */
    public function test_complete_otp_flow(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email'    => 'otp@example.com',
            'password' => Hash::make('SecureP@ss1234'),
        ]);

        // Step 1: Login → AI mengembalikan OTP
        $this->mock(AiRiskClientService::class, function ($mock) {
            $mock->shouldReceive('assess')
                 ->once()
                 ->andReturn(new RiskAssessmentResult(
                     riskScore: 45, decision: 'OTP',
                     reasonFlags: ['new_device'], rawResponse: [],
                 ));
        });

        $loginResponse = $this->postJson('/api/auth/login', [
            'email'    => 'otp@example.com',
            'password' => 'SecureP@ss1234',
        ]);

        $loginResponse->assertAccepted();
        $sessionToken = $loginResponse->json('session_token');
        $this->assertNotEmpty($sessionToken);

        // Step 2: Ambil kode OTP dari database (simulasi)
        $otpRecord = OtpVerification::where('session_token_hash', hash('sha256', $sessionToken))->first();
        $this->assertNotNull($otpRecord);

        // Karena kode di-hash, kita perlu inject kode yang diketahui
        $knownCode = '123456';
        $otpRecord->update(['token' => Hash::make($knownCode)]);

        // Step 3: Verifikasi OTP
        $verifyResponse = $this->postJson('/api/auth/mfa/verify', [
            'session_token' => $sessionToken,
            'code'          => $knownCode,
        ]);

        $verifyResponse->assertOk()
                       ->assertJsonStructure(['message', 'user']);
    }

    /**
     * OTP yang salah harus mengembalikan error, bukan crash.
     */
    public function test_wrong_otp_code_returns_error(): void
    {
        $user = User::factory()->create();

        OtpVerification::create([
            'user_id'       => $user->id,
            'token'         => Hash::make('654321'),
            'session_token_hash' => hash('sha256', str_repeat('a', 64)),
            'ip_address'    => '127.0.0.1',
            'expires_at'    => now()->addMinutes(5),
            'attempts'      => 0,
        ]);

        $response = $this->postJson('/api/auth/mfa/verify', [
            'session_token' => str_repeat('a', 64),
            'code'          => '000000',
        ]);

        $response->assertUnprocessable()
                 ->assertJson(['error_code' => 'INVALID_CODE']);
    }

    /**
     * OTP kedaluwarsa harus ditolak.
     */
    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create();

        OtpVerification::create([
            'user_id'       => $user->id,
            'token'         => Hash::make('123456'),
            'session_token_hash' => hash('sha256', str_repeat('b', 64)),
            'ip_address'    => '127.0.0.1',
            'expires_at'    => now()->subMinutes(1), // Sudah kedaluwarsa
            'attempts'      => 0,
        ]);

        $response = $this->postJson('/api/auth/mfa/verify', [
            'session_token' => str_repeat('b', 64),
            'code'          => '123456',
        ]);

        // Expired token is treated as invalid code/session for clients (anti-enumeration).
        $response->assertUnprocessable()
                 ->assertJson(['error_code' => 'INVALID_CODE']);
    }
}
