# Referensi Variabel Lingkungan (.env)

Dokumentasi ini merinci seluruh konfigurasi konstanta sistem (_environment variables_) yang mengatur sirkulasi data antar layanan di arsitektur Docker Compose.

---

## Kedudukan File `.env`

Terdapat tiga lapisan berkas konfigurasi pada repositori ini:

| Lokasi File | Mengintervensi Layanan | Keterangan |
|------|---------------|------------|
| `.env` (Root) | Docker Compose Daemon | Mendeklarasikan profil keamanan level infrastruktur mesin Host. |
| `laravel-auth-ai/.env` | Laravel FPM App & CLI | Memuat konfigurasi kunci keamanan, pangkalan data, dan SMTP. |
| `ai-security/.env` | Python FastAPI | Skoring batas *AI Risk Threshold* dan kredensial API Key internal. |

---

## Root `.env` (Tingkat Docker Infrastruktur)

File ini digunakan secara eksklusif oleh rantaian `docker-compose.yml` untuk penetapan argumen ketika tahapan *Container Spawning*.

```ini
# Akses Tertinggi Database Induk
MYSQL_ROOT_PASSWORD=     # Dialokasikan otomatis oleh setup.sh

# Pembatasan Akses Storage In-Memory
REDIS_PASSWORD=          # Dialokasikan otomatis oleh setup.sh

# Kredensial Isolasi Layanan Database
DB_PASSWORD=             # Dialokasikan otomatis oleh setup.sh
```

> **Catatan Orkestrasi Otomatis:**
> Mohon untuk **tidak menetapkan string secara manual** pada nilai di atas apabila ini adalah instalasi pertama. Skrip `setup.sh` telah diprogram untuk mendistribusikan entropi string di luar jangkauan _dictionary attack_ menggunakan `/dev/urandom`.

---

## Backend `.env` — `laravel-auth-ai/.env`

### Spesifikasi Persistensi Data (Database)

```ini
DB_CONNECTION=mysql
DB_HOST=db              # FQDN Docker Service Internal
DB_PORT=3306            # Kanal Port MySQL Native (Bukan 3307 dari host!)
DB_DATABASE=auth_db
DB_USERNAME=auth_user
DB_PASSWORD=            # Harus presisi sama dengan Root .env DB_PASSWORD
```

### Mekanisme Sesi dan Caching (Redis)

```ini
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120    # Durasi persistensi sesi aktif (Menit)

REDIS_HOST=redis        # FQDN Docker Service Internal
REDIS_PASSWORD=         # Harus presisi sama dengan Root .env REDIS_PASSWORD
REDIS_PORT=6379
```

### Relasi Manajemen Antrean (Queue)

```ini
QUEUE_CONNECTION=redis
QUEUE_FAILED_TABLE=failed_jobs
```

### Relay SMTP (Pengiriman Surel OTP) — Wajib Distel Manual

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=noreply@doman-anda.com
MAIL_PASSWORD=api-key-smtp-khusus
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@domain-anda.com
MAIL_FROM_NAME="Portal Sistem Keamanan"
```

> **Konfigurasi Akun Aplikasi Eksternal:**
> Sangat disarankan untuk tidak menggunakan *password* utama surel (seperti sandi reguler Gmail) demi alasan keamanan operasional. Silakan pergunakan dukungan pembuatan *App Passwords* atau layanan kurir pihak ketiga seperti AWS SES/Mailgun.

### Jaringan Distribusi Intelijensi API (FastAPI)

```ini
AI_SERVICE_URL=http://fastapi-risk:8000   # Jaringan internal tak terekspos
AI_SERVICE_TIMEOUT=5                       # Toleransi latensi (Detik)
AI_API_KEY=                               # Diinisiasi oleh Artisan Generate
```

### Restriksi Lalu Lintas & Filter Ancaman (Security Limiters)

```ini
RATE_LIMIT_LOGIN=10          # Laju ambang per-permintaan masuk per 60 detik
RATE_LIMIT_OTP=5             # Laju ambang emisi OTP beruntun
CAPTCHA_RISK_THRESHOLD=60    # Pemicu tantangan akses sekunder manual (High Risk)
BLOCK_RISK_THRESHOLD=85      # Isolasi persisten dari layanan jika melebihi batas (Critical)

OTP_EXPIRY_MINUTES=5         # Interval peluruhan OTP sebelum invalidasi
OTP_MAX_ATTEMPTS=3           # Toleransi laju kesalahan penebakan OTP
```

### Informasi Aplikasi Domain Utama

```ini
APP_NAME="Secure Auth Service"
APP_ENV=production           # Harus 'production' dalam deployment eksternal
APP_KEY=                     # Diinisiasi oleh perintah `php artisan key:generate`
APP_DEBUG=false              # Mutlak `false` untuk mencegah kebocoran jejak (stack errors)
APP_URL=https://sistem.domain-anda.com
```

---

## AI Edge Service `.env` — `ai-security/.env`

```ini
# Parameter Operasional Modul
APP_ENV=production
LOG_LEVEL=info

# Restriksi Antar-Layanan
AI_API_KEY=              # Harus presisi identik dengan Laravel AI_API_KEY

# Direktori & Model Evaluator
RISK_MODEL_PATH=/app/app/models/risk_model.pkl
RISK_THRESHOLD_LOW=30
RISK_THRESHOLD_MEDIUM=60
RISK_THRESHOLD_HIGH=80

# Alamat Server Uvicorn 
HOST=0.0.0.0
PORT=8000
WORKERS=2
```

---

## Relasi Pemetaan Port Layanan (Ringkasan Ingress)

| Argumen Lingkungan | Ketetapan Awal | Peranan Fungsional |
|----------|---------|------------|
| `APP_URL` | http://localhost:8080 | Eksposur Aplikasi PHP-FPM publik dari Reverse Proxy. |
| `DB_PORT` | 3306 | Komunikasi koneksi SQL internal dalam kontainer Docker. |
| `REDIS_PORT` | 6379 | Komunikasi koneksi memori persisten internal. |
| `AI_SERVICE_URL` | http://fastapi-risk:8000 | Panggilan inferensi model AI tertutup dari ruang FPM. |
