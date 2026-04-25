# Arsitektur Modular Backend

Sistem ini tidak menggunakan struktur Laravel standar di mana semua logic menumpuk di `app/Http/Controllers`. Sebagai gantinya, kami menggunakan pendekatan **Modular Architecture** di direktori `app/Modules`. 

Setiap modul mewakili satu domain bisnis yang terisolasi, memiliki rutenya sendiri, logic bisnisnya sendiri, dan kadang databasenya sendiri.

---

## Struktur Umum Sebuah Modul

Setiap folder di dalam `app/Modules` memiliki struktur anatomi yang konsisten:

```text
Authentication/
├── Controllers/      # Handler request masuk (Web/API)
├── Models/           # Model khusus domain (misal: SocialAccount)
├── Services/         # Jantung logika (AuthFlowService, MfaService)
├── routes/           # Definisi rute: web.php & api.php
└── Providers/        # Registrasi service ke container Laravel
```

---

## Rincian Modul & Logika Bisnis

### 1. Authentication Module
Modul paling kritikal yang menangani seluruh gerbang masuk pengguna.
- **Tanggung Jawab**: Login (Web/API), Logout, Registrasi, Password Reset.
- **Komponen Utama**: 
    - `AuthFlowService`: Mengatur alur dari login hingga pemberian token/session.
    - `SocialAuthController`: Integrasi Google/GitHub OAuth.
- **Logic**: Modul ini tidak memutuskan apakah user aman, ia hanya memanggil modul **Security** untuk mendapatkan keputusan.

### 2. Security Module (The Guard)
Otak keamanan sistem yang melakukan evaluasi risiko.
- **Tanggung Jawab**: Risk Scoring, Device Fingerprinting, Blocking/Throttling.
- **Komponen Utama**:
    - `DeviceFingerprintService`: Membuat ID unik perangkat.
    - `AiRiskService`: Berkomunikasi dengan microservice Python.
- **Logic**: Menerima metadata dari modul Auth, memprosesnya, dan mengembalikan status: `ALLOW`, `CHALLENGE (MFA)`, atau `BLOCK`.

### 3. Communication Module
Menangani segala bentuk pengiriman pesan keluar.
- **Tanggung Jawab**: Pengiriman Email OTP, WhatsApp Notifications.
- **Komponen Utama**:
    - `WaGatewayModule`: Abstraksi untuk pengiriman pesan via WhatsApp (Fonte).
- **Logic**: Menggunakan sistem antrean (Queue) agar proses pengiriman tidak menghambat performa aplikasi utama.

### 4. Identity & Authorization
- **Identity**: Mengelola profil user, penggantian password, dan manajemen perangkat terpercaya.
- **Authorization**: Menangani Role-Based Access Control (RBAC). Menggunakan *Middleware* untuk memastikan user memiliki izin yang tepat sebelum mengakses resource tertentu.

---

## Pola Interaksi Antar Modul (Deep Dive Logic)

Sistem mengikuti prinsip **Service-to-Service Communication** di dalam monolith untuk menjaga batasan domain (*Domain Boundary*).

### Alur Kerja: Login dengan Penilaian Risiko
Berikut adalah bagaimana logika berpindah antar modul saat proses login:

1. **Authentication Module**: Menerima kredensial dan memvalidasi identitas dasar.
2. **Security Module**: Dipanggil oleh Auth untuk mengevaluasi sidik jari perangkat dan skor risiko AI.
3. **Communication Module**: Dipanggil jika Security memutuskan bahwa tantangan MFA diperlukan.

**Snippet Logika Interaksi (`AuthFlowService`):**
```php {7,11,15}
// Lokasi: app/Modules/Authentication/Services/AuthFlowService.php

public function processLogin(Request $request) {
    // 1. Validasi Kredensial (Auth Module)
    $user = $this->identityService->validate($request->all());

    // 2. Evaluasi Keamanan (Security Module)
    $risk = $this->securityService->analyzeRisk($user, $request);

    if ($risk->isHigh()) {
        $this->securityService->blockAccess($user, 'AI High Risk Detection');
        throw new LockedException("Akses diblokir demi keamanan.");
    }

    if ($risk->requiresMfa()) {
        // 3. Kirim OTP (Communication Module)
        $this->communicationService->sendMfaOtp($user);
        return response()->json(['status' => 'mfa_required']);
    }

    return $this->grantAccess($user);
}
```

::: tip Mengapa Modular?
Dengan modularitas ini, jika Anda ingin mengganti penyedia WhatsApp (misal dari Fonnte ke Twilio), Anda cukup mengubah kode di dalam `app/Modules/WaGateway` tanpa menyentuh logika login atau modul keamanan lainnya. Hal ini mematuhi prinsip **Open/Closed Principle** dalam SOLID.
:::

---

## Cara Menambahkan Modul Baru

1. **Folder**: Buat folder baru di `app/Modules/`.
2. **Struktur**: Buat folder standar (`Controllers`, `Services`, `Models`, `routes`).
3. **Route**: Daftarkan file rute (`web.php` & `api.php`) di dalam folder modul.
4. **Namespace**: Pastikan namespace mengikuti PSR-4: `namespace App\Modules\NamaModul\...`.
5. **Registrasi**: Modul akan otomatis terdeteksi jika sistem *Service Provider* Anda sudah dikonfigurasi untuk memindai folder `Modules`.

