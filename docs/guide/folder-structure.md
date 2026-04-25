# Arsitektur Direktori & Struktur Proyek

Halaman ini menyajikan panduan mendalam mengenai hirarki direktori **AI Auth System**. Proyek ini menggunakan pendekatan *Monorepo* yang menyatukan infrastruktur, layanan backend, dan mesin kecerdasan buatan dalam satu repositori terkontrol.

## Hirarki Root Proyek

```text
.
├── ai-security/             # AI Risk Engine (FastAPI Microservice)
├── docker/                  # Orkestrasi Infrastruktur & Runtime
│   ├── laravel/             # Konfigurasi Lingkungan PHP-FPM
│   ├── nginx/               # Konfigurasi Web Server Reverse Proxy
│   └── mysql/               # Optimasi Storage Database
├── docs/                    # Technical Documentation (VitePress)
├── laravel-auth-ai/         # Core Application (Laravel 10 Framework)
├── docker-compose.yml       # Definisi Multi-Container Orchestration
├── setup.sh                 # Otomasi Inisialisasi Environment
└── README.md                # Panduan Memulai Cepat
```

---

## 1. Backend Core: `laravel-auth-ai/`

Aplikasi utama dibangun dengan prinsip **Domain-Driven Design (DDD)** yang dimodifikasi ke dalam struktur **Modular**.

### Struktur Modular (`app/Modules/`)
Setiap modul di bawah ini bersifat otonom dan memiliki tanggung jawab tunggal (*Single Responsibility*).

- **Authentication/**: Gerbang utama akses. Berisi logika login (Web/API), pendaftaran, dan manajemen sesi.
- **Security/**: Komponen paling vital. Menangani kalkulasi risiko, *device fingerprinting*, integrasi dengan AI Engine, serta mekanisme pemblokiran (*IP/User Blacklist*).
- **Communication/**: Abstraksi layanan pengiriman kode OTP via Email (SMTP) dan WhatsApp (Fonnte).
- **Identity/**: Manajemen entitas pengguna, profil, dan histori perangkat tepercaya.
- **Authorization/**: Implementasi Role-Based Access Control (RBAC).

### Folder Krusial Lainnya
- `app/Models/`: Definisi entitas data global (misal: `User.php`).
- `database/migrations/`: Seluruh skema database yang mendefinisikan struktur keamanan data.
- `resources/views/`: Lokasi template frontend (Blade) dan template email profesional.
- `routes/`: Folder ini hanya berisi *entry point* global; rute spesifik fitur didefinisikan di dalam masing-masing modul.

---

## 2. AI Intelligence: `ai-security/`

Layanan ini berfungsi sebagai "Hakim" keamanan yang menentukan status risiko sebuah percobaan login.

- `app/api/`: Definisi kontrak REST API untuk integrasi dengan Laravel.
- `app/core/`: Berisi logika algoritma penilaian risiko (*Risk Scoring Logic*).
- `app/models/`: Lokasi penyimpanan model machine learning yang sudah dilatih (*pre-trained models*).
- `logs/`: Rekam jejak audit internal mesin AI untuk kebutuhan *fine-tuning*.

---

## 3. Infrastruktur & DevOps: `docker/`

Memastikan lingkungan pengembangan (*Development*) dan produksi (*Production*) tetap identik.

- **`laravel/Dockerfile`**: Menggunakan *Multi-stage build* untuk mengoptimalkan ukuran image, menyertakan ekstensi PHP krusial seperti `bcmath`, `gd`, `redis`, dan `pdo_mysql`.
- **`nginx/default.conf`**: Mengatur jalur komunikasi *fastcgi* ke aplikasi Laravel serta menangani optimasi pengiriman aset statis.
- **`docker-compose.yml`**: Menghubungkan seluruh layanan (App, DB, Redis, AI Engine, Docs) ke dalam jaringan internal yang terisolasi untuk keamanan maksimal.

::: tip Standar Penamaan
Kami mengikuti standar **PSR-12** untuk penamaan file dan kode di sisi PHP, serta mengikuti konvensi **PEP 8** untuk sisi Python di AI Engine. Hal ini memastikan kode tetap mudah dibaca oleh kontributor baru.
:::
