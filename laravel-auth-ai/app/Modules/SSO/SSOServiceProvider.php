<?php

namespace App\Modules\SSO;

use App\Http\Middleware\SsoSecurityHeadersMiddleware;
use App\Modules\SSO\Models\PassportClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class SSOServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ── Passport Configuration ─────────────────────────────────────────────
        // Abaikan pengecekan permission key (penting untuk Docker/Windows)
        Passport::$validateKeyPermissions = false;

        // Ambil expiry dari database (Gunakan helper Setting)
        $accessExpiry  = (int) \App\Modules\Settings\Models\Setting::get('token_expiry_access', 120);
        $refreshExpiry = (int) \App\Modules\Settings\Models\Setting::get('token_expiry_refresh', 43200);

        Passport::tokensExpireIn(now()->addMinutes($accessExpiry));
        Passport::refreshTokensExpireIn(now()->addMinutes($refreshExpiry));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Use custom client model to skip authorization prompts for seamless SSO
        Passport::useClientModel(PassportClient::class);

        // Use custom consent view (as a fallback)
        Passport::authorizationView('sso.authorize');

        // ── [5.9] Token Scoping: Minimal Privilege ─────────────────────────────
        // Define explicit OAuth scopes agar client hanya bisa request permission
        // yang memang diperlukan, bukan akses penuh ke semua resource.
        Passport::tokensCan([
            'profile' => 'Membaca profil dasar (nama, email, avatar, status aktif)',
            'areas'   => 'Membaca access areas dan roles yang dimiliki akun',
            'logout'  => 'Melakukan logout dari SSO server (revoke token)',
        ]);

        // Default scope yang diminta otomatis jika client tidak specify scope
        Passport::setDefaultScope([
            'profile',
            'areas',
            'logout',
        ]);

        // ── [5.11] Dynamic CORS Hardening ──────────────────────────────────────
        // Hanya mengizinkan origin dari client SSO yang aktif
        $activeOrigins = \Illuminate\Support\Facades\Cache::remember('sso_active_origins', 60, function () {
            try {
                return \App\Modules\SSO\Models\SsoClient::active()
                    ->pluck('webhook_url')
                    ->map(function ($url) {
                        $parsed = parse_url($url);
                        if (!isset($parsed['scheme']) || !isset($parsed['host'])) return null;
                        return $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
                    })
                    ->filter()
                    ->unique()
                    ->toArray();
            } catch (\Throwable $e) {
                // Fallback saat database belum siap (misal saat migrasi awal)
                return [];
            }
        });

        if (!empty($activeOrigins)) {
            config(['cors.allowed_origins' => array_merge(config('cors.allowed_origins', []), $activeOrigins)]);
        }

        // ── Routes ─────────────────────────────────────────────────────────────
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // CATATAN: enablePasswordGrant() sengaja TIDAK dipanggil.
        // Sistem SSO ini menggunakan Authorization Code Grant — bukan Password Grant.
        // Password Grant sudah deprecated di Passport v12+ dan tidak diperlukan.
    }
}
