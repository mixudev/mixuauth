<?php

namespace App\Modules\Security\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Security\Models\TrustedDevice;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Security\Models\IpBlacklist;
use App\Modules\Security\Models\IpWhitelist;
use App\Modules\Security\Models\LoginLog;
use App\Modules\Authentication\Services\BlockingService;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    // ── DEVICE MANAGEMENT ──────────────────────────────────────────────
    public function devices(Request $request) {
        $search = $request->query('search');
        $devices = TrustedDevice::with('user:id,name,email')
            ->when($search, function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                })->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => TrustedDevice::count(),
            'active' => TrustedDevice::active()->count(),
            'revoked' => TrustedDevice::where('is_revoked', true)->count(),
            'expired' => TrustedDevice::where('is_revoked', false)
                        ->whereNotNull('trusted_until')
                        ->where('trusted_until', '<', now())
                        ->count(),
        ];

        return view('admin.security.device.index', compact('devices', 'stats'));
    }

    /**
     * Get device details via AJAX.
     */
    public function deviceDetails(TrustedDevice $device) {
        $device->load('user:id,name,email');
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $device->id,
                'user_name' => $device->user->name ?? 'Unknown',
                'user_email' => $device->user->email ?? '-',
                'browser' => $device->browser_name,
                'os' => $device->os_name,
                'device_type' => $device->device_type ?? 'Unknown',
                'ip' => $device->ip_address,
                'country' => $device->country_code ?? 'XX',
                'fingerprint' => $device->fingerprint_hash,
                'is_revoked' => $device->is_revoked,
                'is_expired' => $device->trusted_until && $device->trusted_until < now(),
                'created_at' => $device->created_at->format('d M Y, H:i'),
                'last_seen_at' => $device->last_seen_at ? $device->last_seen_at->format('d M Y, H:i') : 'Never',
                'trusted_until' => $device->trusted_until ? $device->trusted_until->format('d M Y, H:i') : 'Indefinite',
            ]
        ]);
    }

    public function revokeDevice(TrustedDevice $device) {
        $device->update(['is_revoked' => !$device->is_revoked]);
        Cache::forget("device_blocked:{$device->fingerprint_hash}");
        $status = $device->is_revoked ? 'dicabut (revoked)' : 'dipulihkan';
        return back()->with('success', "Akses perangkat berhasil $status.");
    }

    // ── OTP LOGS ───────────────────────────────────────────────────────
    public function otps(Request $request) {
        $search = $request->query('search');
        $otps = OtpVerification::with('user:id,name,email')
            ->when($search, function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);
        return view('security::otp.index', compact('otps'));
    }

    // ── IP BLACKLIST ───────────────────────────────────────────────────
    public function blacklist(Request $request) {
        $search = $request->query('search');
        $blacklist = IpBlacklist::when($search, function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")->orWhere('reason', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);
        return view('security::blacklist.index', compact('blacklist'));
    }

    public function storeBlacklist(Request $request) {
        $request->validate(['ip_address' => 'required|ip', 'reason' => 'nullable|string']);
        $this->blockingService->blacklistIp($request->ip_address, $request->reason ?? 'Manual Block', null, auth()->user()->name);
        return back()->with('success', "IP {$request->ip_address} berhasil ditambahkan ke Blacklist.");
    }

    public function destroyBlacklist(IpBlacklist $blacklist) {
        $this->blockingService->unblacklistIp($blacklist->ip_address);
        return back()->with('success', "IP {$blacklist->ip_address} berhasil dihapus dari Blacklist.");
    }

    // ── IP WHITELIST ───────────────────────────────────────────────────
    public function whitelist(Request $request) {
        $search = $request->query('search');
        $whitelist = IpWhitelist::when($search, function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")->orWhere('label', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);
        return view('security::whitelist.index', compact('whitelist'));
    }

    public function storeWhitelist(Request $request) {
        $request->validate(['ip_address' => 'required|ip', 'label' => 'nullable|string']);
        $this->blockingService->whitelistIp($request->ip_address, $request->label ?? 'Admin Whitelist', auth()->user()->name);
        return back()->with('success', "IP {$request->ip_address} berhasil ditambahkan ke Whitelist.");
    }

    public function destroyWhitelist(IpWhitelist $whitelist) {
        $this->blockingService->removeFromWhitelist($whitelist->ip_address);
        return back()->with('success', "IP {$whitelist->ip_address} berhasil dihapus dari Whitelist.");
    }

    // ── AUTH LOGS ──────────────────────────────────────────────────────
    public function logs(Request $request) {
        $search = $request->query('search');
        $status = $request->query('status');
        
        $query = LoginLog::with('user:id,name,email')
            ->when($search, function($q) use ($search) {
                $q->where('email_attempted', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            });

        $logs = $query->latest('occurred_at')->paginate(20)->withQueryString();
        
        // Detailed Stats
        $stats = [
            'total'   => LoginLog::count(),
            'success' => LoginLog::where('status', 'success')->count(),
            'failed'  => LoginLog::where('status', 'failed')->count(),
            'blocked' => LoginLog::where('status', 'blocked')->count(),
            'otp'     => LoginLog::where('status', 'otp_required')->count(),
        ];
            
        return view('admin.security.log.index', compact('logs', 'stats'));
    }

    /**
     * Get log details via AJAX.
     */
    public function logDetails(LoginLog $log) {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($log->user_agent);
        
        $browser = $agent->browser();
        $platform = $agent->platform();
        $device = $agent->device();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'email' => $log->email_attempted,
                'ip' => $log->ip_address,
                'status' => $log->status,
                'risk_score' => $log->risk_score,
                'decision' => $log->decision,
                'reason_flags' => $log->reason_flags,
                'country_code' => $log->country_code,
                'ua' => $log->user_agent,
                'browser' => $browser ?: 'Unknown',
                'browser_version' => $agent->version($browser) ?: '',
                'platform' => $platform ?: 'Unknown',
                'platform_version' => $agent->version($platform) ?: '',
                'device' => $agent->isDesktop() ? 'Desktop' : ($agent->isTablet() ? 'Tablet' : ($agent->isMobile() ? 'Mobile' : 'Unknown')),
                'occurred_at' => $log->occurred_at->format('d M Y, H:i:s'),
                'raw' => $log->ai_response_raw
            ]
        ]);
    }

    /**
     * Bulk delete logs by date range.
     */
    public function bulkDeleteLogs(Request $request) {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $count = LoginLog::whereBetween('occurred_at', [$request->start_date, $request->end_date])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} log keamanan berhasil dihapus secara permanen."
        ]);
    }
}
