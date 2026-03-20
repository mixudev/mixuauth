<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use App\Models\OtpVerification;
use App\Models\IpBlacklist;
use App\Models\IpWhitelist;
use App\Services\Auth\BlockingService;
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
        return view('admin.security.devices', compact('devices'));
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
        return view('admin.security.otps', compact('otps'));
    }

    // ── IP BLACKLIST ───────────────────────────────────────────────────
    public function blacklist(Request $request) {
        $search = $request->query('search');
        $blacklist = IpBlacklist::when($search, function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")->orWhere('reason', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);
        return view('admin.security.blacklist', compact('blacklist'));
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
        return view('admin.security.whitelist', compact('whitelist'));
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
}
