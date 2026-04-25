<?php

namespace App\Modules\WaGateway\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WaGateway\Models\WaGatewayConfig;
use App\Modules\WaGateway\Models\WaGatewayLog;
use App\Modules\WaGateway\Models\WaGatewayTemplate;
use App\Modules\WaGateway\Requests\StoreWaGatewayConfigRequest;
use App\Modules\WaGateway\Services\WaAlertService;
use App\Modules\WaGateway\Services\WaGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WaGatewayConfigController extends Controller
{
    protected WaAlertService $alertService;

    public function __construct(WaAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WaGatewayConfig::class);

        $accessibleConfigs = $this->accessibleConfigsQuery($request);

        $configs = (clone $accessibleConfigs)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $logs = WaGatewayLog::with(['config:id,name,purpose,is_active,user_id'])
            ->whereIn('wa_gateway_config_id', (clone $accessibleConfigs)->select('id'))
            ->latest()
            ->limit(50)
            ->get();

        $stats = [
            'total_configs' => (clone $accessibleConfigs)->count(),
            'active_configs' => (clone $accessibleConfigs)->where('is_active', true)->count(),
            'total_messages_sent' => WaGatewayLog::whereIn('wa_gateway_config_id', (clone $accessibleConfigs)->select('id'))
                ->where('status', 'success')
                ->count(),
            'failed_messages' => WaGatewayLog::whereIn('wa_gateway_config_id', (clone $accessibleConfigs)->select('id'))
                ->where('status', 'failed')
                ->count(),
        ];

        $hourlyCounts = WaGatewayLog::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_bucket, COUNT(*) as total")
            ->whereIn('wa_gateway_config_id', (clone $accessibleConfigs)->select('id'))
            ->where('created_at', '>=', now()->subHours(24)->startOfHour())
            ->groupBy('hour_bucket')
            ->pluck('total', 'hour_bucket');

        $hourlyTraffic = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $count = (int) ($hourlyCounts[$hour->format('Y-m-d H:00:00')] ?? 0);

            $hourlyTraffic[] = [
                'hour' => $hour->format('H:00'),
                'count' => $count,
                'height' => $count > 0 ? min(100, max(15, ($count / 50) * 100)) : 5
            ];
        }
        $stats['hourly_traffic'] = $hourlyTraffic;

        $templates = WaGatewayTemplate::latest()->get();
        $systemSettings = $this->redactedSystemSettings(config('wa_gateway', []));

        return view('wa-gateway::config.index', compact('configs', 'logs', 'stats', 'templates', 'systemSettings'));
    }

    public function create(): View
    {
        return view('wa-gateway::config.create');
    }

    public function systemConfig(): RedirectResponse
    {
        return redirect()->route('wa-gateway.config.index');
    }

    public function updateSystemConfig(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:fonnte,official',
            'providers.fonnte.base_url' => 'required|string',
            'providers.fonnte.token' => 'nullable|string',
            'providers.fonnte.token_header' => 'required|string',
            'providers.fonnte.token_prefix' => 'nullable|string',
            'providers.fonnte.timeout' => 'required|integer|min:1|max:120',
            'providers.fonnte.default_country_code' => 'required|string|max:5',
            'providers.official.base_url' => 'nullable|string',
            'providers.official.token' => 'nullable|string',
            'providers.official.token_header' => 'required|string',
            'providers.official.token_prefix' => 'nullable|string',
            'providers.official.timeout' => 'required|integer|min:1|max:120',
            'providers.official.default_country_code' => 'required|string|max:5',
            'guardrail.daily_limit_per_config' => 'required|integer|min:1|max:100000',
            'guardrail.prevent_duplicate_within_seconds' => 'required|integer|min:0|max:86400',
            'guardrail.quiet_hours_start' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'guardrail.quiet_hours_end' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'guardrail.default_random_delay' => 'required|string|max:20',
        ]);

        $newConfig = [
            'provider' => $request->input('provider'),
            'providers' => [
                'fonnte' => [
                    'base_url' => (string) $request->input('providers.fonnte.base_url'),
                    'token' => (string) ($request->filled('providers.fonnte.token')
                        ? $request->input('providers.fonnte.token')
                        : config('wa_gateway.providers.fonnte.token', '')),
                    'token_header' => (string) $request->input('providers.fonnte.token_header'),
                    'token_prefix' => (string) $request->input('providers.fonnte.token_prefix', ''),
                    'as_form' => true,
                    'timeout' => (int) $request->input('providers.fonnte.timeout', 15),
                    'default_country_code' => (string) $request->input('providers.fonnte.default_country_code', '62'),
                ],
                'official' => [
                    'base_url' => (string) $request->input('providers.official.base_url', ''),
                    'token' => (string) ($request->filled('providers.official.token')
                        ? $request->input('providers.official.token')
                        : config('wa_gateway.providers.official.token', '')),
                    'token_header' => (string) $request->input('providers.official.token_header', 'Authorization'),
                    'token_prefix' => (string) $request->input('providers.official.token_prefix', 'Bearer '),
                    'as_form' => false,
                    'timeout' => (int) $request->input('providers.official.timeout', 15),
                    'default_country_code' => (string) $request->input('providers.official.default_country_code', '62'),
                ],
            ],
            'guardrail' => [
                'enabled' => $request->boolean('guardrail.enabled'),
                'daily_limit_per_config' => (int) $request->input('guardrail.daily_limit_per_config'),
                'prevent_duplicate_within_seconds' => (int) $request->input('guardrail.prevent_duplicate_within_seconds'),
                'quiet_hours_start' => (string) $request->input('guardrail.quiet_hours_start'),
                'quiet_hours_end' => (string) $request->input('guardrail.quiet_hours_end'),
                'allow_critical_in_quiet_hours' => $request->boolean('guardrail.allow_critical_in_quiet_hours'),
                'default_random_delay' => (string) $request->input('guardrail.default_random_delay'),
            ],
        ];

        // Konfigurasi runtime disimpan di memory saja — tidak ditulis ke file PHP
        // untuk mencegah secret tersimpan plaintext di filesystem source code.
        config(['wa_gateway' => $newConfig]);

        if (app()->configurationIsCached()) {
            Artisan::call('config:clear');
            Artisan::output(); // Capture and discard output to ensure clean buffer
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi global WA Gateway berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Konfigurasi global WA Gateway berhasil diperbarui.');
    }

    public function store(StoreWaGatewayConfigRequest $request): RedirectResponse|JsonResponse
    {
        WaGatewayConfig::create([
            'user_id' => $request->user()->id,
            'name' => $request->input('name'),
            'purpose' => $request->input('purpose', 'security'),
            'token' => $request->input('token'),
            'alert_phone_number' => $request->input('alert_phone_number'),
            'send_on_critical_alert' => $request->boolean('send_on_critical_alert'),
            'is_active' => true,
            'meta' => $this->buildMeta($request, null),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi WA Gateway berhasil dibuat',
                'redirect' => route('wa-gateway.config.index'),
            ]);
        }

        return redirect()
            ->route('wa-gateway.config.index')
            ->with('success', 'Konfigurasi WA Gateway berhasil dibuat');
    }

    public function show(WaGatewayConfig $config): View
    {
        $this->authorize('view', $config);

        $logs = $config->logs()
            ->latest()
            ->paginate(20);

        return view('wa-gateway::config.show', compact('config', 'logs'));
    }

    public function edit(WaGatewayConfig $config): View
    {
        $this->authorize('update', $config);
        return view('wa-gateway::config.edit', compact('config'));
    }

    public function update(StoreWaGatewayConfigRequest $request, WaGatewayConfig $config): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $config);

        $config->update([
            'name' => $request->input('name'),
            'purpose' => $request->input('purpose', $config->purpose),
            'token' => $request->input('token') ?: $config->token,
            'alert_phone_number' => $request->input('alert_phone_number'),
            'send_on_critical_alert' => $request->boolean('send_on_critical_alert'),
            'is_active' => $request->boolean('is_active', $config->is_active),
            'meta' => $this->buildMeta($request, $config),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi berhasil diperbarui',
                'redirect' => route('wa-gateway.config.index'),
            ]);
        }

        return redirect()
            ->route('wa-gateway.config.index')
            ->with('success', 'Konfigurasi berhasil diperbarui');
    }

    public function destroy(WaGatewayConfig $config): RedirectResponse
    {
        $this->authorize('delete', $config);
        $config->delete();

        return redirect()
            ->route('wa-gateway.config.index')
            ->with('success', 'Konfigurasi berhasil dihapus');
    }

    public function testSystemConnection(): JsonResponse
    {
        try {
            $service = new WaGatewayService();
            $provider = strtolower(config('wa_gateway.provider', 'fonnte'));
            $phoneNumber = config("wa_gateway.providers.$provider.default_country_code", '62') . '8123456789'; // Dummy number for connection test pulse
            
            // In a real scenario, you might want to send to the admin number
            // or just perform a status check. Here we send a "Pulse" message.
            $testMessage = "*System Pulse Test*\nStatus: Online\nTime: " . now()->format('H:i:s');
            
            $response = $service->sendMessage($phoneNumber, $testMessage, [
                'bypass_guardrail' => true,
            ]);

            return response()->json([
                'success' => $response['status'] ?? false,
                'message' => ($response['status'] ?? false)
                    ? 'Pulse test berhasil dikirim ke jalur utama'
                    : 'Gagal tes koneksi: ' . ($response['reason'] ?? 'Unknown error'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(WaGatewayConfig $config): JsonResponse
    {
        $this->authorize('update', $config);

        $config->update(['is_active' => !$config->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $config->is_active,
            'message' => $config->is_active ? 'Gateway diaktifkan' : 'Gateway dinonaktifkan',
        ]);
    }

    public function testConnection(WaGatewayConfig $config): JsonResponse
    {
        $this->authorize('update', $config);

        try {
            $service = new WaGatewayService($config);
            $provider = data_get($config->meta, 'provider', config('wa_gateway.provider'));
            $testMessage = "*Test Connection*\nGateway: {$config->name}\nProvider: {$provider}\nStatus: OK";
            $response = $service->sendMessage($config->alert_phone_number, $testMessage, [
                'bypass_guardrail' => true,
            ]);

            return response()->json([
                'success' => $response['status'] ?? false,
                'message' => ($response['status'] ?? false)
                    ? 'Test message berhasil dikirim'
                    : 'Gagal mengirim test message: ' . ($response['reason'] ?? 'Unknown error'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getLogs(Request $request, WaGatewayConfig $config): JsonResponse
    {
        $this->authorize('view', $config);

        $logs = $config->logs()
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(20);

        return response()->json($logs);
    }

    public function getLatestLogs(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WaGatewayConfig::class);

        $logs = WaGatewayLog::query()
            ->select([
                'id',
                'wa_gateway_config_id',
                'target_number',
                'message',
                'status',
                'response_id',
                'error_message',
                'sent_at',
                'created_at',
            ])
            ->with(['config' => function ($query) {
                $query->select('id', 'name', 'purpose', 'is_active', 'user_id');
            }])
            ->whereIn('wa_gateway_config_id', $this->accessibleConfigsQuery($request)->select('id'))
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($logs);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:wa_gateway_templates,slug',
            'content' => 'required|string',
            'purpose' => 'required|string',
        ]);

        $template = WaGatewayTemplate::create([
            'name' => $request->input('name'),
            'slug' => $request->input('slug') ?: Str::slug($request->input('name')),
            'content' => $request->input('content'),
            'purpose' => $request->input('purpose'),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil disimpan',
            'data' => $template,
        ]);
    }

    public function updateTemplate(Request $request, WaGatewayTemplate $template): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:wa_gateway_templates,slug,' . $template->id,
            'content' => 'required|string',
            'purpose' => 'required|string',
        ]);

        $template->update($request->only(['name', 'slug', 'content', 'purpose']));

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil diperbarui',
        ]);
    }

    public function destroyTemplate(WaGatewayTemplate $template): JsonResponse
    {
        $template->delete();
        return response()->json([
            'success' => true,
            'message' => 'Template berhasil dihapus',
        ]);
    }

    protected function buildMeta(Request $request, ?WaGatewayConfig $existingConfig): array
    {
        $meta = (array) ($request->input('meta', $existingConfig?->meta ?? []));
        $provider = $request->input('meta.provider') ?: data_get($existingConfig?->meta, 'provider');

        if (!empty($provider)) {
            $meta['provider'] = strtolower((string) $provider);
        }

        return $meta;
    }

    protected function accessibleConfigsQuery(Request $request)
    {
        return WaGatewayConfig::query()
            ->when(
                !$request->user()->hasRole('super-admin'),
                fn ($query) => $query->where('user_id', $request->user()->id)
            );
    }

    protected function redactedSystemSettings(array $settings): array
    {
        data_set($settings, 'providers.fonnte.token', '');
        data_set($settings, 'providers.official.token', '');

        return $settings;
    }

    // persistModuleConfig() DIHAPUS — menyimpan secret ke file PHP adalah
    // anti-pattern keamanan (secret exposure di filesystem & version control).
    // Perubahan konfigurasi runtime menggunakan config() helper saja.
}
