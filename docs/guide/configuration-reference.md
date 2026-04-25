# Referensi Konfigurasi Sistem

Seluruh konfigurasi utama AI Auth System diatur melalui _Environment Variables_ (`.env`) yang terletak di root direktori proyek. Halaman ini menjelaskan fungsi spesifik dari masing-masing variabel yang berfokus pada autentikasi, keamanan, dan integrasi _third-party_.

## Database & Redis

Sistem memerlukan penyimpanan memori berkecepatan tinggi (Redis) untuk menangani eksekusi _Rate Limiting_ dan _Session Storage_.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_auth
DB_USERNAME=root
DB_PASSWORD=

# Redis Wajib Diaktifkan untuk Performa dan Rate Limiting
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## AI Risk Engine

Konfigurasi untuk berkomunikasi dengan microservice FastAPI.

```env
# URL base untuk Risk Engine
AI_RISK_ENGINE_URL=http://localhost:8000

# Nilai batas (threshold) penentuan risiko [0.0 - 1.0]
# Jika skor AI melewati batas ini, status diklasifikasikan Medium/High
AI_RISK_THRESHOLD_MEDIUM=0.41
AI_RISK_THRESHOLD_HIGH=0.80

# Waktu tunggu maksimum (dalam detik) sebelum Laravel menganggap layanan AI mati (Timeout)
AI_RISK_TIMEOUT=3
```

## Parameter Keamanan Sesi

Pengaturan yang mengontrol masa hidup token dan perilaku sesi.

```env
# Panjang waktu sesi web sebelum expired (dalam menit)
SESSION_LIFETIME=120

# Apakah sesi harus otomatis invalid jika browser ditutup?
SESSION_EXPIRE_ON_CLOSE=true

# Batas login pre-auth rate limiter
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=1
```

## Gateway Komunikasi (Fonte & SMTP)

Pengaturan untuk pengiriman OTP. Untuk Fonte Gateway, kredensial sebaiknya disimpan di database jika menggunakan fitur [Fonte Token Migration](/guide/operations), tetapi jika menggunakan `.env`:

```env
# SMTP Konfigurasi Email
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="security@aiauth.local"
MAIL_FROM_NAME="${APP_NAME} Security"

# Fonte WhatsApp Gateway (Untuk OTP via WA)
FONTE_API_URL=https://api.fonnte.com/send
FONTE_TOKEN=your-secret-fonnte-token
```

## Social Authentication (SSO)

Kredensial OAuth 2.0 untuk Google dan GitHub. Kredensial ini dapat diakses di menu konsol pengembang masing-masing penyedia (Google Cloud Console & GitHub Developer Settings).

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"
```

::: tip Mengelola Perubahan Konfigurasi
Setiap kali Anda melakukan perubahan besar pada `.env`, pastikan Anda me-reset cache konfigurasi dengan menjalankan:
```bash
php artisan config:clear
php artisan cache:clear
```
:::
