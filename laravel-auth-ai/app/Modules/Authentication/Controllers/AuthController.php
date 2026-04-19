<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Services\AuthFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthFlowService $authFlowService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authFlowService->attemptLogin($request, (bool) $request->boolean('remember'));

        return response()->json($this->toApiPayload($result), $result['http_status']);
    }

    public function verifyMfa(Request $request): JsonResponse
    {
        $request->validate([
            'session_token' => 'required|string|size:64',
            'code'          => 'required|string',
        ]);

        $result = $this->authFlowService->verifyMfa(
            $request,
            (string) $request->input('session_token'),
            (string) $request->input('code')
        );

        return response()->json($this->toApiPayload($result), $result['http_status']);
    }

    public function logout(Request $request): JsonResponse
    {
        $userId = Auth::id();
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Log::channel('security')->info('Pengguna logout', [
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Anda berhasil keluar dari sistem.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $result = $this->authFlowService->sendResetLink($request, (string) $request->input('email'));

        return response()->json(['message' => $result['message']], $result['http_status']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => [
                'required',
                'string',
                'confirmed',
                // [H-02 FIX] Password strength rules yang lebih ketat
                \Illuminate\Validation\Rules\Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3), // Tolak jika muncul di breach database (HaveIBeenPwned)
            ],
        ], [
            'password.min'           => 'Password minimal 12 karakter.',
            'password.mixed_case'    => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers'       => 'Password harus mengandung minimal satu angka.',
            'password.symbols'       => 'Password harus mengandung minimal satu karakter simbol.',
            'password.uncompromised' => 'Password ini telah bocor dalam data breach publik. Gunakan password yang berbeda.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
        ]);

        $result = $this->authFlowService->resetPassword(
            $request,
            (string) $request->input('email'),
            (string) $request->input('token'),
            (string) $request->input('password')
        );

        return response()->json($this->toApiPayload($result), $result['http_status']);
    }

    public function validateResetToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $validation = $this->authFlowService->validateResetToken(
            (string) $request->input('email'),
            (string) $request->input('token')
        );

        if (! $validation['success']) {
            return response()->json([
                'valid'   => false,
                'message' => $validation['reason'] === 'expired'
                    ? 'Link reset password sudah kedaluwarsa.'
                    : 'Link reset password tidak valid.',
            ], 422);
        }

        return response()->json(['valid' => true]);
    }

    private function toApiPayload(array $result): array
    {
        return array_filter([
            'message'       => $result['message'] ?? null,
            'error_code'    => $result['error_code'] ?? null,
            'requires_mfa'  => $result['requires_mfa'] ?? null,
            'requires_otp'  => $result['requires_otp'] ?? null,
            'mfa_type'      => $result['mfa_type'] ?? null,
            'session_token' => $result['session_token'] ?? null,
            'expires_in'    => $result['expires_in'] ?? null,
            'user'          => $result['user'] ?? null,
        ], static fn ($value) => $value !== null);
    }
}
