<?php

namespace App\Modules\SSO\Jobs;

use App\Modules\SSO\Models\SsoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGlobalLogoutWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika gagal.
     */
    public int $tries = 3;

    /**
     * Timeout per request (detik).
     */
    public int $timeout = 10;

    public function __construct(
        public readonly SsoClient $client,
        public readonly int $userId,
        public readonly string $email,
    ) {}

    public function handle(): void
    {
        // ── [5.2] Replay Attack Prevention ────────────────────────────────────
        // Sertakan Unix timestamp dalam payload.
        // Client WAJIB menolak webhook jika |now - timestamp| > 300 detik (5 menit).
        // Ini mencegah penyerang me-replay webhook yang sudah ditangkap sebelumnya.
        $timestamp = now()->timestamp;

        $payload = json_encode([
            'event'     => 'global_logout',
            'user_id'   => $this->userId,
            'email'     => $this->email,
            'timestamp' => $timestamp,             // Unix epoch (UTC)
            'issued_at' => now()->toIso8601String(), // Human-readable untuk debugging
        ]);

        // HMAC-SHA256 dihitung dari keseluruhan JSON string
        // (bukan array) agar konsisten antara server dan client
        $signature = hash_hmac('sha256', $payload, $this->client->webhook_secret);

        try {
            $response = Http::withHeaders([
                'X-SSO-Signature' => $signature,
                'X-SSO-Timestamp' => (string) $timestamp,   // Header terpisah untuk kemudahan verifikasi
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withBody($payload, 'application/json') // Kirim body sebagai raw JSON string
            ->post($this->client->webhook_url);

            if ($response->successful()) {
                Log::info('SSO global logout webhook sent.', [
                    'client'    => $this->client->name,
                    'url'       => $this->client->webhook_url,
                    'user_id'   => $this->userId,
                    'email'     => $this->email,
                    'status'    => $response->status(),
                    'timestamp' => $timestamp,
                ]);
            } else {
                Log::warning('SSO global logout webhook failed.', [
                    'client'  => $this->client->name,
                    'url'     => $this->client->webhook_url,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SSO global logout webhook exception.', [
                'client' => $this->client->name,
                'url'    => $this->client->webhook_url,
                'error'  => $e->getMessage(),
            ]);

            // Re-throw agar queue mencoba ulang sesuai $tries
            throw $e;
        }
    }
}
