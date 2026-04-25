# Production Hardening Plan — MixuAuth (AUDIT-01)

Rencana ini mencakup **semua temuan** dari `AUDIT-01.md` yang dikategorikan ulang berdasarkan urutan pengerjaan optimal. Total ada **8 kelompok perbaikan** yang bila selesai semua akan memenuhi seluruh checklist _Production Ready_.

---

## Status Saat Ini

- `php artisan route:list` → **GAGAL** (GlobalSearchController tidak ter-resolve)  
- `php artisan optimize` → **GAGAL** (komponen `x-email-base-text` tidak ditemukan)  
- `php artisan test` → **GAGAL** (25 failures, driver SQLite belum ada)  
- Endpoint `/api/whatsapp/send` → **PUBLIK tanpa auth/throttle**  
- Token WA Fonnte hardcoded di `wa_gateway.php` → **EXPOSED di Git**  
- Stored/DOM XSS di notification dropdown → **CRITICAL PATH ke admin**

---

## Urutan Pengerjaan

> [!IMPORTANT]
> Kerjakan secara berurutan. Kelompok 1-3 harus diselesaikan lebih dulu karena blocking deploy dan risiko paling tinggi.

---

## Kelompok 1 — CRITICAL: Tutup Endpoint WA Publik + Secret Leak

### 1.1 Hapus & Amankan Token dari Source Code

> [!CAUTION]
> Token Fonnte aktif (`sTVodj3b8qkdzEmkWakE`) saat ini ter-commit di Git. Token ini harus segera di-revoke di dashboard Fonnte, lalu diganti dengan token baru yang disimpan di `.env`.

#### [MODIFY] [wa_gateway.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/Config/wa_gateway.php)
- Hapus nilai token `'sTVodj3b8qkdzEmkWakE'` dari array `fonnte`
- Ganti dengan `env('WA_FONNTE_TOKEN', '')` agar membaca dari environment

#### [MODIFY] [WaGatewayConfig.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/Models/WaGatewayConfig.php)
- Tambahkan `'token' => 'encrypted'` ke `$casts`
- Tambahkan `protected $hidden = ['token']`

#### [MODIFY] [WaGatewayConfigController.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/Controllers/WaGatewayConfigController.php)
- Hapus method `persistModuleConfig()` dan `getModuleConfigPath()`
- Di `updateSystemConfig()`: hapus pemanggilan `$this->persistModuleConfig($newConfig)` — konfigurasi runtime tidak boleh ditulis ke file PHP
- Di `getLatestLogs()`: ubah dari `WaGatewayLog::with('config')` menjadi query yang men-select hanya kolom aman (`id`, `name`, `purpose`, `is_active`) dan bukan `token`

#### [MODIFY] [.env.example](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/.env.example)
- Ganti password DB `W9sLeP7T@x4RkM!2cHf` dan `ZQ!8pV@r6FJxkNwD7m2C` dengan placeholder nyata seperti `CHANGE_ME_STRONG_PASSWORD`
- Tambahkan variabel baru: `WA_FONNTE_TOKEN=your_fonnte_token_here`

### 1.2 Tutup Endpoint `/api/whatsapp/send`

#### [MODIFY] [routes/api.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/routes/api.php)
- Hapus route publik `Route::post('/whatsapp/send', ...)` yang tanpa middleware
- Buat route baru dengan perlindungan penuh:
  ```php
  Route::middleware(['auth:sanctum', 'throttle:wa-send'])->group(function () {
      Route::post('/whatsapp/send', [WhatsAppController::class, 'send'])
          ->middleware('permission:wa-gateway.send');
  });
  ```
- Daftarkan rate limiter `wa-send` di `AppServiceProvider` atau `RouteServiceProvider`

#### [MODIFY] [WhatsAppController.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Http/Controllers/WhatsAppController.php)
- Ubah implementasi `send()` agar menggunakan `WaGatewayService` (yang memiliki guardrail: quiet hours, daily limit, duplicate protection) bukan `WhatsAppService` yang bypass guardrail

---

## Kelompok 2 — HIGH: Perbaiki Stored/DOM XSS

### 2.1 Notification Dropdown — Sink Utama

#### [MODIFY] [app-dashboard.blade.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/resources/views/layouts/app-dashboard.blade.php)

Pada fungsi `fetchNotifs()` (baris 302-341), ganti template literal dengan DOM builder aman. Alih-alih:
```js
list.innerHTML = res.data.map(n => `...${n.title}...${n.message}...`).join('');
```
Gunakan `document.createElement` + `.textContent` untuk setiap field dinamis (`n.title`, `n.message`, `n.time_ago`). Hanya `icon` SVG (yang berasal dari konstanta internal, bukan user input) yang boleh menggunakan `innerHTML`.

### 2.2 Command Palette — `item.title` dari Server API

#### [MODIFY] [command-palette.js](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/public/assets/js/command-palette.js)

Di `createItemEl()` (baris 255-266), refactor `a.innerHTML = ...` agar `item.title` dan `item.category` diset via `el.textContent`, bukan diinterpolasi ke template string. `item.icon` yang berasal dari internal sidebar boleh tetap via `innerHTML` pada elemen icon-only yang terpisah.

### 2.3 Sink Tambahan

#### [MODIFY] [app-popup.blade.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/resources/views/components/app-popup.blade.php)
- Audit baris 376: pastikan field dinamis yang di-render ke `innerHTML` diganti ke `textContent`

#### [MODIFY] [device/index.blade.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/resources/views/admin/security/device/index.blade.php)
- Audit baris 193: pastikan data device (nama, UA, dll.) di-render aman

#### [MODIFY] [modals.blade.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/resources/views/admin/identity/users/partials/scripts/modals.blade.php)
- Audit baris 276: pastikan field user yang dimasukkan ke modal menggunakan escape yang benar

---

## Kelompok 3 — MEDIUM: Perbaiki Readiness Deploy (route:list + optimize + test)

### 3.1 Fix `php artisan route:list` (GlobalSearchController)

#### [MODIFY] [Identity web.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Identity/routes/web.php)
- Tambahkan `use App\Modules\Identity\Controllers\GlobalSearchController;` di bagian import (baris 1-6)

#### [MODIFY] [GlobalSearchController.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Identity/Controllers/GlobalSearchController.php)
- Ganti `use App\Modules\Identity\Models\User;` → `use App\Models\User;` (namespace User yang benar)

### 3.2 Fix `php artisan optimize` (Email Component)

#### [MODIFY] [otp-text.blade.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/resources/views/emails/otp-text.blade.php)
- Ganti `<x-email-base-text ...>` → `<x-email.base-text ...>` (sesuai path komponen di `resources/views/components/email/base-text.blade.php`)
- Periksa semua file email Blade lain yang mungkin memakai komponen serupa

### 3.3 Fix `php artisan test` (Environment + Drift)

- Cek `phpunit.xml` — pastikan `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` terdefinisi untuk test
- Install/aktifkan ekstensi `pdo_sqlite` di `docker/php/php.ini` (atau Dockerfile)
- Sinkronkan unit test yang drift (terutama `DeviceFingerprintServiceTest`) terhadap implementasi terkini

---

## Kelompok 4 — MEDIUM: RBAC Modul WA

### 4.1 Tambah Permission pada Template Routes

#### [MODIFY] [WaGateway routes/web.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/routes/web.php)
- Baris 76-78 (template routes): tambahkan middleware `permission:wa-gateway.templates.manage` ke setiap route template:
  ```php
  Route::post('templates', ...)->middleware('permission:wa-gateway.templates.manage')->name(...);
  Route::put('templates/{template}', ...)->middleware('permission:wa-gateway.templates.manage')->name(...);
  Route::delete('templates/{template}', ...)->middleware('permission:wa-gateway.templates.manage')->name(...);
  ```

### 4.2 Buat WaGatewayConfigPolicy

#### [NEW] `app/Modules/WaGateway/Policies/WaGatewayConfigPolicy.php`
- Implementasikan method: `viewAny`, `view`, `create`, `update`, `delete`
- Super-admin: akses penuh ke semua config
- Admin/security-officer: hanya config yang `user_id == auth()->id()` atau role tertentu

#### [MODIFY] [WaGatewayConfig.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/Models/WaGatewayConfig.php)
- Tambahkan `use Illuminate\Database\Eloquent\Casts\Attribute;` jika diperlukan untuk Encrypted Cast

#### [MODIFY] `app/Providers/AuthServiceProvider.php` (atau AppServiceProvider)
- Daftarkan: `WaGatewayConfig::class => WaGatewayConfigPolicy::class`

### 4.3 Perbaiki `StoreWaGatewayConfigRequest::authorize()`

#### [MODIFY] `app/Modules/WaGateway/Requests/StoreWaGatewayConfigRequest.php`
- Ganti `return true;` dengan pengecekan nyata: `return $this->user()->can('create', WaGatewayConfig::class);`

---

## Kelompok 5 — MEDIUM: Information Disclosure WA Dashboard

#### [MODIFY] [WaGatewayConfigController.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/WaGateway/Controllers/WaGatewayConfigController.php)

**Stats global (baris 47-51):** Scope statistik berdasarkan role user, bukan global:
```php
$baseQuery = $request->user()->hasRole('super-admin')
    ? WaGatewayConfig::query()
    : WaGatewayConfig::where('user_id', $request->user()->id);

$stats = [
    'total_configs'        => $baseQuery->count(),
    'active_configs'       => (clone $baseQuery)->where('is_active', true)->count(),
    'total_messages_sent'  => WaGatewayLog::whereIn('config_id', $baseQuery->pluck('id'))->where('status', 'success')->count(),
    'failed_messages'      => WaGatewayLog::whereIn('config_id', $baseQuery->pluck('id'))->where('status', 'failed')->count(),
];
```

**Hourly traffic (baris 56-67):** Optimalkan 24 query serial menjadi satu query agregasi tunggal:
```php
$hourlyTraffic = WaGatewayLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
    ->where('created_at', '>=', now()->subHours(24))
    ->groupByRaw('HOUR(created_at)')
    ->pluck('count', 'hour');
```

---

## Kelompok 6 — MEDIUM: Security Headers & CSP Alignment

#### [MODIFY] [SecurityHeadersMiddleware.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/Middleware/SecurityHeadersMiddleware.php)
- Tambahkan CDN yang memang dipakai ke `script-src`: `cdn.tailwindcss.com`, `cdn.jsdelivr.net`, `unpkg.com`
- Tambahkan `connect-src 'self'` untuk AJAX calls
- Tambahkan HSTS header: `Strict-Transport-Security: max-age=31536000; includeSubDomains`

#### [MODIFY] [docker/nginx/default.conf](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/docker/nginx/default.conf)
- Tambahkan header security response di blok `server {}`:
  ```nginx
  add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
  add_header X-Frame-Options "DENY" always;
  add_header X-Content-Type-Options "nosniff" always;
  add_header Referrer-Policy "strict-origin-when-cross-origin" always;
  ```
- Sembunyikan phpMyAdmin dan service internal dari akses publik dengan `deny all` di blok lokasi tertentu

---

## Kelompok 7 — MEDIUM: Migrasi Non-Destruktif & Health Endpoint

### 7.1 Amankan Migrasi Security Notifications

#### [NEW] Migration additive untuk menggantikan pendekatan drop-recreate
- Buat migrasi baru `2026_04_23_add_missing_columns_security_notifications.php`
- Gunakan `Schema::table()` dengan `->after()` untuk menambahkan kolom yang belum ada
- Hapus `Schema::dropIfExists()` dari migrasi lama (atau gunakan `Schema::hasTable()` sebagai guard)

> [!WARNING]
> Jika migrasi drop sudah dijalankan di lingkungan production, buat backup via `mysqldump` sebelum menjalankan ulang.

### 7.2 Batasi Endpoint System Health

#### [MODIFY] [Security routes/web.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/routes/web.php)
- Pindahkan route `system.health` dari group middleware `auth` saja, ke dalam group yang sudah ada (`role:super-admin,admin,security-officer`) atau tambahkan middleware `permission:system.health.view`
- Tambahkan rate limiter ringan agar tidak bisa di-spam:
  ```php
  Route::get('/system/health', [SystemHealthController::class, 'index'])
      ->middleware(['permission:system.health.view', 'throttle:system-health'])
      ->name('system.health');
  ```

---

## Kelompok 8 — LOW: Namespace Job + Dependency Pinning

### 8.1 Fix Namespace Import WA Alert Job

#### [MODIFY] [PreAuthRateLimitMiddleware.php](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/Middleware/PreAuthRateLimitMiddleware.php)
- Ganti baris 14: `use App\Modules\WhatsAppGateway\Jobs\SendWhatsAppNotification;`
- Dengan namespace yang benar sesuai lokasi file job yang sesungguhnya (perlu di-trace: kemungkinan `App\Modules\WaGateway\Jobs\SendWhatsAppNotification`)

#### Pastikan file `SendWhatsAppNotification.php` ada di path yang benar
- Jika belum ada, buat job sederhana yang memanggil `WaGatewayService::sendMessage()`

### 8.2 Pin Dependency Composer

#### [MODIFY] [composer.json](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/composer.json)
- Ganti `"jenssegers/agent": "^*"` dan `"fakerphp/faker": "^*"` dengan versi yang ter-pin ke minor, misal `"^2.13"` untuk agent dan `"^1.23"` untuk faker

---

## Rencana Verifikasi

### Verifikasi Otomatis

```bash
# Harus hijau semua:
php artisan route:list --except-vendor  # Tidak ada error
php artisan optimize                    # Tidak ada error
php artisan test                        # Minimal test yang ada lulus

# Cek XSS Fix
grep -rn "innerHTML" resources/views/layouts/app-dashboard.blade.php
# Harus tidak ada interpolasi data dinamis dari API

# Cek secret tidak ada di source
grep -rn "sTVodj3b8qkdzEmkWakE" .
# Harus kosong hasilnya
```

### Verifikasi Manual

| Item | Cara Verifikasi |
|------|----------------|
| Endpoint WA publik tertutup | `curl -X POST http://localhost/api/whatsapp/send -d "..."` → harus return `401 Unauthorized` |
| Token terenkripsi di DB | Cek kolom `token` di tabel `wa_gateway_configs` → harus terlihat encrypted (base64-like), bukan plaintext |
| XSS fix notifikasi | Buat user dengan nama `<img src=x onerror=alert(1)>`, ganti password, login sebagai admin buka notif dropdown → tidak boleh ada alert box |
| Health endpoint dibatasi | Login sebagai user biasa, akses `/dashboard/api/system/health` → harus `403 Forbidden` |
| CSP header konsisten | `curl -I https://domain/dashboard` dan cek header `Content-Security-Policy` |
| Migrasi aman | Jalankan `php artisan migrate --pretend` dan pastikan tidak ada `dropTable` atau `dropIfExists` untuk tabel yang berisi data audit |

---

## Open Questions

> [!IMPORTANT]
> **Q1: Apakah endpoint `/api/whatsapp/send` memang diperlukan sebagai API eksternal?**  
> Jika ya, tentukan apakah akan pakai Sanctum token (untuk SPA/mobile) atau HMAC signature (untuk server-to-server). Ini menentukan implementasi middleware di Kelompok 1.2.

> [!IMPORTANT]
> **Q2: Apakah ada data di tabel `security_notifications` di environment staging/production?**  
> Jika ya, Kelompok 7 perlu pendekatan backfill yang lebih hati-hati sebelum migrasi additive dijalankan.

> [!NOTE]
> **Q3: Di mana lokasi file `SendWhatsAppNotification.php` yang benar?**  
> Middleware `PreAuthRateLimitMiddleware` mengimport dari namespace yang tidak ada. Perlu konfirmasi path file job yang benar agar Kelompok 8.1 bisa diselesaikan.

> [!NOTE]
> **Q4: Apakah phpMyAdmin diekspos di docker-compose production?**  
> Audit menyebut ini sebagai risiko. Jika ya, perlu dinonaktifkan atau dibatasi ke jaringan internal saja.
