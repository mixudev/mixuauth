<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Services\AuthFlowService;
use App\Modules\Security\Middleware\DeviceIdentifierMiddleware;
use App\Models\User;
use App\Modules\Timezone\Services\TimezoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

use App\Modules\Authentication\Services\BlockingService;
use App\Modules\Security\Services\DeviceFingerprintService;

class WebAuthController extends Controller
{
    public function __construct(
        private readonly TimezoneService $timezoneService,
        private readonly AuthFlowService $authFlowService,
        private readonly BlockingService $blockingService,
        private readonly DeviceFingerprintService $fingerprintService,
    ) {}

    public function showLogin(Request $request)
    {
        $ip = $this->fingerprintService->getRealIp($request);
        $fingerprint = $this->fingerprintService->generate($request);

        $isIpBlocked = $this->blockingService->isIpBlocked($ip);
        $isDeviceBlocked = $this->blockingService->isDeviceBlocked($fingerprint);

        $banReason = null;
        if ($isIpBlocked) $banReason = 'IP Anda telah dibatasi karena aktivitas mencurigakan.';
        if ($isDeviceBlocked) $banReason = 'Perangkat Anda tidak diizinkan untuk mengakses sistem.';

        return view('auth.login', [
            'is_banned' => $isIpBlocked || $isDeviceBlocked,
            'ban_reason' => $banReason
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authFlowService->attemptLogin($request, $request->boolean('remember'));

        if ($result['status'] === 'mfa_required') {
            session([
                'mfa_session_token' => $result['session_token'],
                'mfa_expires_in'    => $result['expires_in'],
                'mfa_type'          => $result['mfa_type'] ?? 'email',
                'mfa_email'         => $request->input('email'),
                'mfa_timezone'      => $request->input('_timezone'),
            ]);

            return redirect()->route('auth.mfa.verify')->with('info', $result['message']);
        }

        if ($result['status'] === 'authenticated' && isset($result['user']['id'])) {
            $user = User::find($result['user']['id']);
            if ($user) {
                $this->syncTimezoneAfterLogin($request, $user);
            }

            return $this->redirectAfterAuthentication($request, $result['message']);
        }

        return $this->loginErrorResponse($request, $result);
    }

    public function showMfaVerify()
    {
        if (! session('mfa_session_token')) {
            return redirect()->route('login');
        }

        return view('auth.mfa', [
            'email'      => session('mfa_email'),
            'type'       => session('mfa_type', 'email'),
            'expires_in' => session('mfa_expires_in'),
        ]);
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $isRecovery = $request->boolean('recovery_mode');
        $sessionToken = (string) session('mfa_session_token');
        $code = (string) $request->input('code');

        if ($isRecovery) {
            $result = $this->authFlowService->verifyRecoveryCode($request, $sessionToken, $code);
        } else {
            $result = $this->authFlowService->verifyMfa($request, $sessionToken, $code);
        }

        if ($result['status'] === 'authenticated' && isset($result['user']['id'])) {
            $user = User::find($result['user']['id']);
            if ($user) {
                $this->syncTimezoneAfterLogin($request, $user, session('mfa_timezone'));
            }

            session()->forget([
                'mfa_session_token',
                'mfa_type',
                'mfa_expires_in',
                'mfa_email',
                'mfa_timezone',
            ]);

            return $this->redirectAfterAuthentication($request, $result['message']);
        }

        return back()->withErrors([
            'code' => $result['message'] ?? 'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $result = $this->authFlowService->sendResetLink($request, (string) $request->input('email'));

        return back()->with('success', $result['message']);
    }

    public function showResetPassword(Request $request, $token)
    {
        $validation = $this->authFlowService->validateResetToken((string) $request->query('email'), (string) $token);

        if (! $validation['success']) {
            return view('auth.reset-link-expired', [
                'message' => $validation['reason'] === 'expired'
                    ? 'Link reset password tidak valid atau kadaluarsa.'
                    : 'Link reset password tidak valid.',
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $result = $this->authFlowService->resetPassword(
            $request,
            (string) $request->input('email'),
            (string) $request->input('token'),
            (string) $request->input('password')
        );

        if ($result['status'] === 'ok') {
            return redirect()->route('login')->with('success', $result['message']);
        }

        return back()->withErrors(['email' => $result['message'] ?? 'Gagal meriset password.']);
    }

    private function redirectAfterAuthentication(Request $request, string $message): RedirectResponse
    {
        $response    = redirect()->route('dashboard')->with('success', $message);
        $deviceToken = $request->cookie(DeviceIdentifierMiddleware::COOKIE_NAME);

        if (is_string($deviceToken) && $deviceToken !== '') {
            Cookie::queue(cookie(
                DeviceIdentifierMiddleware::COOKIE_NAME,
                $deviceToken,
                (int) config('security.session.trusted_device_cookie_minutes', 60 * 24 * 30),
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'Lax'
            ));
        }

        return $response;
    }

    private function loginErrorResponse(Request $request, array $result)
    {
        $message = $result['message'] ?? 'Email atau password salah.';

        if (($result['http_status'] ?? 500) === 429) {
            return back()
                ->withInput($request->only('email'))
                ->with('rate_limited', true)
                ->withErrors(['email' => $message]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }

    private function syncTimezoneAfterLogin(
        Request $request,
        User    $user,
        ?string $overrideTimezone = null
    ): void {
        $tz = $overrideTimezone;

        if (! $tz) {
            $tz = $request->input('_timezone');
        }

        if (! $tz) {
            $tz = $request->header('X-Timezone');
        }

        if (! $tz || ! $this->timezoneService->isValid($tz)) {
            if ($user->timezone && $this->timezoneService->isValid($user->timezone)) {
                session(['user_timezone' => $user->timezone]);
            }

            return;
        }

        $user->timezone = $tz;
        $user->save();

        session(['user_timezone' => $tz]);

        Log::debug('[Timezone] Synced after login', [
            'user_id'  => $user->id,
            'timezone' => $tz,
            'source'   => $overrideTimezone ? 'otp_session' : ($request->input('_timezone') ? 'form_input' : 'header'),
        ]);
    }
}
