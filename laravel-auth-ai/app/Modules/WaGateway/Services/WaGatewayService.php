<?php

namespace App\Modules\WaGateway\Services;

use App\Modules\WaGateway\Models\WaGatewayConfig;
use App\Modules\WaGateway\Models\WaGatewayLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaGatewayService
{
    protected ?WaGatewayConfig $config;
    protected array $globalConfig;

    public function __construct(?WaGatewayConfig $config = null)
    {
        $this->config = $config;
        $this->globalConfig = config('wa_gateway', []);
    }

    /**
     * Kirim pesan WhatsApp via Fonte API
     *
     * @param string $target Nomor tujuan
     * @param string $message Isi pesan
     * @param array $options Opsi tambahan (url, filename, delay, dll)
     * @return array Response dari API
     */
    public function sendMessage(string $target, string $message, array $options = []): array
    {
        try {
            if ($this->config && !$this->config->isActive()) {
                throw new \Exception("WA Gateway (" . $this->config->name . ") sedang nonaktif");
            }

            if (!$this->config && !($this->globalConfig['provider'] ?? null)) {
                throw new \Exception("Sistem WA Gateway belum dikonfigurasi secara global");
            }

            $provider = $this->resolveProviderName();
            $providerConfig = $this->resolveProviderConfig($provider);

            if ($provider === 'official') {
                throw new \Exception("Provider official belum diimplementasikan. Isi adapter resmi di WaGatewayService::sendViaOfficial().");
            }

            $normalizedTarget = $this->normalizeTarget($target, $providerConfig);
            $this->assertTargetFormat($normalizedTarget);
            $this->assertGuardrail($normalizedTarget, $message, $options);

            $data = array_merge([
                'target' => $normalizedTarget,
                'message' => $message,
            ], $options);

            $data = $this->applyDefaultSendOptions($data);
            unset($data['is_critical'], $data['bypass_guardrail']);
            $responseData = $this->sendByProvider($provider, $providerConfig, $data);

            $this->logMessage($normalizedTarget, $message, $responseData);

            return $responseData;

        } catch (\Exception $e) {
            Log::error("WA Gateway Error: " . $e->getMessage());
            $this->logFailedMessage($target, $message, $e->getMessage());
            return [
                'status' => false,
                'reason' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim pesan ke multiple target
     *
     * @param array $targets Array nomor tujuan
     * @param string $message Isi pesan
     * @param array $options Opsi tambahan
     * @return array Hasil pengiriman untuk setiap target
     */
    public function sendBulkMessages(array $targets, string $message, array $options = []): array
    {
        $results = [];

        foreach ($targets as $target) {
            $results[$target] = $this->sendMessage($target, $message, $options);
        }

        return $results;
    }

    /**
     * Kirim dengan attachment
     *
     * @param string $target
     * @param string $message
     * @param string $url URL publik file
     * @param string|null $filename Nama file custom
     * @param array $options
     * @return array
     */
    public function sendWithAttachment(
        string $target,
        string $message,
        string $url,
        ?string $filename = null,
        array $options = []
    ): array {
        $options['url'] = $url;
        if ($filename) {
            $options['filename'] = $filename;
        }
        return $this->sendMessage($target, $message, $options);
    }

    /**
     * Log pesan yang berhasil dikirim
     *
     * @param string $target
     * @param string $message
     * @param array $responseData
     * @return void
     */
    protected function logMessage(string $target, string $message, array $responseData): void
    {
        $status = $responseData['status'] ?? false ? 'success' : 'failed';
        $responseId = $responseData['id'][0] ?? ($responseData['id'] ?? null);
        $configId = $this->resolveLogConfigId();

        WaGatewayLog::create([
            'wa_gateway_config_id' => $configId,
            'target_number' => $target,
            'message' => $message,
            'status' => $status,
            'response_id' => $responseId,
            'response_data' => $responseData,
            'sent_at' => now(),
        ]);
    }

    /**
     * Log pesan yang gagal
     *
     * @param string $target
     * @param string $message
     * @param string $errorMessage
     * @return void
     */
    protected function logFailedMessage(string $target, string $message, string $errorMessage): void
    {
        $configId = $this->resolveLogConfigId();

        WaGatewayLog::create([
            'wa_gateway_config_id' => $configId,
            'target_number' => $target,
            'message' => $message,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);
    }

    protected function resolveProviderName(): string
    {
        if ($this->config) {
            return strtolower((string) data_get($this->config->meta, 'provider', $this->globalConfig['provider'] ?? 'fonnte'));
        }
        return strtolower((string) ($this->globalConfig['provider'] ?? 'fonnte'));
    }

    protected function resolveProviderConfig(string $provider): array
    {
        $providerConfig = $this->globalConfig['providers'][$provider] ?? [];

        if (empty($providerConfig)) {
            throw new \Exception("Konfigurasi provider '$provider' tidak ditemukan");
        }

        $token = ($this->config && $this->config->token) ? $this->config->token : ($providerConfig['token'] ?? null);
        if (empty($token)) {
            throw new \Exception("Token WA provider belum dikonfigurasi");
        }

        $providerConfig['token'] = $token;
        return $providerConfig;
    }

    protected function sendByProvider(string $provider, array $providerConfig, array $payload): array
    {
        return match ($provider) {
            'fonnte' => $this->sendViaFonnte($providerConfig, $payload),
            'official' => $this->sendViaOfficial($providerConfig, $payload),
            default => throw new \Exception("Provider WA '$provider' belum didukung"),
        };
    }

    protected function sendViaFonnte(array $providerConfig, array $payload): array
    {
        $headerName = $providerConfig['token_header'] ?? 'Authorization';
        $tokenPrefix = $providerConfig['token_prefix'] ?? '';
        $baseUrl = $providerConfig['base_url'] ?? '';
        $timeout = (int) ($providerConfig['timeout'] ?? 15);

        if (empty($baseUrl)) {
            throw new \Exception("Base URL provider fonnte belum diisi");
        }

        $request = Http::timeout($timeout)->withHeaders([
            $headerName => $tokenPrefix . $providerConfig['token'],
        ]);

        if (($providerConfig['as_form'] ?? true) === true) {
            $request = $request->asForm();
        }

        $response = $request->post($baseUrl, $payload);

        if ($response->failed()) {
            $reason = $response->json('reason') ?: $response->body();
            throw new \Exception("Request ke provider gagal (HTTP {$response->status()}): {$reason}");
        }

        return $response->json() ?? [
            'status' => false,
            'reason' => 'Empty response body from provider',
        ];
    }

    protected function sendViaOfficial(array $providerConfig, array $payload): array
    {
        // Implementasi sengaja dipisahkan agar saat migrasi ke official API
        // cukup isi adapter ini + pengaturan di App/Modules/WaGateway/Config/wa_gateway.php.
        throw new \Exception(
            "Adapter provider official belum aktif. Isi endpoint dan payload resmi di method sendViaOfficial()."
        );
    }

    protected function normalizeTarget(string $target, array $providerConfig): string
    {
        $clean = preg_replace('/[^0-9]/', '', trim($target)) ?? '';
        if ($clean === '') {
            return $clean;
        }

        if (str_starts_with($clean, '0')) {
            $countryCode = (string) ($providerConfig['default_country_code'] ?? '62');
            $clean = $countryCode . substr($clean, 1);
        }

        return $clean;
    }

    protected function assertTargetFormat(string $target): void
    {
        if (!preg_match('/^\d{8,16}$/', $target)) {
            throw new \Exception("Format nomor tujuan tidak valid. Gunakan format internasional, contoh: 62812xxxx.");
        }
    }

    protected function applyDefaultSendOptions(array $payload): array
    {
        $guardrail = config('wa_gateway.guardrail', []);
        $enabled = (bool) ($guardrail['enabled'] ?? true);
        if (!$enabled) {
            return $payload;
        }

        if (!array_key_exists('delay', $payload) && !empty($guardrail['default_random_delay'])) {
            $payload['delay'] = $guardrail['default_random_delay'];
        }

        if (!array_key_exists('countryCode', $payload)) {
            $provider = $this->resolveProviderName();
            $payload['countryCode'] = $this->globalConfig['providers'][$provider]['default_country_code'] ?? '62';
        }

        return $payload;
    }

    protected function assertGuardrail(string $target, string $message, array $options): void
    {
        $guardrail = config('wa_gateway.guardrail', []);
        $enabled = (bool) ($guardrail['enabled'] ?? true);
        if (!$enabled) {
            return;
        }

        $bypassGuardrail = (bool) ($options['bypass_guardrail'] ?? false);
        if ($bypassGuardrail) {
            return;
        }

        $isCritical = (bool) ($options['is_critical'] ?? false);
        $now = now();

        $quietStart = (string) ($guardrail['quiet_hours_start'] ?? '22:00');
        $quietEnd = (string) ($guardrail['quiet_hours_end'] ?? '07:00');
        if ($this->isWithinQuietHours($quietStart, $quietEnd, $now)) {
            $allowCritical = (bool) ($guardrail['allow_critical_in_quiet_hours'] ?? true);
            if (!($allowCritical && $isCritical)) {
                throw new \Exception("Pengiriman diblokir pada quiet hours untuk mengurangi risiko spam/ban.");
            }
        }

        $dailyLimit = (int) ($guardrail['daily_limit_per_config'] ?? 0);
        if ($dailyLimit > 0) {
            $configId = $this->resolveLogConfigId();
            $todayCount = WaGatewayLog::where('wa_gateway_config_id', $configId)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($todayCount >= $dailyLimit) {
                throw new \Exception("Batas kirim harian tercapai ({$dailyLimit}/hari).");
            }
        }

        $dupWindow = (int) ($guardrail['prevent_duplicate_within_seconds'] ?? 0);
        if ($dupWindow > 0) {
            $configId = $this->resolveLogConfigId();
            $recentDuplicate = WaGatewayLog::where('wa_gateway_config_id', $configId)
                ->where('target_number', $target)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subSeconds($dupWindow))
                ->exists();

            if ($recentDuplicate) {
                throw new \Exception("Pesan duplikat terdeteksi dalam {$dupWindow} detik terakhir.");
            }
        }
    }

    protected function isWithinQuietHours(string $start, string $end, Carbon $now): bool
    {
        try {
            [$startHour, $startMinute] = array_map('intval', explode(':', $start));
            [$endHour, $endMinute] = array_map('intval', explode(':', $end));
        } catch (\Throwable) {
            return false;
        }

        $startAt = $now->copy()->setTime($startHour, $startMinute);
        $endAt = $now->copy()->setTime($endHour, $endMinute);

        if ($startAt->equalTo($endAt)) {
            return false;
        }

        if ($startAt->lessThan($endAt)) {
            return $now->between($startAt, $endAt);
        }

        return $now->greaterThanOrEqualTo($startAt) || $now->lessThanOrEqualTo($endAt);
    }

    protected function resolveLogConfigId(): ?int
    {
        return $this->config?->id
            ?? WaGatewayConfig::where('is_active', true)->value('id');
    }
}
