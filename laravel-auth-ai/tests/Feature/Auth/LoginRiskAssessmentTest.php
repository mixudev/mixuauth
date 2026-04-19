<?php

namespace Tests\Feature\Auth;

use App\DTOs\RiskAssessmentResult;
use App\Models\User;
use App\Services\AiRiskClientService;
use App\Services\RiskFallbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRiskAssessmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat pengguna uji dengan Argon2id
        $this->user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => Hash::make('SecureP@ss1234'),
            'is_active' => true,
        ]);
    }

    /**
     * AI mengembalikan ALLOW → login langsung berhasil.
     */
    public function test_login_succeeds_when_ai_returns_allow(): void
    {
        $this->mockAiService('ALLOW', 10);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'SecureP@ss1234',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email']]);
    }

    /**
     * AI mengembalikan OTP → respons 202 dengan session_token.
     */
    public function test_login_requires_otp_when_ai_returns_otp(): void
    {
        $this->mockAiService('OTP', 45);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'SecureP@ss1234',
        ]);

        $response->assertAccepted()
                 ->assertJsonStructure(['message', 'requires_otp', 'session_token', 'expires_in'])
                 ->assertJson(['requires_otp' => true]);
    }

    /**
     * AI mengembalikan BLOCK → respons 403.
     */
    public function test_login_is_blocked_when_ai_returns_block(): void
    {
        $this->mockAiService('BLOCK', 75, ['vpn_detected', 'new_country', 'new_device']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'SecureP@ss1234',
        ]);

        $response->assertForbidden()
                 ->assertJson(['error_code' => 'LOGIN_BLOCKED']);
    }

    /**
     * Jika AI tidak tersedia, fallback rule-based harus aktif.
     */
    public function test_fallback_activates_when_ai_is_unreachable(): void
    {
        // Simulasikan AI tidak bisa dijangkau
        $this->mock(AiRiskClientService::class, function ($mock) {
            $mock->shouldReceive('assess')
                 ->once()
                 ->andThrow(new \RuntimeException('Connection refused'));
        });

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'SecureP@ss1234',
        ]);

        // Fallback harus memproses — bukan 500 error
        $response->assertStatus(fn($status) => in_array($status, [200, 202, 403]));
    }

    /**
     * Password salah → 401, tidak menyentuh AI sama sekali.
     */
    public function test_wrong_password_returns_401_without_ai_call(): void
    {
        $this->mock(AiRiskClientService::class, function ($mock) {
            $mock->shouldReceive('assess')->never();
        });

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertUnauthorized()
                 ->assertJson(['error_code' => 'INVALID_CREDENTIALS']);
    }

    /**
     * Rate limit: setelah N percobaan, mengembalikan 429.
     */
    public function test_rate_limit_triggers_after_max_attempts(): void
    {
        $maxAttempts = config('security.rate_limit.max_attempts', 5);

        for ($i = 0; $i <= $maxAttempts; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => 'test@example.com',
                'password' => 'WrongPassword',
            ]);
        }

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertTooManyRequests()
                 ->assertJsonStructure(['retry_after']);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Mock AiRiskClientService dengan keputusan tertentu.
     */
    private function mockAiService(string $decision, int $riskScore, array $flags = []): void
    {
        $this->mock(AiRiskClientService::class, function ($mock) use ($decision, $riskScore, $flags) {
            $mock->shouldReceive('assess')
                 ->once()
                 ->andReturn(new RiskAssessmentResult(
                     riskScore:   $riskScore,
                     decision:    $decision,
                     reasonFlags: $flags,
                     rawResponse: ['mocked' => true],
                 ));
        });
    }
}
