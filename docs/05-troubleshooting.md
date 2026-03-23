# 05. Kamus Error & Solusi (Troubleshooting)

Gunakan tabel ini sebagai bantuan pertama jika sistem mengalami kendala teknis.

## 📂 Tabel Error Umum

| Pesan / Kode Error | Kemungkinan Penyebab | Langkah Perbaikan |
| :--- | :--- | :--- |
| **`429 Too Many Requests`** | Serangan brute-force atau salah konfigurasi rate limit. | Cek IP pengirim. Jika legitimate, tingkatkan `RATE_LIMIT_MAX` di `.env`. |
| **`502 Bad Gateway`** | Container Laravel (PHP-FPM) mati atau sedang restart. | Jalankan `docker compose restart app`. Cek RAM server. |
| **`419 Page Expired`** | CSRF Token tidak valid (biasanya di web view). | Refresh halaman. Pastikan `SESSION_DOMAIN` di `.env` sudah benar. |
| **`INVALID_API_KEY`** | Kunci Laravel dan FastAPI tidak sinkron. | Jalankan `php artisan ai:generate-key` dan restart `fastapi-risk`. |
| **`SQLSTATE[HY000] [1045]`** | Password database di `.env` salah. | Samakan `DB_PASSWORD` dengan yang ada di `docker-compose.yml`. |

---

## 🚑 Skenario Darurat (Emergency Runbook)

### Skenario A: "Email OTP Tidak Bisa Terkirim (Server Email Down)"
Jika pengiriman OTP macet total dan user tidak bisa login:
1. Masuk ke Dashboard Admin (jika masih ada session).
2. Ubah konfigurasi "OTP Required" menjadi `false` sementara.
3. ATAU: Secara manual tandai email user sebagai `email_verified_at` di database untuk bypass OTP.

### Skenario B: "Sistem Terasa Sangat Lambat"
1. Cek penggunaan Redis: `docker compose exec redis redis-cli info memory`.
2. Kosongkan queue jika membengkak: `docker compose exec app php artisan queue:flush`.
3. Restart sistem: `docker compose restart`.

### Skenario C: "Hacker Melakukan Serangan Massal (DDoS/Brute-force)"
1. Identitas IP penyerang melalui log: `docker compose logs nginx | grep "POST /api/auth/login"`.
2. Gunakan Firewall OS host (UFW/Iptables) untuk memblokir IP tersebut secara permanen.
3. Naikkan durasi decay rate limit di Laravel menjadi 60 menit.

---

## 📊 Memeriksa Kesehatan AI (Health Check)
Gunakan perintah ini untuk memastikan AI engine dalam kondisi prima:
```bash
curl -i http://localhost:8000/health
```
**Analisis Respons:**
- `status: ok` -> Normal.
- `model_loaded: true` -> Sistem menggunakan kecerdasan penuh.
- `model_loaded: false` -> Sistem menggunakan Rule-based fallback (lakukan retrain segera).
