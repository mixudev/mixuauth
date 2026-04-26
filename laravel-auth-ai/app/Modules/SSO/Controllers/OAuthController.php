<?php
/*
|--------------------------------------------------------------------------
| OAuthController.php
|--------------------------------------------------------------------------
| Handles OAuth2 Authorization flow dengan lapisan keamanan tambahan:
|
| [1] State parameter validation       → cegah CSRF pada OAuth flow
| [2] PKCE enforcement                 → cegah authorization code interception
| [3] Active client guard              → tolak client yang nonaktif
| [4] Redirect URI strict validation   → cegah open redirect
| [5] Access Area enforcement          → user harus punya area yang dibutuhkan client
| [6] Audit logging                    → catat semua events penting
|
| Setelah semua pemeriksaan lulus, delegate ke Laravel Passport's
| built-in controllers untuk memastikan kompatibilitas dan keamanan OAuth2.
|--------------------------------------------------------------------------
*/

namespace App\Modules\SSO\Controllers;

use App\Modules\SSO\Models\SsoClient;
use App\Modules\SSO\Services\SsoAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController as BaseAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class OAuthController
{
    public function __construct(
        private readonly BaseAuthorizationController $baseController,
        private readonly ApproveAuthorizationController $approveController,
        private readonly DenyAuthorizationController $denyController,
        private readonly SsoAuditService $audit,
    ) {}

    /**
     * GET /oauth/authorize
     *
     * Menampilkan halaman consent atau auto-approve.
     * Sebelum sampai ke Passport, jalankan semua pemeriksaan keamanan.
     */
    public function show(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse|RedirectResponse {

        // ──────────────────────────────────────────────────────────────────
        // [1] STATE PARAMETER VALIDATION (CSRF Protection)
        //     Passport sudah meng-handle ini secara internal, tapi kita
        //     tambahkan validasi eksplisit sebagai defense-in-depth.
        // ──────────────────────────────────────────────────────────────────
        if (empty($request->query('state'))) {
            Log::warning('SSO OAuth: missing state parameter.', [
                'ip'         => $request->ip(),
                'client_id'  => $request->query('client_id'),
                'user_agent' => $request->userAgent(),
            ]);

            $this->audit->log(
                SsoAuditService::EVENT_AUTHORIZE_DENIED_STATE,
                $request,
                ['client_id' => $request->query('client_id')]
            );

            return $this->oauthError($request, 'invalid_request', 'Parameter state wajib disertakan.');
        }

        // ──────────────────────────────────────────────────────────────────
        // [2] PKCE ENFORCEMENT (Authorization Code Interception Protection)
        //     Wajib kirim code_challenge dengan method S256.
        // ──────────────────────────────────────────────────────────────────
        if (empty($request->query('code_challenge'))) {
            Log::warning('SSO OAuth: PKCE code_challenge missing.', [
                'ip'        => $request->ip(),
                'client_id' => $request->query('client_id'),
            ]);

            $this->audit->log(
                SsoAuditService::EVENT_AUTHORIZE_DENIED_PKCE,
                $request,
                ['client_id' => $request->query('client_id')]
            );

            return $this->oauthError($request, 'invalid_request', 'PKCE code_challenge wajib disertakan.');
        }

        if ($request->query('code_challenge_method', '') !== 'S256') {
            return $this->oauthError($request, 'invalid_request', 'code_challenge_method harus S256.');
        }

        // ──────────────────────────────────────────────────────────────────
        // [AUTH CHECK] Pastikan user sudah login sebelum cek area
        // ──────────────────────────────────────────────────────────────────
        if (! Auth::check()) {
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ──────────────────────────────────────────────────────────────────
        // [3] ACTIVE CLIENT GUARD
        //     Cari SsoClient berdasarkan oauth_client_id dari query string.
        // ──────────────────────────────────────────────────────────────────
        $oauthClientId = $request->query('client_id');
        $ssoClient     = null;

        if ($oauthClientId) {
            $ssoClient = SsoClient::where('oauth_client_id', $oauthClientId)->first();
        }

        if ($ssoClient && ! $ssoClient->is_active) {
            Log::warning('SSO OAuth: client is inactive.', [
                'client_id'  => $oauthClientId,
                'client_name' => $ssoClient->name,
                'user_id'    => $user->id,
            ]);

            $this->audit->log(
                SsoAuditService::EVENT_AUTHORIZE_DENIED_CLIENT,
                $request,
                [
                    'client_id'   => $oauthClientId,
                    'client_name' => $ssoClient->name,
                ],
                $user->id
            );

            return redirect()->route('sso.access-denied')->with([
                'denied_reason'  => 'client_inactive',
                'app_name'       => $ssoClient->name,
            ]);
        }

        // ──────────────────────────────────────────────────────────────────
        // [4] REDIRECT URI STRICT VALIDATION (Open Redirect Prevention)
        //     redirect_uri dari request HARUS exact-match dengan yang terdaftar
        //     di oauth_clients. Tidak ada prefix/partial match.
        // ──────────────────────────────────────────────────────────────────
        $requestedUri = $request->query('redirect_uri');
        if ($requestedUri && $oauthClientId) {
            $registeredUris = DB::table('oauth_clients')
                ->where('id', $oauthClientId)
                ->value('redirect_uris');

            if ($registeredUris) {
                // Passport menyimpan redirect URIs sebagai JSON array
                $allowedUris = json_decode($registeredUris, true) ?? [$registeredUris];
                $normalizedRequest = rtrim($requestedUri, '/');
                $isValid = false;

                foreach ($allowedUris as $uri) {
                    if (rtrim($uri, '/') === $normalizedRequest) {
                        $isValid = true;
                        break;
                    }
                }

                if (! $isValid) {
                    Log::warning('SSO OAuth: redirect_uri mismatch.', [
                        'requested'   => $requestedUri,
                        'registered'  => $allowedUris,
                        'client_id'   => $oauthClientId,
                        'user_id'     => $user->id,
                    ]);

                    $this->audit->log(
                        SsoAuditService::EVENT_AUTHORIZE_DENIED_URI,
                        $request,
                        [
                            'requested_uri' => $requestedUri,
                            'client_id'     => $oauthClientId,
                        ],
                        $user->id
                    );

                    return $this->oauthError($request, 'invalid_request', 'redirect_uri tidak valid.');
                }
            }
        }

        // ──────────────────────────────────────────────────────────────────
        // [5] ACCESS AREA ENFORCEMENT (Core Feature)
        //     Cek apakah user memiliki SEMUA access area yang dibutuhkan client.
        //     Jika client tidak punya required areas → open client, semua boleh.
        // ──────────────────────────────────────────────────────────────────
        if ($ssoClient && $ssoClient->requiresAnyAccessArea()) {
            // Ambil slugs area yang dimiliki user dari method accessAreas() di User model
            $userAreaSlugs = $user->accessAreas()->pluck('slug')->toArray();

            if (! $ssoClient->userHasRequiredAreas($userAreaSlugs)) {
                // Ambil data untuk ditampilkan di halaman denied
                $requiredAreas  = $ssoClient->accessAreas()
                    ->where('access_areas.is_active', true)
                    ->get(['access_areas.name', 'access_areas.slug']);

                $missingAreaSlugs = $requiredAreas->pluck('slug')
                    ->diff($userAreaSlugs)
                    ->values();

                Log::warning('SSO OAuth: user denied — insufficient access areas.', [
                    'user_id'      => $user->id,
                    'client_id'    => $oauthClientId,
                    'client_name'  => $ssoClient->name,
                    'required'     => $requiredAreas->pluck('slug')->toArray(),
                    'user_has'     => $userAreaSlugs,
                    'missing'      => $missingAreaSlugs->toArray(),
                ]);

                $this->audit->log(
                    SsoAuditService::EVENT_AUTHORIZE_DENIED_AREA,
                    $request,
                    [
                        'client_id'     => $oauthClientId,
                        'client_name'   => $ssoClient->name,
                        'required_areas' => $requiredAreas->pluck('slug')->toArray(),
                        'user_areas'    => $userAreaSlugs,
                        'missing_areas' => $missingAreaSlugs->toArray(),
                    ],
                    $user->id
                );

                return redirect()->route('sso.access-denied')->with([
                    'denied_reason'  => 'access_area',
                    'app_name'       => $ssoClient->name,
                    'required_areas' => $requiredAreas,
                    'user_areas'     => $userAreaSlugs,
                    'missing_areas'  => $missingAreaSlugs->toArray(),
                ]);
            }
        }

        // ──────────────────────────────────────────────────────────────────
        // [6] AUDIT LOG — Authorization berhasil
        // ──────────────────────────────────────────────────────────────────
        $this->audit->log(
            SsoAuditService::EVENT_AUTHORIZE_SUCCESS,
            $request,
            [
                'client_id'   => $oauthClientId,
                'client_name' => $ssoClient?->name,
            ],
            $user->id
        );

        // ──────────────────────────────────────────────────────────────────
        // Semua pemeriksaan lulus → delegate ke Passport's AuthorizationController
        // ──────────────────────────────────────────────────────────────────
        return $this->baseController->authorize(
            $psrRequest,
            $request,
            $psrResponse,
            $viewResponse
        );
    }

    /**
     * POST /oauth/authorize
     * Handles user approval.
     */
    public function approve(Request $request, ResponseInterface $psrResponse): Response
    {
        return $this->approveController->approve($request, $psrResponse);
    }

    /**
     * DELETE /oauth/authorize
     * Handles user denial.
     */
    public function deny(Request $request, ResponseInterface $psrResponse): Response
    {
        return $this->denyController->deny($request, $psrResponse);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Redirect ke redirect_uri dengan OAuth error parameter,
     * atau return JSON jika request menerima JSON.
     */
    private function oauthError(Request $request, string $error, string $description): Response|RedirectResponse
    {
        $redirectUri = $request->query('redirect_uri');
        $state       = $request->query('state');

        if ($redirectUri) {
            $separator = str_contains($redirectUri, '?') ? '&' : '?';
            $url = $redirectUri . $separator . http_build_query([
                'error'             => $error,
                'error_description' => $description,
                'state'             => $state,
            ]);
            return redirect()->away($url);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error'             => $error,
                'error_description' => $description,
            ], 400);
        }

        abort(400, $description);
    }
}
