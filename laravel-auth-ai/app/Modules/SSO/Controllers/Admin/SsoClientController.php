<?php

namespace App\Modules\SSO\Controllers\Admin;

use App\Modules\SSO\Models\SsoClient;
use App\Modules\SSO\Models\AccessArea;
use App\Modules\SSO\Services\SsoAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Laravel\Passport\ClientRepository;
use Illuminate\Support\Str;

class SsoClientController
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly SsoAuditService $audit,
    ) {}

    /**
     * Daftar semua SSO client apps (Single Page dengan Modal).
     */
    public function index(): View
    {
        $clients = SsoClient::latest()->paginate(15);
        return view('admin.sso.clients.index', compact('clients'));
    }

    /**
     * Simpan client baru: buat OAuth client via Passport + record di sso_clients.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'protocol'    => 'required|in:http://,https://',
            'domain'      => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $domain = rtrim($validated['domain'], '/');
        // Bersihkan http/https jika user menginputnya di dalam input text
        $domain = preg_replace('#^https?://#', '', $domain);

        $baseDomain = $validated['protocol'] . $domain;
        $redirectUri = "{$baseDomain}/auth/callback";
        $webhookUrl = "{$baseDomain}/api/sso/webhook";

        try {
            // Buat OAuth client baru di Passport
            $oauthClient = $this->clientRepository->createAuthorizationCodeGrantClient(
                $validated['name'],
                [$redirectUri], // redirectUris (array)
                true // confidential
            );
            
            // Assign provider directly if needed
            $oauthClient->forceFill(['provider' => 'users'])->save();

            $webhookSecret = SsoClient::generateWebhookSecret();

            // Simpan ke sso_clients
            $ssoClient = SsoClient::create([
                'name'            => $validated['name'],
                'oauth_client_id' => (string) $oauthClient->id,
                'webhook_url'     => $webhookUrl,
                'webhook_secret'  => $webhookSecret,
                'description'     => $validated['description'] ?? null,
                'is_active'       => true,
            ]);

            Log::info('SSO client created.', [
                'sso_client_id'  => $ssoClient->id,
                'oauth_client_id' => $oauthClient->id,
            ]);

            // Flash data credentials agar ditangkap oleh modal pop-up (Hanya 1x tampil)
            // Hilangkan ->with('success', ...) agar tidak bentrok dengan modal credentials
            return redirect()
                ->route('sso.clients.index')
                ->with('credentials_modal', true)
                ->with('client_id', $oauthClient->id)
                ->with('client_secret', $oauthClient->plainSecret)
                ->with('webhook_secret', $webhookSecret);

        } catch (\Throwable $e) {
            Log::error('SSO client creation failed.', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal membuat SSO Client: ' . $e->getMessage());
        }
    }

    /**
     * Update client data.
     */
    public function update(Request $request, SsoClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'protocol'    => 'required|in:http://,https://',
            'domain'      => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        $domain = rtrim($validated['domain'], '/');
        $domain = preg_replace('#^https?://#', '', $domain);

        $baseDomain = $validated['protocol'] . $domain;
        $redirectUri = "{$baseDomain}/auth/callback";
        $webhookUrl = "{$baseDomain}/api/sso/webhook";

        try {
            // Update di Passport juga
            if ($client->oauth_client_id) {
                $oauthClient = $this->clientRepository->find($client->oauth_client_id);
                if ($oauthClient) {
                    $this->clientRepository->update(
                        $oauthClient,
                        $validated['name'],
                        [$redirectUri]
                    );
                }
            }

            $client->update([
                'name'        => $validated['name'],
                'webhook_url' => $webhookUrl,
                'description' => $validated['description'] ?? null,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            return redirect()
                ->route('sso.clients.index')
                ->with('success', 'Data Klien berhasil diperbarui.');

        } catch (\Throwable $e) {
            Log::error('SSO client update failed.', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal update SSO Client: ' . $e->getMessage());
        }
    }

    /**
     * Hapus / revoke client.
     */
    public function destroy(SsoClient $client): RedirectResponse
    {
        try {
            // Revoke di Passport
            if ($client->oauth_client_id) {
                $oauthClient = $this->clientRepository->find($client->oauth_client_id);
                if ($oauthClient) {
                    $this->clientRepository->delete($oauthClient);
                }
            }

            $client->delete();

            Log::info('SSO client deleted.', ['name' => $client->name]);

            return redirect()
                ->route('sso.clients.index')
                ->with('success', 'Klien berhasil dihapus.');

        } catch (\Throwable $e) {
            Log::error('SSO client deletion failed.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal hapus SSO Client: ' . $e->getMessage());
        }
    }

    /**
     * Generate ulang token rahasia (OAuth Secret dan Webhook Secret).
     */
    public function generateToken(SsoClient $client): RedirectResponse
    {
        try {
            $newOAuthSecret = Str::random(40);
            
            if ($client->oauth_client_id) {
                DB::table('oauth_clients')
                    ->where('id', $client->oauth_client_id)
                    ->update([
                        'secret' => $newOAuthSecret,
                        'updated_at' => now(),
                    ]);
            }

            $newWebhookSecret = SsoClient::generateWebhookSecret();
            $client->update(['webhook_secret' => $newWebhookSecret]);

            Log::info('SSO client tokens regenerated.', ['client' => $client->name]);

            // Hilangkan ->with('success', ...) agar popup success tidak bentrok
            return redirect()
                ->route('sso.clients.index')
                ->with('credentials_modal', true)
                ->with('client_id', $client->oauth_client_id)
                ->with('client_secret', $newOAuthSecret)
                ->with('webhook_secret', $newWebhookSecret);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal generate token: ' . $e->getMessage());
        }
    }

    /**
     * Test konektivitas webhook.
     * Mengirim ping ke webhook URL klien dan menampilkan hasil detail.
     */
    public function testWebhook(Request $request, SsoClient $client)
    {
        if (!$client->webhook_url) {
            return back()->with('error', 'Klien tidak memiliki konfigurasi Webhook URL.');
        }

        try {
            // Bangun payload — format SAMA dengan SendGlobalLogoutWebhookJob
            // agar client dapat memakai logika verifikasi yang identik
            $payloadArray = [
                'event'     => 'ping',
                'user_id'   => null,
                'email'     => null,
                'timestamp' => now()->timestamp,
                'message'   => 'SSO Webhook Connection Test',
            ];

            // Encode ke JSON terlebih dahulu — signature dihitung dari string JSON
            // (sama persis dengan cara Job menghitung signature)
            $payloadJson = json_encode($payloadArray);
            $signature   = hash_hmac('sha256', $payloadJson, $client->webhook_secret ?? '');

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-SSO-Signature' => $signature,
                    'Content-Type'    => 'application/json',
                    'Accept'          => 'application/json',
                ])
                ->post($client->webhook_url, $payloadArray);

            Log::info('SSO webhook test selesai.', [
                'client' => $client->name,
                'url'    => $client->webhook_url,
                'status' => $response->status(),
                'body'   => Str::limit($response->body(), 500),
            ]);

            if ($response->successful()) {
                $msg = '✅ Webhook Terhubung! Klien merespons dengan status HTTP ' . $response->status();
                if ($request->ajax()) return response()->json(['success' => true, 'message' => $msg]);
                return back()->with('success', $msg);
            }

            // Tampilkan status + potongan body agar admin tahu penyebabnya
            $preview = Str::limit(strip_tags($response->body()), 200);
            $errMsg = '❌ Test Webhook Gagal. HTTP ' . $response->status() . ($preview ? ' — ' . $preview : '');
            
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $errMsg], 400);
            return back()->with('error', $errMsg);

        } catch (\Exception $e) {
            Log::error('SSO webhook test exception.', [
                'client' => $client->name,
                'url'    => $client->webhook_url,
                'error'  => $e->getMessage(),
            ]);
            $excMsg = '❌ Gagal menghubungi webhook: ' . $e->getMessage();
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $excMsg], 500);
            return back()->with('error', $excMsg);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Access Area Management for Clients
    |--------------------------------------------------------------------------
    */

    /**
     * Tampilkan halaman assign access areas ke client ini.
     * GET /dashboard/sso/clients/{client}/access-areas
     */
    public function editAccessAreas(SsoClient $client): View
    {
        // Semua access area yang aktif
        $allAreas = AccessArea::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Area yang sudah di-assign ke client ini
        $assignedIds = $client->accessAreas()->pluck('access_areas.id')->toArray();

        return view('admin.sso.clients.access-areas', compact('client', 'allAreas', 'assignedIds'));
    }

    /**
     * Sync access areas client (replace semua dengan yang baru).
     * POST /dashboard/sso/clients/{client}/access-areas
     */
    public function syncAccessAreas(Request $request, SsoClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'access_area_ids'   => 'nullable|array',
            'access_area_ids.*' => 'exists:access_areas,id',
        ]);

        $oldAreas = $client->accessAreas()->pluck('access_areas.slug')->toArray();

        // sync() akan remove yang tidak ada dan add yang baru sekaligus
        $client->accessAreas()->sync($validated['access_area_ids'] ?? []);

        $newAreas = $client->fresh()->accessAreas()->pluck('access_areas.slug')->toArray();

        Log::info('SSO client access areas synced.', [
            'client'   => $client->name,
            'old'      => $oldAreas,
            'new'      => $newAreas,
        ]);

        $this->audit->log(
            SsoAuditService::EVENT_CLIENT_AREA_SYNCED,
            $request,
            [
                'client_id'   => $client->id,
                'client_name' => $client->name,
                'old_areas'   => $oldAreas,
                'new_areas'   => $newAreas,
            ],
            $request->user()?->id
        );

        $count = count($validated['access_area_ids'] ?? []);
        $msg   = $count > 0
            ? "{$count} access area berhasil di-assign ke klien {$client->name}."
            : "Semua access area telah dihapus dari klien {$client->name} (Open Client)."; 

        return redirect()
            ->route('sso.clients.index')
            ->with('success', $msg);
    }
}
