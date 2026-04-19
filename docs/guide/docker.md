# Arsitektur Docker

Sistem berbasis layanan terpisah (*micro-service isolation*) ini menggunakan orkestrasi **Docker Compose** yang memilah dependensi ke dalam **7 kontainer layanan** yang berjalan di dalam jaringan tunggal jenis *bridge terisolasi* (`internal`).

## Topologi Jaringan Layanan

```text
                    ┌─────────────────────────────────┐
                    │         docker network           │
                    │           (internal)             │
                    │                                  │
  :8080  ──── nginx ──── app (php-fpm:9000)            │
  :8000  ── fastapi ─┤                                 │
  :8090  ──── docs  │   ├─ db (MySQL:3306)             │
  :8081  ── phpmyadmin  ├─ redis (Redis:6379)           │
  :3307  ──── db    │   ├─ worker (queue)               │
  :6379  ── redis   │   └─ scheduler (cron)             │
                    └─────────────────────────────────┘
```

---

## Rincian Peran Masing-Masing Layanan

### `app` — Aplikasi Inti (Laravel PHP-FPM)

```yaml
build: docker/laravel/Dockerfile
command: php-fpm
port: 9000 (internal only)
depends_on: db, redis
```

Layanan kunci yang bertugas merender fungsi _controller_ dan proses bisnis internal Laravel melalui protokol FastCGI. **Tidak diekspos langsung ke publik**—seluruh gerbang masuk ke port aplikasi FPM wajib melalui *Reverse Proxy* (Nginx).

**Pemasangan Volume (Mounts):**
- `./laravel-auth-ai:/var/www/html` — Persistensi berkas sumber.
- `vendor_data:/var/www/html/vendor` — Manajemen caching direktori ketergantungan library Composer.

---

### `nginx` — Titik Temu Gerbang Akses (Reverse Proxy)

```yaml
image: nginx:alpine
port: 8080:80
depends_on: app
```

Penerima statis *ingress route* awal. HTTP request dari lapisan transport diproses untuk disalurkan ke port `9000` di dalam lingkungan internal aplikasi. Konfigurasi kontrol rute disunting melalui profil Nginx tertanam.

---

### `worker` — Proses Antrean Latar Belakang

```yaml
build: docker/laravel/Dockerfile
command: php artisan queue:work redis --queue=notifications-high,default
```

Bertanggung jawab menuntaskan beban tugas logikal yang telah dikonversi bersifat _asynchronous_, termasuk prioritas tinggi seperti penyaluran email *One-Time Password* (OTP). Membawa fondasi dan tumpukan (*image stack*) yang sama identik secara byte dengan kontainer `app`.

---

### `scheduler` — Cron Orchestrator (Tugas Berkala)

```yaml
command: sh -c "while true; do php artisan schedule:run && sleep 60; done"
```

Subsistem berjalannya program secara rutin dan otomatis berbasis waktu (Cron daemon emulasi) pada aplikasi. Melakukan:
- Rotasi *garbage collection* entri kedaluwarsa OTP.
- Kompilasi berkala statistik dashboard intelijensi keamanan.

---

### `fastapi-risk` — Analisis Keamanan (AI-Driven)

```yaml
build: ./ai-security/Dockerfile
port: 8000:8000
```

Fasilitas infrastruktur berbasis *Python* yang mejalankan titik akses FastAPI sebagai gerbang klasifikasi keamanan berbasis model risiko AI. Dilengkapi kapabilitas sinkronisasi pemeriksaan fungsi (healthcheck tests) periodik.

---

### `db` — Basis Data Relasional MySQL

```yaml
image: mysql:8.0
port: 3307:3306
volumes: db_data
```

Gudang struktur identitas dan integritas keamanan entitas. Eksposur port eksternal menggunakan kanal `3307` untuk mitigasi interferensi dan friksi *bound-address* dari sistem Host operasional yang sudah memiliki tumpukan layanan SQL purnabawa.

---

### `redis` — Persistensi Sementara Kinerja Cepat

```yaml
image: redis:7-alpine
port: 6379:6379
command: redis-server --requirepass "$REDIS_PASSWORD"
```

Akselerator aplikasi yang direkayasa perannya untuk mencadangkan:
- Kunci autentikasi sesi dan JWT sessional cache.
- Batasan rekam jejak untuk pemicu blok *Rate-Limit*.
- Skema dasar memori penampungan antrean *Pushed Jobs*.

---

### `docs` — Sistem Distribusi Panduan Statis

```yaml
build: docker/docs/Dockerfile
port: 8090:80
```

*Static Site Generator* (VitePress). Dikompilasi dari fondasi praproses *markdown* secara masif menjadi hierarki HTML/CSS selama proses penyusunan (Image build layer caching).

---

### `phpmyadmin` — Antarmuka Manajemen Visual Database (Mode Non-Produksi)

```yaml
image: phpmyadmin/phpmyadmin
port: 8081:80
```

Dashboard panel peninjauan manual isi tabulasi sistem relasional MySQL.

> **Perhatian Konfigurasi Audit Keamanan:** Kontainer tipe eksploratif grafikal harus dibongkar atau dialihkan penempatannya saat rilis memasuki area Produksi definitif.

## Pembagian Isolasi Volume Tersimpan

| Nama Volume | Entitas Dependen | Kategori Informasi |
|--------|---------------|-----|
| `db_data` | `db` | Blok Biner Skema Persistensi Database MySQL. |
| `redis_data` | `redis` | Status Persisten RDB. |
| `vendor_data` | `app`, `worker`, `scheduler` | Kumpulan artefak Pustaka Eksternal PHP (Composer). |

## Network

Semua services ditempatkan dalam network `internal` dengan driver `bridge`. Ini memastikan services tidak dapat diakses dari luar kecuali melalui port yang eksplisit di-expose.

```yaml
networks:
  internal:
    driver: bridge
```
