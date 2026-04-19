# Laravel 11 — AI-Powered Authentication System

Sistem autentikasi Laravel 11 dengan deteksi ancaman berbasis AI menggunakan layanan FastAPI eksternal. Dirancang untuk deployment di Docker dengan jaringan internal.

---

## Arsitektur Sistem

```
Client
  │
  ▼
[POST /api/auth/login]
  │
  ├─ PreAuthRateLimitMiddleware  ←── blokir brute-force sebelum menyentuh DB
  │
  ├─ AuthController::login()
  │     ├─ Validasi kredensial (email + Argon2id password)
  │     ├─ LoginRiskService::prepareRiskPayload()  ←── kumpulkan sinyal risiko
  │     ├─ AiRiskClientService::assess()           ←── kirim ke FastAPI
  │     │         └─ [timeout/error] → RiskFallbackService::assess()
  │     └─ Eksekusi keputusan:
  │           ├─ ALLOW  → finalizeLogin() → sesi + trusted device
  │           ├─ OTP    → OtpService::generateOtp() → kirim kode
  │           └─ BLOCK  → LoginAuditService::recordBlocked() → 403
  │
  └─ LoginAuditService → login_logs table + security.log
```

---

## Struktur Folder

```
app/
├── Console/Commands/
│   └── CleanupExpiredOtpsCommand.php
├── DTOs/
│   └── RiskAssessmentResult.php
├── Exceptions/
│   └── ApiExceptionHandler.php
├── Http/
│   ├── Controllers/Auth/
│   │   └── AuthController.php
│   ├── Middleware/
│   │   ├── PreAuthRateLimitMiddleware.php
│   │   └── VerifySessionFingerprintMiddleware.php
│   └── Requests/
│       ├── LoginRequest.php
│       └── VerifyOtpRequest.php
├── Models/
│   ├── User.php
│   ├── LoginLog.php
│   ├── OtpVerification.php
│   └── TrustedDevice.php
├── Notifications/
│   └── OtpCodeNotification.php
├── Providers/
│   └── AppServiceProvider.php
├── Repositories/
│   └── TrustedDeviceRepository.php
└── Services/
    ├── AiRiskClientService.php
    ├── DeviceFingerprintService.php
    ├── LoginAuditService.php
    ├── LoginRiskService.php
    ├── OtpService.php
    └── RiskFallbackService.php

config/
├── hashing.php     ← Argon2id
├── logging.php     ← security channel
└── security.php    ← semua threshold & konfigurasi

database/migrations/
├── 2024_01_01_000000_create_users_table.php
├── 2024_01_01_000001_create_login_logs_table.php
├── 2024_01_01_000002_create_otp_verifications_table.php
└── 2024_01_01_000003_create_trusted_devices_table.php

tests/
├── Feature/Auth/
│   ├── LoginRiskAssessmentTest.php
│   └── OtpVerificationTest.php
└── Unit/Services/
    ├── DeviceFingerprintServiceTest.php
    ├── OtpServiceTest.php
    └── RiskFallbackServiceTest.php

docker/
├── Dockerfile
├── mysql/my.cnf
└── php/
    ├── opcache.ini
    └── php.ini
```

---

## Konfigurasi Cepat

### 1. Salin dan isi environment variables

```bash
cp .env.example .env
php artisan key:generate
```

Isi nilai berikut di `.env`:
```
AI_RISK_SERVICE_URL=http://fastapi-risk:8000
AI_RISK_API_KEY=your-secret-key
DB_PASSWORD=strong-password
REDIS_PASSWORD=strong-password
```

### 2. Jalankan migrasi

```bash
php artisan migrate
```

### 3. Konfigurasi queue worker (untuk OTP via email)

```bash
php artisan queue:work redis --queue=notifications-high,default
```

### 4. Jalankan dengan Docker

```bash
docker-compose up -d
```

---

## Konfigurasi Keamanan (`config/security.php`)

| Parameter | Default | Keterangan |
|-----------|---------|------------|
| `risk_thresholds.allow` | 30 | Skor di bawah ini → login langsung |
| `risk_thresholds.otp` | 60 | Skor di bawah ini → wajib OTP |
| `otp.expires_minutes` | 5 | Masa berlaku OTP |
| `otp.max_attempts` | 3 | Batas percobaan OTP |
| `rate_limit.max_attempts` | 5 | Batas percobaan login |
| `rate_limit.decay_minutes` | 15 | Durasi lockout |
| `ai_service.timeout_seconds` | 5 | Timeout koneksi ke FastAPI |

---

## API Endpoints

| Method | URL | Keterangan |
|--------|-----|------------|
| POST | `/api/auth/login` | Login dengan penilaian risiko AI |
| POST | `/api/auth/otp/verify` | Verifikasi kode OTP |
| POST | `/api/auth/logout` | Logout (autentikasi wajib) |

### Request Login
```json
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}
```

### Respons ALLOW (200)
```json
{
  "message": "Login berhasil. Selamat datang kembali!",
  "user": { "id": 1, "name": "John", "email": "john@example.com" }
}
```

### Respons OTP (202)
```json
{
  "message": "Kode verifikasi telah dikirimkan.",
  "requires_otp": true,
  "session_token": "64-char-random-token",
  "expires_in": "5 menit"
}
```

### Respons BLOCK (403)
```json
{
  "message": "Login tidak dapat dilanjutkan karena aktivitas mencurigakan terdeteksi.",
  "error_code": "LOGIN_BLOCKED"
}
```

### Request Verifikasi OTP
```json
POST /api/auth/otp/verify
{
  "session_token": "64-char-token-dari-respons-login",
  "otp_code": "123456"
}
```

---

## Format Payload ke FastAPI

```json
{
  "user_id_hash": "sha256-of-user-id-with-app-key",
  "ip_risk_score": 15,
  "is_vpn": false,
  "is_new_device": true,
  "is_new_country": false,
  "login_hour": 14,
  "failed_attempts": 0,
  "request_speed": 1.0,
  "device_fingerprint": "sha256-hash",
  "timestamp": "2024-01-01T14:00:00+00:00"
}
```

### Format Respons yang Diharapkan dari FastAPI

```json
{
  "risk_score": 45,
  "decision": "OTP",
  "reason_flags": ["new_device", "vpn_detected"],
  "confidence": 0.87
}
```

**Nilai `decision` yang valid:** `ALLOW`, `OTP`, `BLOCK`

---

## Fail-Safe Behavior

Jika FastAPI **timeout**, **error**, atau **unreachable**:
- `RiskFallbackService` diaktifkan secara otomatis
- Skor dihitung berdasarkan aturan statis (sinyal: perangkat baru, negara baru, VPN, dll.)
- Threshold fallback **lebih ketat** dari threshold AI (70% dari nilai normal)
- Setiap login fallback dicatat dengan flag `fallback_mode` di `reason_flags`
- Log level `WARNING` ditulis ke `security.log`

---

## Audit Log

Semua percobaan login dicatat di:

- **Database**: tabel `login_logs` — untuk query dan investigasi insiden
- **File**: `storage/logs/security.log` (format JSON) — untuk integrasi SIEM

Setiap rekaman BLOCK menyertakan `reason_flags` yang dapat dijelaskan, misalnya:
```json
["new_device", "new_country", "vpn_detected", "failed_attempts:3"]
```

---

## Menjalankan Test

```bash
# Semua test
php artisan test

# Hanya unit test
php artisan test --testsuite=Unit

# Hanya feature test
php artisan test --testsuite=Feature

# Dengan coverage
php artisan test --coverage
```

---

# Jika terjadi eror Laravel
- Jalankan        : docker exec -it ai-auth-02-app-1 sh
- Lalu jalankan   : rm -f bootstrap/cache/*.php
- atau bisa ini : rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

## Jika terjadi eror composer
- Jalankan        : docker exec -it --user root ai-auth-02-app-1 composer install

## Jika terjadi eror artisan
- Jalankan        : docker exec -it --user root ai-auth-02-app-1 php artisan optimize:clear


# Perintah Restart App Laravel
- docker restart ai-auth-02-app-1
digunakan semisal ada perubahan kita restart container

## Pertimbangan Keamanan Produksi

1. **Argon2id** dikonfigurasi via `config/hashing.php` — sesuaikan `memory` dengan kapasitas server
2. **Session** diikat ke fingerprint perangkat via `VerifySessionFingerprintMiddleware`
3. **FastAPI** hanya dapat diakses dari network internal Docker (`internal: true`)
4. **OTP** disimpan sebagai hash Bcrypt — kode mentah tidak pernah tersimpan
5. **Password dan token** tidak pernah dikirim ke layanan AI
6. **Stack trace** tidak pernah dikembalikan ke klien di production
7. **Rate limiting** berjalan sebelum validasi password (tidak membebani DB)
8. Pastikan menjalankan `php artisan config:cache` dan `php artisan route:cache` di production
