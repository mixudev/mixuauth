# Template Email & Notifikasi

Sistem menggunakan email sebagai saluran utama untuk verifikasi dan notifikasi keamanan. Seluruh template dibangun menggunakan **Laravel Blade** dan bersifat responsif.

## Lokasi Template

Template email dapat ditemukan di direktori:
`laravel-auth-ai/resources/views/emails/`

## Katalog Template Utama

### 1. Multi-Factor Authentication (MFA)
- **File**: `emails/otp.blade.php` atau `emails/mfa/verify.blade.php`
- **Kegunaan**: Mengirimkan 6 digit kode rahasia saat login terdeteksi berisiko atau saat user mengaktifkan 2FA.
- **Data yang dikirim**: `otp_code`, `user_name`, `expiry_time`.

### 2. Backup Codes
- **File**: `emails/mfa/backup-codes.blade.php`
- **Kegunaan**: Dikirimkan saat pengguna pertama kali mengaktifkan MFA. Berisi 8-10 kode cadangan sekali pakai.
- **Peringatan Keamanan**: Template ini menyarankan pengguna untuk mencetak atau menyimpan kode di tempat yang sangat aman.

### 3. Selamat Datang (Social User)
- **File**: `emails/welcome-social-user.blade.php`
- **Kegunaan**: Dikirimkan otomatis saat pengguna baru pertama kali mendaftar menggunakan Google atau GitHub SSO.
- **Informasi**: Memberitahu bahwa akun telah dibuat dan cara mengatur password lokal jika diperlukan di masa depan.

### 4. Akun Sosial Terhubung
- **File**: `emails/social-account-linked.blade.php`
- **Kegunaan**: Notifikasi keamanan saat akun sosial (Google/GitHub) dihubungkan ke akun lokal yang sudah ada.

---

## Mekanisme Pengiriman (Queues)

Agar tidak memperlambat waktu respon login (UX), seluruh pengiriman email dilakukan secara asinkron menggunakan antrean (**Laravel Queues**).

**Konfigurasi di `.env`:**
```env
MAIL_MAILER=smtp
QUEUE_CONNECTION=redis
```

**Service Layer Logic:**
```php
// Contoh pengiriman email di Service
Mail::to($user)->queue(new MfaOtpMail($otpCode));
```

::: tip Operasional Worker
Pastikan container `worker` di Docker selalu berjalan. Anda bisa memantau antrean email melalui log container worker:
```bash
docker compose logs -f worker
```
:::

---

## Kustomisasi Desain

Jika Anda ingin mengubah tampilan email (Logo, Warna, Footer):
1. Buka file template blade yang relevan.
2. Kami menggunakan inline CSS untuk memastikan kompatibilitas di berbagai email client (Gmail, Outlook, Apple Mail).
3. Anda dapat menggunakan komponen `@component('mail::message')` standar Laravel atau desain HTML kustom yang tersedia di folder `emails/layouts`.
