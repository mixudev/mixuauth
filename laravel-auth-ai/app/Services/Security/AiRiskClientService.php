<?php

namespace App\Services\Security;

use App\DTOs\RiskAssessmentResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRiskClientService
{
    /*
    |--------------------------------------------------------------------------
    | Klien HTTP untuk berkomunikasi dengan layanan penilaian risiko FastAPI.
    |
    | Layanan ini mengirim payload risiko dan menerima keputusan login.
    | Tidak ada password atau token autentikasi yang dikirimkan ke sini.
    |--------------------------------------------------------------------------
    */

    /**
     * Kirim payload risiko ke FastAPI dan terima hasil penilaian.
     *
     * Melempar exception jika terjadi kegagalan jaringan, timeout,
     * atau respons tidak valid — penanganan fallback ada di RiskFallbackMiddleware.
     *
     * @param  array<string, mixed>  $payload
     * @throws \RuntimeException
     */
    public function sendToFastApi(array $payload): RiskAssessmentResult
    {
        $baseUrl  = config('security.ai_service.base_url');
        $endpoint = config('security.ai_service.endpoint');
        $timeout  = config('security.ai_service.timeout_seconds', 5);
        $apiKey   = config('security.ai_service.api_key');

        $startTime = microtime(true);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(config('security.ai_service.connect_timeout', 3))
                ->withHeaders([
                    'X-API-Key'    => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post("{$baseUrl}{$endpoint}", $payload);

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            // Catat latensi untuk monitoring performa
            Log::channel('security')->info('Respons AI diterima', [
                'status_code' => $response->status(),
                'latency_ms'  => $latencyMs,
            ]);

            if ($response->failed()) {
                throw new \RuntimeException(
                    "Layanan AI mengembalikan status HTTP {$response->status()}"
                );
            }

            return $this->parseResponse($response->json(), $payload);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('security')->warning('Koneksi ke layanan AI gagal', [
                'error'   => $e->getMessage(),
                'base_url' => $baseUrl,
            ]);
            throw new \RuntimeException('Layanan AI tidak dapat dijangkau: ' . $e->getMessage(), 0, $e);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::channel('security')->warning('Request ke layanan AI gagal', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Request AI gagal: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validasi dan parse respons JSON dari FastAPI menjadi DTO.
     *
     * Format respons yang diharapkan:
     * {
     *   "risk_score": 45,
     *   "decision": "OTP",
     *   "reason_flags": ["new_device", "vpn_detected"],
     *   "confidence": 0.87
     * }
     *
     * @param  array<string, mixed>|null  $data
     * @throws \RuntimeException
     */
    private function parseResponse(?array $data, array $payload = []): RiskAssessmentResult
    {
        if (empty($data)) {
            throw new \RuntimeException('Respons AI kosong atau tidak valid');
        }

        // Validasi keberadaan field wajib
        if (! isset($data['risk_score'], $data['decision'])) {
            throw new \RuntimeException(
                'Respons AI tidak memiliki field wajib: risk_score atau decision'
            );
        }

        $riskScore = (int) $data['risk_score'];
        $decision  = strtoupper((string) $data['decision']);

        // Validasi nilai risk_score dalam rentang yang wajar
        if ($riskScore < 0 || $riskScore > 100) {
            throw new \RuntimeException("risk_score tidak valid: {$riskScore}");
        }

        // Validasi nilai decision hanya menerima yang diketahui
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
