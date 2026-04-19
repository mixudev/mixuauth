<?php

namespace App\Modules\Security\Services;

use App\Shared\DTO\RiskAssessmentResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Shared\Contracts\RiskAssessorInterface;

class AiRiskClientService implements RiskAssessorInterface
{
    /*
    |--------------------------------------------------------------------------
    | Klien HTTP untuk berkomunikasi dengan layanan penilaian risiko FastAPI.
    |
    | [H-06 FIX] Setiap request ke AI service sekarang disertai:
    |   - X-Request-ID  : UUID unik per-request untuk traceability
    |   - X-Timestamp   : Unix timestamp untuk mencegah replay attack
    |   - X-HMAC-Signature : HMAC-SHA256 dari payload + timestamp
    |     → FastAPI harus memverifikasi signature ini sebelum memproses
    |
    | Tidak ada password atau token autentikasi yang dikirimkan ke sini.
    |--------------------------------------------------------------------------
    */

    /**
     * Kirim payload risiko ke FastAPI dan terima hasil penilaian.
     *
     * @param  array<string, mixed>  $payload
     * @throws \RuntimeException
     */
    public function assess(array $payload): RiskAssessmentResult
    {
        $baseUrl    = config('security.ai_service.base_url');
        $endpoint   = config('security.ai_service.endpoint');
        $timeout    = config('security.ai_service.timeout_seconds', 5);
        $apiKey     = config('security.ai_service.api_key');

        $startTime  = microtime(true);
        $requestId  = (string) Str::uuid();
        $timestamp  = (string) now()->timestamp;

        // [H-06] HMAC-SHA256 dari payload + timestamp untuk mencegah MITM/replay
        $hmacSignature = $this->generateHmacSignature($payload, $timestamp, (string) $apiKey);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(config('security.ai_service.connect_timeout', 3))
                ->withHeaders([
                    'X-API-Key'        => $apiKey,
                    'X-Request-ID'     => $requestId,
                    'X-Timestamp'      => $timestamp,
                    'X-HMAC-Signature' => $hmacSignature,
                    'Content-Type'     => 'application/json',
                    'Accept'           => 'application/json',
                ])
                ->post("{$baseUrl}{$endpoint}", $payload);

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('security')->info('Respons AI diterima', [
                'status_code' => $response->status(),
                'latency_ms'  => $latencyMs,
                'request_id'  => $requestId,
            ]);

            if ($response->failed()) {
                throw new \RuntimeException(
                    "Layanan AI mengembalikan status HTTP {$response->status()}"
                );
            }

            return $this->parseResponse($response->json(), $payload);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('security')->warning('Koneksi ke layanan AI gagal', [
                'error'      => $e->getMessage(),
                'base_url'   => $baseUrl,
                'request_id' => $requestId,
            ]);

            try {
                \App\Modules\Security\Models\SecurityNotification::create([
                    'user_id'    => null,
                    'type'       => 'error',
                    'event'      => 'system.ai_service_down',
                    'title'      => 'Layanan AI Offline',
                    'message'    => 'Sistem gagal terhubung ke FastAPI Risk Service. Memasuki mode deteksi fallback (Rule-based).',
                    'meta'       => ['error' => $e->getMessage(), 'url' => $baseUrl],
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Throwable $notifError) {
                // [M-06] Jangan biarkan kegagalan notif menyembunyikan error asli
                Log::channel('security')->error('Gagal membuat SecurityNotification (AI down)', [
                    'error' => $notifError->getMessage(),
                ]);
            }

            throw new \RuntimeException('Layanan AI tidak dapat dijangkau: ' . $e->getMessage(), 0, $e);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::channel('security')->warning('Request ke layanan AI gagal', [
                'error'      => $e->getMessage(),
                'request_id' => $requestId,
            ]);

            try {
                \App\Modules\Security\Models\SecurityNotification::create([
                    'user_id'    => null,
                    'type'       => 'warning',
                    'event'      => 'system.ai_api_error',
                    'title'      => 'Error API AI',
                    'message'    => 'Request ke FastAPI gagal (Status: ' . $e->response?->status() . ').',
                    'meta'       => ['error' => $e->getMessage()],
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Throwable $notifError) {
                Log::channel('security')->error('Gagal membuat SecurityNotification (API error)', [
                    'error' => $notifError->getMessage(),
                ]);
            }

            throw new \RuntimeException('Request AI gagal: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate HMAC-SHA256 signature dari payload + timestamp.
     * FastAPI harus memverifikasi signature ini menggunakan shared secret.
     *
     * Format yang di-sign: sha256( json(payload) + "|" + timestamp )
     */
    private function generateHmacSignature(array $payload, string $timestamp, string $secret): string
    {
        $data = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '|' . $timestamp;
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Validasi dan parse respons JSON dari FastAPI menjadi DTO.
     *
     * @param  array<string, mixed>|null  $data
     * @throws \RuntimeException
     */
    private function parseResponse(?array $data, array $payload = []): RiskAssessmentResult
    {
        if (empty($data)) {
            throw new \RuntimeException('Respons AI kosong atau tidak valid');
        }

        if (! isset($data['risk_score'], $data['decision'])) {
            throw new \RuntimeException(
                'Respons AI tidak memiliki field wajib: risk_score atau decision'
            );
        }

        $riskScore = (int) $data['risk_score'];
        $decision  = strtoupper((string) $data['decision']);

        if ($riskScore < 0 || $riskScore > 100) {
            throw new \RuntimeException("risk_score tidak valid: {$riskScore}");
        }

        $validDecisions = ['ALLOW', 'OTP', 'BLOCK'];
        if (! in_array($decision, $validDecisions, true)) {
            throw new \RuntimeException("Nilai decision tidak dikenal: {$decision}");
        }

        return new RiskAssessmentResult(
            riskScore:   $riskScore,
            decision:    $decision,
            reasonFlags: (array) ($data['reason_flags'] ?? []),
            rawResponse: $data,
            payload:     $payload,
        );
    }
}
