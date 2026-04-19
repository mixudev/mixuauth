<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Security\Services\GeoIpService;
use App\Modules\Security\Services\DeviceFingerprintService;

class PreAuthRateLimitMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware untuk membatasi frekuensi percobaan login.
    |
    | Dua lapis perlindungan:
    |   Layer 1 — Per-IP global  : ip_max_attempts/decay_minutes dari satu IP
    |             [H-01 FIX] Mencegah bypass rate-limit dengan rotasi email.
    |   Layer 2 — Per-email + IP : max_attempts/decay_minutes per kombinasi
    |
    | [H-07 FIX] Backend CAPTCHA Enforcement:
    |   Setelah captcha_after gagal, token CAPTCHA wajib dikirim dan diverifikasi
    |   di backend sebelum request diteruskan ke controller.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly RateLimiter $limiter,
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $config        = config('security.rate_limit');
        $maxAttempts   = (int) ($config['max_attempts'] ?? 5);
        $decayMins     = (int) ($config['decay_minutes'] ?? 15);
        $captchaAfter  = (int) ($config['captcha_after'] ?? 3);
        $challengeType = $config['challenge'] ?? 'captcha';
        $ipMaxAttempts = (int) ($config['ip_max_attempts'] ?? 20);

        $ip      = $this->fingerprintService->getRealIp($request);
        $context = $this->determineContext($request);

        // ── Layer 1: Rate limit per-IP (global, semua email) ─────────────────
        $ipOnlyKey = "ratelimit:ip:{$context}:" . sha1($ip);
        if ($this->limiter->tooManyAttempts($ipOnlyKey, $ipMaxAttempts)) {
            $waitSeconds = $this->limiter->availableIn($ipOnlyKey);

            Log::channel('security')->warning('Rate limit IP global terlampaui', [
                'ip_address'  => $ip,
                'context'     => $context,
                'retry_after' => $waitSeconds,
            ]);

            return $this->buildThrottleResponse($request, $waitSeconds, 'global_ip');
        }

        // ── Layer 2: Rate limit per-email + IP (spesifik) ────────────────────
        $key = $this->buildRateLimitKey($request, $context);
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $waitSeconds = $this->limiter->availableIn($key);

            Log::channel('security')->warning('Rate limit ' . $context . ' (email+IP) terlampaui', [
                'ip_address'      => $ip,
                'email_attempted' => $request->input('email'),
                'context'         => $context,
                'retry_after'     => $waitSeconds,
            ]);

            return $this->buildThrottleResponse($request, $waitSeconds, $context);
        }

        // ── [H-07] Backend CAPTCHA Enforcement ───────────────────────────────
        $captchaRequiredKey = "captcha_required:{$key}";
        if ($challengeType === 'captcha' && Cache::has($captchaRequiredKey)) {
            $captchaToken = $request->input('captcha_token');
            if (! $this->verifyCaptchaToken($captchaToken)) {

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message'          => 'Verifikasi Keamanan diperlukan sebelum melanjutkan.',
                        'error_code'       => 'CAPTCHA_REQUIRED',
                        'requires_captcha' => true,
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }

                return back()
                    ->withInput($request->except('password', 'captcha_token'))
                    ->withErrors(['captcha_token' => 'Harap lengkapi verifikasi keamanan untuk membuktikan Anda bukan robot.'])
                    ->with('requires_captcha', true);
            }
            // Token valid → bersihkan flag agar tidak terus menghalangi
            Cache::forget($captchaRequiredKey);
        }

        // ── Catat percobaan pada kedua layer ──────────────────────────────────
        $this->limiter->hit($key, $decayMins * 60);
        $this->limiter->hit($ipOnlyKey, $decayMins * 60);

        $currentAttempts = $this->limiter->attempts($key);

        $response = $next($request);

        // Setelah N gagal → set flag CAPTCHA required di cache
        if ($currentAttempts >= $captchaAfter && $challengeType === 'captcha') {
            Cache::put($captchaRequiredKey, true, now()->addMinutes($decayMins));
            $response->headers->set('X-Captcha-Required', 'true');
        }

        return $response;
    }

    /**
     * Verifikasi token CAPTCHA ke penyedia eksternal (hCaptcha / Turnstile).
     */
    private function verifyCaptchaToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $secret = config('services.captcha.secret');

        if (empty($secret)) {
            Log::channel('security')->debug('CAPTCHA secret belum dikonfigurasi — melewati verifikasi (dev mode)');
            return true;
        }

        try {
            $verifyUrl = config('services.captcha.verify_url', 'https://hcaptcha.com/siteverify');
            $result    = Http::asForm()
                ->timeout(5)
                ->post($verifyUrl, [
                    'secret'   => $secret,
                    'response' => $token,
                ]);

            return (bool) $result->json('success', false);
        } catch (\Throwable $e) {
            Log::channel('security')->warning('Verifikasi CAPTCHA gagal (exception)', [
                'error' => $e->getMessage(),
            ]);

            // Fail-open pada error jaringan agar tidak memblokir user sah
            return true;
        }
    }

    /**
     * Tentukan konteks permintaan berdasarkan nama route.
     */
    private function determineContext(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        if (str_contains($routeName, 'password.email')) {
            return 'forgot_password';
        }

        if (str_contains($routeName, 'password.update')) {
            return 'reset_password';
        }

        return 'login';
    }

    /**
     * Bangun kunci rate limit per-email + IP + konteks.
     */
    private function buildRateLimitKey(Request $request, string $context): string
    {
        $emailHash = sha1(strtolower((string) $request->input('email', '')));
        $ipHash    = sha1($this->fingerprintService->getRealIp($request));

        return "{$context}|{$emailHash}|{$ipHash}";
    }

    /**
     * Bangun response 429 yang kontekstual (mendukung Web maupun API).
     */
    private function buildThrottleResponse(Request $request, int $retryAfterSeconds, string $context = 'login'): Response
    {
        $retryAfterMinutes = (int) ceil($retryAfterSeconds / 60);

        $message = match ($context) {
            'forgot_password' => "Terlalu banyak permintaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            'reset_password'  => "Terlalu banyak percobaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            'global_ip'       => "Terlalu banyak aktivitas dari sistem Anda. Akses dibatasi sementara hingga {$retryAfterMinutes} menit.",
            default           => "Terlalu banyak percobaan login gagal. Coba lagi dalam {$retryAfterMinutes} menit.",
        };

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message'     => $message,
                'error_code'  => 'TOO_MANY_ATTEMPTS',
                'retry_after' => $retryAfterSeconds,
            ], Response::HTTP_TOO_MANY_REQUESTS)
                ->withHeaders([
                    'Retry-After' => $retryAfterSeconds,
                ]);
        }

        return back()
            ->withInput($request->except('password'))
            ->withErrors(['email' => $message])
            ->withHeaders([
                'Retry-After' => $retryAfterSeconds,
            ]);
    }
}
