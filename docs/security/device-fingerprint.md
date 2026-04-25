# Device Fingerprint & Manajemen Sesi

Dalam upaya melawan serangan pembajakan sesi (_Session Hijacking_) dan Token Theft, AI Auth System mengimplementasikan perlindungan berbasis **Device Fingerprinting**. Sistem tidak hanya memercayai _Session ID_ atau _JWT Token_, tetapi mengikat _state_ autentikasi tersebut ke karakteristik spesifik perangkat pengguna.

## Konsep Dasar Fingerprinting

_Device Fingerprint_ adalah representasi hash (unik) dari kombinasi beberapa variabel yang dikumpulkan dari klien (browser atau mobile device) pada saat proses login terjadi. 

Variabel-variabel tersebut meliputi:
- Alamat IP (Public IP)
- Header HTTP `User-Agent`
- _Accept-Language_ dan _Encoding_
- Fitur Canvas/WebGL Fingerprinting (Jika diimplementasi via _Frontend Script_)

## Implementasi di Backend (Laravel)

Modul Security menyediakan `DeviceFingerprintService` yang bertugas untuk membuat (generate) dan memvalidasi fingerprint.

**Proses Generate Fingerprint:**
```php {6-10}
// app/Modules/Security/Services/DeviceFingerprintService.php

namespace App\Modules\Security\Services;

class DeviceFingerprintService {
    public function generate(Request $request): string {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $accept = $request->header('Accept-Language');
        
        // Membuat hash SHA-256 sebagai identifier unik perangkat
        return hash('sha256', $ip . '|' . $userAgent . '|' . $accept);
    }
}
```

## Validasi Berkelanjutan (Session Guard)

Sistem akan memvalidasi *Fingerprint* tersebut tidak hanya saat *Login*, tetapi **pada setiap request yang memerlukan autentikasi**. 

Jika *Session ID* pengguna dicuri dan digunakan dari komputer peretas, nilai IP atau `User-Agent` akan berbeda. Middleware akan mendeteksi ketidakcocokan ini dan segera menghancurkan sesi tersebut secara preemptif.

**Kode Implementasi Validasi Sesi:**
```php {8,13}
// app/Http/Middleware/VerifyDeviceFingerprint.php

public function handle(Request $request, Closure $next) {
    if (Auth::check()) {
        $expectedFingerprint = session('device_fingerprint');
        $currentFingerprint = app(DeviceFingerprintService::class)->generate($request);
        
        if ($expectedFingerprint !== $currentFingerprint) {
            // Anomali terdeteksi! (Kemungkinan Session Hijacking)
            Log::alert('Session Hijack Attempt Detected', ['user_id' => Auth::id()]);
            
            Auth::logout();
            $request->session()->invalidate();
            
            return redirect('/login')->withErrors(['error' => 'Sesi berakhir demi keamanan Anda.']);
        }
    }
    
    return $next($request);
}
```

::: warning Kebijakan IP Dinamis (Dynamic IP)
Pengguna dari ISP seluler sering kali mengalami perubahan alamat IP. Secara _default_, mengikat sesi secara kaku ke alamat IP dapat menyebabkan pengguna sering ter-_logout_. AI Auth System menggunakan pendekatan _subnet matching_ atau mendelegasikan anomali IP murni ke [AI Risk Scoring](/security/ai-risk-scoring) untuk mengambil keputusan, alih-alih me-logout pengguna secara kaku (hard block).
:::

## Manajemen Perangkat Pengguna

Pengguna dapat melihat daftar perangkat yang aktif di halaman profil mereka. Sistem merekam setiap sesi dan memberikan opsi bagi pengguna untuk mematikan (revoke) sesi di perangkat yang mencurigakan.

Struktur Data pada Tabel `user_sessions`:
- `id` (Session String ID)
- `user_id` (Relasi User)
- `ip_address` 
- `user_agent`
- `last_activity`
- `fingerprint_hash`
