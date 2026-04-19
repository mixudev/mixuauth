# Panduan Instalasi Sistem

Panduan standar ini menjelaskan tahapan implementasi infrastruktur AI Auth System dari tahap inisiasi (*scratch*) menggunakan mekanisme orstrasi *Docker Container*.

## Syarat Pratinjau Lingkungan Dasar

Pastikan mesin inang (Server Host) spesifikasinya memenuhi dan memiliki layanan pra-syarat berikut:

| Infrastruktur Dependensi | Spesifikasi Minimum | Tautan Unduhan |
|------|--------------|------|
| Docker Engine | 20.10+ | [Dokumentasi Docker](https://docs.docker.com/get-docker/) |
| Docker Compose plugin | 2.0+ | Terpaket bersama Engine/Desktop |
| Git Version Control | 2.x | [Unduhan Git](https://git-scm.com/downloads) |

Pengujian verifikasi lingkungan standar:
```bash
docker --version          # Mengharapkan Docker version 24.x.x
docker compose version    # Mengharapkan Docker Compose version v2.x.x
git --version             # Mengharapkan git version 2.x.x
```

## Tahapan 1: Ekstraksi Repositori Inti

Lakukan penduplikatan source code dari penyedia VCS:

```bash
git clone https://github.com/referensi-perusahaan/ai-auth-system.git
cd ai-auth-system
```

## Tahapan 2: Penetapan Variabel Lingkungan Rahasia

Templat bawaan environment akan digandakan secara otomatis saat melakukan eksekusi otomasi `setup.sh`. Namun, administrator **diwajibkan untuk meninjau dan mensuplai konfigurasi manual SMTP/Email relay** guna menstabilkan fitur pengiriman OTP.

Lakukan penyalinan inisiasi lokal:

```bash
# Penggandaan templat
cp .env.example .env
cp laravel-auth-ai/.env.example laravel-auth-ai/.env
cp ai-security/.env.example ai-security/.env
```

Penyuntingan kredensial SMTP (*Harap modifikasi `laravel-auth-ai/.env` secara aman*):

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=noreply@domain-anda.com
MAIL_PASSWORD=secret_app_password
MAIL_FROM_ADDRESS=noreply@domain-anda.com
MAIL_FROM_NAME="Portal Akses AI System"
```

> **Perhatian Khusus Konfigurasi Celah Transaksi**
> Kegagalan injeksi otentikasi SMTP yang tervalidasi akan memecah fungsi antrean prioritas dan mengakibakan paralisis fungsional OTP.

## Tahapan 3: Proses Orstrasi dan Persiapan (*Bootstrapping*)

Skrip pengelola tingkat eksekutif (.`setup.sh`) berfungsi mengabaikan rutinitas berulang pembangunan *stack*.

```bash
chmod +x setup.sh
./setup.sh
```

Manifest operasional otomatis yang dijalankan skrip:
1. Validasi proksimitas jaringan eksternal dan kapabilitas Daemon Docker.
2. Inisiasi acak **kredensial entropi keamanan tinggi** untuk layanan (REDIS_PASSWORD, MYSQL_ROOT_PASSWORD, DB_PASSWORD).
3. Evaluasi pemetaan Tree Directory dan *Log Dumps*.
4. Kompilasi mandiri seluruh citra *Docker image* berlapis (Stage Builder).
5. Inisiasi persistensi RDBMS & memori Redis dalam kontainer parsial.
6. Penarikan dependensi pustaka via Composer tanpa konflik dependensi lintas OS.
7. Produksi kriptografi `APP_KEY` untuk enkripsi data sisi klien.
8. Sinkronisasi persistensi struktur skema (*Database Migrations*).
9. Pengoptimalan perutean aplikasi.
10. Validasi kesehatan (Health-check) servis pasca-eksekusi.

Estimasi temporal proses: **3–10 menit** (Metrik durasi bergantung kepada latensi bandwidth dan performa komputasi Thread mesin inang).

## Tahapan 4: Pemantauan Alamat Gerbang Aplikasi

Sistem beroperasi tanpa gangguan ketika tabel layanan terekspos pada rute terminal lokal (Host):

| Komponen Terisolasi | Tautan Gerbang Resolusi |
|---------|-----|
| Antarmuka Laravel App | http://localhost:8080 |
| Koneksi Langsung Laravel API | http://localhost:8080/api |
| Pemeriksaan Kesahatan FastAPI | http://localhost:8000/health |
| Konsol Manajerial SQL | http://localhost:8081 |
| **Arsip Dokumentasi Luring** | http://localhost:8090 |

## Operasi Diagnosis Reguler

Manajemen pemantauan logs dan siklus komputasi per *container*:

```bash
# Evaluasi status runtime layanan
docker compose ps

# Audit logs dari terminal aplikasi PHP 
docker compose logs -f app

# Audit rekam jejak performa mesin FastAPI
docker compose logs -f fastapi-risk

# Memulai ulang siklus satu layanan spasial
docker compose restart app

# Pemusnahan container stack teragregasi
docker compose down

# *Purge* infrastruktur absolut (termasuk penyimpanan presisten)
docker compose down -v
```

## Troubleshooting

### Port sudah digunakan

```bash
# Cek proses yang menggunakan port
netstat -tulpn | grep 8080

# Ganti port di docker-compose.yml jika konflik
```

### Container gagal start

```bash
# Lihat error detail
docker compose logs app
docker compose logs db

# Build ulang dari scratch
docker compose build --no-cache
docker compose up -d
```

### Reset database

```bash
docker compose down -v   # Hapus volume
./setup.sh               # Setup ulang dari awal
```
