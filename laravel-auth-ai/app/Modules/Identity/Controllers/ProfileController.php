<?php

namespace App\Modules\Identity\Controllers;

use App\Modules\Authentication\Services\AuthFlowService;
use App\Http\Controllers\Controller;
use App\Modules\Security\Models\LoginLog;
use App\Modules\Security\Models\TrustedDevice;
use App\Models\User;
use App\Modules\Timezone\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Mail\BackupCodesMail;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    private const ALLOWED_PANELS = ['profile', 'security', 'preferences', 'devices', 'activity'];

    public function __construct(
        private readonly TimezoneService $timezoneService,
        private readonly AuthFlowService $authFlowService,
    ) {}

    // -----------------------------------------------------------------------
    // Helper: panel request detection (AJAX swap, no full page)
    // -----------------------------------------------------------------------

    private function isPanelRequest(Request $request): bool
    {
        return $request->header('X-Profile-Panel') === '1';
    }

    // -----------------------------------------------------------------------
    // Single entry point for all profile panels
    // -----------------------------------------------------------------------

    /**
     * Tampilkan profil. Panel ditentukan oleh query string ?panel=xxx
     */
    public function show(Request $request)
    {
        $panel = $request->input('panel', 'profile');

        if (! in_array($panel, self::ALLOWED_PANELS)) {
            $panel = 'profile';
        }

        if ($this->isPanelRequest($request)) {
            $data = $this->getPanelData($panel, $request);
            $view = "identity::profile.panels.{$panel}";
            return response(view($view, $data)->render(), 200)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Full page request: Load data for ALL panels to support instant switching
        $allData = [
            'timezones'          => $this->timezoneService->allTimezones(),
            'devices'            => TrustedDevice::where('user_id', Auth::id())->orderByDesc('last_seen_at')->get(),
            'currentFingerprint' => $request->cookie(\App\Modules\Security\Middleware\DeviceIdentifierMiddleware::COOKIE_NAME),
            'logs'               => LoginLog::where('user_id', Auth::id())->orderBy('occurred_at', 'desc')->paginate(15),
            'currentPanel'       => $panel,
        ];

        return view('identity::profile.index', $allData);
    }

    /**
     * Ambil data yang dibutuhkan oleh masing-masing panel.
     */
    private function getPanelData(string $panel, Request $request): array
    {
        return match ($panel) {
            'preferences' => [
                'timezones' => $this->timezoneService->allTimezones(),
            ],
            'devices' => [
                'devices'            => TrustedDevice::where('user_id', Auth::id())->orderByDesc('last_seen_at')->get(),
                'currentFingerprint' => $request->cookie(\App\Modules\Security\Middleware\DeviceIdentifierMiddleware::COOKIE_NAME),
            ],
            'activity' => [
                'logs' => LoginLog::where('user_id', Auth::id())
                    ->orderBy('occurred_at', 'desc')
                    ->paginate(15),
            ],
            default => [],
        };
    }

    // -----------------------------------------------------------------------
    // Aksi: Update Profil Dasar (nama, avatar)
    // -----------------------------------------------------------------------

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048', 'dimensions:min_width=100,min_height=100'],
        ]);

        if ($request->hasFile('avatar_file')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar_file')->store('avatars', 'public');
        }

        $user->name = $request->name;
        $user->save();

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    // -----------------------------------------------------------------------
    // Aksi: Update Password
    // -----------------------------------------------------------------------

    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);
        $this->authFlowService->revokeUserSessions($user, $request->session()->getId());
        $request->session()->put('auth_session_version', (int) $user->fresh()->session_version);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    // -----------------------------------------------------------------------
    // Aksi: Update Preferensi
    // -----------------------------------------------------------------------

    public function updatePreferences(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'timezone'       => ['required', 'string'],
            'otp_preference' => ['required', 'string', 'in:always,system,disabled'],
        ]);

        $user->update([
            'timezone'       => $validated['timezone'],
            'otp_preference' => $validated['otp_preference'],
        ]);

        session(['user_timezone' => $user->timezone]);

        return back()->with('success', 'Preferensi berhasil disimpan.');
    }

    // -----------------------------------------------------------------------
    // Aksi: Revoke Perangkat
    // -----------------------------------------------------------------------

    public function revokeDevice(Request $request, TrustedDevice $device)
    {
        $this->authorize('revoke', $device);

        $device->revoke();

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'Perangkat berhasil dicabut.']);
        }

        return back()->with('success', 'Perangkat berhasil dicabut kepercayaannya.');
    }

    // -----------------------------------------------------------------------
    // Aksi: MFA
    // -----------------------------------------------------------------------

    public function setupMfa()
    {
        /** @var User $user */
        $user = Auth::user();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $secret    = $google2fa->generateSecretKey();

        session(['mfa_setup_secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        $renderer  = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $writer    = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                $renderer
            )
        );

        return response()->json([
            'secret'  => $secret,
            'qr_code' => $writer->writeString($qrCodeUrl),
        ]);
    }

    public function confirmMfa(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate(['code' => ['required', 'string']]);

        $secret = session('mfa_setup_secret');
        if (! $secret) {
            return response()->json(['message' => 'Sesi setup habis. Silakan coba lagi.'], 422);
        }

        if (! \PragmaRX\Google2FALaravel\Facade::verifyKey($secret, $request->code, 0)) {
            return response()->json(['message' => 'Kode yang Anda masukkan salah.'], 422);
        }

        $backupCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $backupCodes[] = strtoupper(\Illuminate\Support\Str::random(10));
        }

        $user->update([
            'mfa_enabled'  => true,
            'mfa_type'     => 'totp',
            // Gunakan cast "encrypted" di model agar tidak terjadi double-encryption.
            'totp_secret'  => $secret,
            'backup_codes' => $backupCodes,
        ]);

        // Kirim kode cadangan ke email user
        try {
            Mail::to($user->email)->send(new BackupCodesMail($user->name, $backupCodes));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email kode cadangan MFA: ' . $e->getMessage());
        }

        session()->forget('mfa_setup_secret');

        return response()->json([
            'message'      => 'Authenticator App berhasil dikonfigurasi! Kode cadangan telah dikirim ke email Anda.',
            'backup_codes' => $backupCodes,
        ]);
    }

    public function disableMfa(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate(['current_password' => ['required']]);

        if (! Hash::check($request->current_password, $user->password)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Kata sandi salah.'], 422)
                : back()->withErrors(['current_password' => 'Kata sandi salah.']);
        }

        $user->update([
            'mfa_enabled'  => false,
            'totp_secret'  => null,
            'backup_codes' => null,
            'mfa_type'     => 'email',
        ]);

        return $request->wantsJson()
            ? response()->json(['message' => 'MFA berhasil dinonaktifkan.'])
            : back()->with('success', 'MFA berhasil dinonaktifkan.');
    }

    // -----------------------------------------------------------------------
    // Aksi: Reset Kata Sandi via Email
    // -----------------------------------------------------------------------

    public function requestPasswordReset()
    {
        /** @var User $user */
        $user     = Auth::user();
        $result = $this->authFlowService->sendResetLink(request(), $user->email);
        $message = $result['message'] ?? 'Permintaan reset telah dikirim ke email Anda.';

        return back()->with('success', $message);
    }
}
