<?php

namespace App\Modules\SSO\Services;

use App\Modules\SSO\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SsoAuditService
 * ---------------
 * Mencatat semua peristiwa kritis pada alur SSO/OAuth ke tabel audit_logs.
 * Gunakan konstanta event agar konsisten di seluruh codebase.
 */
class SsoAuditService
{
    // ── Event Constants ──────────────────────────────────────────────────────
    const EVENT_AUTHORIZE_SUCCESS       = 'sso.authorize.success';
    const EVENT_AUTHORIZE_DENIED_AREA   = 'sso.authorize.denied.access_area';
    const EVENT_AUTHORIZE_DENIED_CLIENT = 'sso.authorize.denied.inactive_client';
    const EVENT_AUTHORIZE_DENIED_PKCE   = 'sso.authorize.denied.pkce_missing';
    const EVENT_AUTHORIZE_DENIED_STATE  = 'sso.authorize.denied.state_missing';
    const EVENT_AUTHORIZE_DENIED_URI    = 'sso.authorize.denied.redirect_uri_mismatch';
    const EVENT_LOGOUT_GLOBAL           = 'sso.logout.global';
    const EVENT_TOKEN_ISSUED            = 'sso.token.issued';
    const EVENT_CLIENT_AREA_SYNCED      = 'sso.client.access_areas.synced';

    /**
     * Catat event SSO ke tabel audit_logs.
     *
     * @param  string        $event      Nama event (gunakan konstanta di atas)
     * @param  Request       $request    HTTP request yang sedang diproses
     * @param  array         $context    Data tambahan (client_name, required_areas, dll)
     * @param  int|null      $userId     ID user yang terlibat (null jika belum autentikasi)
     */
    public function log(
        string $event,
        Request $request,
        array $context = [],
        ?int $userId = null
    ): void {
        try {
            AuditLog::create([
                'user_id'        => $userId,
                'event'          => $event,
                'auditable_type' => 'SSOEvent',
                'auditable_id'   => null,
                'old_values'     => null,
                'new_values'     => $context ?: null,
                'url'            => $request->fullUrl(),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Audit logging tidak boleh menggagalkan request utama
            Log::error('SsoAuditService: failed to write audit log.', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
