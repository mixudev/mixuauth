# Arsitektur & Struktur Direktori SSO

Modul SSO di dalam arsitektur AI-Auth ini dirancang sepenuhnya secara *modular*, berlokasi di `app/Modules/SSO/`. Pendekatan ini menjaga agar logika autentikasi silang (cross-app auth) terisolasi dan mudah di-maintain.

## Struktur Folder (`app/Modules/SSO`)

Berikut adalah komponen kunci yang mendukung modul SSO:

```text
app/Modules/SSO/
├── Controllers/
│   ├── Admin/
│   │   ├── AccessAreaController.php  // CRUD Area Akses
│   │   ├── ApplicationController.php // Laporan aplikasi & statistik
│   │   └── SsoClientController.php   // Manajemen registrasi klien
│   ├── OAuthController.php           // Mengambil alih flow Authorize Passport
│   ├── SsoLogoutController.php       // Endpoint logout & trigger webhook
│   └── UserInfoController.php        // Endpoint profil (/api/user)
├── Jobs/
│   └── SendGlobalLogoutWebhookJob.php // Queue: Kirim webhook ke klien
├── Models/
│   ├── AccessArea.php                // Representasi Area Akses
│   ├── PassportClient.php            // Override model Passport (auto-approve)
│   └── SsoClient.php                 // Meta-data klien SSO lokal
├── Services/
│   └── SsoAuditService.php           // Pencatat log audit SSO
└── SSOServiceProvider.php            // Registrasi rute, scopes, dan middleware
```

## Penjelasan Controller Utama

### 1. `OAuthController`
Sebagai jantung dari penegakan keamanan. Saat aplikasi *redirect* ke `/oauth/authorize`, sistem **tidak** langsung membiarkan Passport memprosesnya. Request akan dihadang oleh controller ini untuk melakukan:
- Validasi PKCE (`code_challenge`).
- Validasi *State Parameter*.
- Pengecekan status *Active* klien.
- Validasi strict `redirect_uri`.
- **Enforcement Access Area** (Apakah user punya akses ke klien ini?).

Jika semua cek *passed*, controller ini baru meneruskan (*delegate*) *request* tersebut ke *AuthorizationController* milik Passport.

### 2. `UserInfoController`
Endpoint `/api/user` adalah tujuan klien setelah berhasil mendapatkan *Access Token*.
- Mengembalikan JSON profil *user*.
- Mengimplementasikan **Client Inactive Guard**. Sebelum JSON dikembalikan, sistem akan mengecek apakah ID Klien dari token yang digunakan masih "Aktif". Jika tidak, token akan di-*revoke* (cabut) dan mengembalikan respons `403 Forbidden`.

### 3. `SsoLogoutController`
Digunakan ketika *user* *logout* melalui klien atau server pusat.
- Melakukan *revoke* (cabut) *Access Token* yang sedang digunakan.
- Me- *trigger* **SendGlobalLogoutWebhookJob** yang akan men-*dispatch* HTTP POST asinkron ke `webhook_url` milik klien.

## Skema Database

Sistem SSO ini menambahkan tabel spesifik di luar tabel bawaan Passport (`oauth_clients`, `oauth_access_tokens`, dll).

### `sso_clients`
Tabel relasional *one-to-one* dengan `oauth_clients`. Menyimpan *metadata* tambahan klien.
- `oauth_client_id` (FK)
- `webhook_url` (Endpoint untuk menerima Global Logout)
- `webhook_secret` (Kunci rahasia untuk HMAC Signature)
- `is_active` (Status aktif aplikasi)

### `access_areas`
Tabel master daftar *Area Akses* (misal: "Keuangan", "Akademik", "Admin").
- `name`, `slug`, `description`.

### Tabel Pivot
Terdapat dua tabel *many-to-many* untuk relasi *Access Area*:
1. `user_access_area`: Memetakan *User* ke *Access Area* (Area apa saja yang dimiliki pengguna).
2. `sso_client_access_area`: Memetakan *Klien SSO* ke *Access Area* (Area apa saja yang diwajibkan oleh aplikasi).
