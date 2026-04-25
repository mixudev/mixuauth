# End-to-End Login Layers

Keamanan pada _AI Auth System_ tidak hanya bergantung pada validasi _password_, melainkan menerapkan arsitektur *Defense in Depth* (pertahanan berlapis). Halaman ini membedah apa saja yang terjadi di belakang layar saat sebuah *request* masuk ke endpoint login.

## Alur Pertahanan

Setiap permintaan otentikasi melewati 5 (lima) layer utama sebelum _session_ atau _token_ diberikan:

1. **Pre-Auth Rate Limiting**: Memblokir serangan _brute-force_ massal sebelum menyentuh database.
2. **Credential Verification**: Validasi email dan password standar.
3. **AI Risk Assessment**: Evaluasi real-time berbasis profil _behavior_ pengguna.
4. **Device Fingerprinting**: Mendeteksi jika login terjadi dari peramban/perangkat yang tidak dikenal.
5. **Multi-Factor Authentication (MFA)**: Syarat tambahan jika sistem mendeteksi risiko menengah (Medium Risk).

---

## 1. Pre-Auth Rate Limiting Middleware

Layer pertama berada di level HTTP _Middleware_. Sistem membatasi jumlah percobaan login per IP Address dan per kombinasi Email + IP.

**Kode Implementasi:**
```php {4,10}
// app/Http/Middleware/PreAuthRateLimitMiddleware.php

public function handle(Request $request, Closure $next) {
    $key = 'login_attempts:' . $request->ip() . ':' . $request->input('email', 'guest');
    
    // Maksimal 5 percobaan dalam 1 menit
    if (RateLimiter::tooManyAttempts($key, 5)) {
        Log::warning('Brute force attempt blocked', ['ip' => $request->ip()]);
        
        return response()->json([
            'error' => 'Too many login attempts. Please try again later.'
        ], 429);
    }
    
    RateLimiter::hit($key, 60);
    return $next($request);
}
```

::: tip Strategi Operasional
Nilai batasan (`5 attempts`) dapat disesuaikan pada file konfigurasi atau `RateLimiter` provider di Laravel. Pastikan Redis berjalan normal karena _rate limiter_ sangat bergantung pada in-memory data store.
:::

---

## 2. Pemeriksaan oleh AuthFlowService

Setelah lolos limiter, proses utama ditangani oleh `AuthFlowService`. Service ini bertugas mengorkestrasikan *credential check* dan memanggil *Risk Engine*.

**Kode Implementasi:**
```php {8-9,15}
// app/Modules/Authentication/Services/AuthFlowService.php

public function attemptLogin(array $credentials, Request $request) {
    if (!Auth::validate($credentials)) {
        throw new AuthenticationException('Invalid credentials.');
    }

    $user = User::where('email', $credentials['email'])->first();
    
    // 3. Panggil AI Risk Engine
    $riskScore = $this->riskEngine->evaluate($user, $request);
    
    if ($riskScore->level === 'HIGH') {
        $this->securityService->lockAccount($user);
        throw new SecurityException('Login blocked due to high security risk.');
    }

    if ($riskScore->level === 'MEDIUM' || $user->mfa_enabled) {
        // 5. Arahkan ke MFA
        return $this->triggerMfaFlow($user);
    }

    // Login Sukses
    Auth::login($user);
    return $this->generateSuccessResponse($user);
}
```

---

## 3. AI Risk Scoring & 4. Device Fingerprinting

Kedua proses ini terjadi bersamaan saat `riskEngine->evaluate()` dipanggil. Laravel akan mengumpulkan metadata seperti IP, User-Agent, Headers, dan _Canvas Fingerprint_ (jika dari Web) dan mengirimkannya ke FastAPI _Risk Engine_.

Untuk penjelasan teknis mendalam tentang algoritma dan integrasi _FastAPI_, silakan baca halaman khusus berikut:
- [AI Risk Scoring](/security/ai-risk-scoring)
- [Device Fingerprint & Session](/security/device-fingerprint)

---

## 5. Multi-Factor Authentication (MFA)

Jika pengguna mengaktifkan MFA **atau** AI mendeteksi anomali pada tingkat _Medium Risk_ (misal: login dari negara berbeda tapi perangkat valid), pengguna tidak akan langsung diloginkan. 

Sistem mengeluarkan respons `202 Accepted` atau merender _view_ OTP. State login sementara disimpan di session atau menggunakan _signed route/token_ sementara.

**Detail Flow:**
[Baca Panduan Flow MFA & Backup Codes](/security/mfa-backup)
