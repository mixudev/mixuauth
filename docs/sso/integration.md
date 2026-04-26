# Panduan Integrasi Klien

Dokumen ini menjelaskan bagaimana cara menghubungkan aplikasi/klien dengan Server SSO AI-Auth Anda. Untuk contoh di bawah ini, diasumsikan klien menggunakan *package* otentikasi standar yang mendukung **PKCE** dan **OAuth2 Authorization Code Grant**.

## 1. Mendaftarkan Klien di SSO Server

Sebelum klien dapat terkoneksi, Admin SSO harus mendaftarkan aplikasi tersebut:
1. Login ke Dashboard SSO sebagai Administrator.
2. Navigasi ke menu **SSO > Klien Terdaftar**.
3. Klik **+ Klien Baru**.
4. Isi `Nama Klien`, `Redirect URI` (misal: `https://klien.test/auth/callback`), dan `Webhook URL` (misal: `https://klien.test/webhook/sso-logout`).
5. (Opsional) Klik tab **Access Area** dan tentukan area mana yang harus dimiliki *user* untuk dapat *login*.
6. Simpan klien. Server akan memunculkan popup berisi **Client ID**, **Client Secret**, dan **Webhook Secret**. Pastikan untuk meng- *copy* data tersebut.

## 2. Implementasi Endpoint OAuth

### A. Authorize Endpoint (Inisiasi Login)
Klien mengarahkan pengguna ke SSO Server untuk menyetujui *login*.
**Endpoint**: `GET /oauth/authorize`
**Parameter Wajib**:
- `client_id`: ID Klien.
- `redirect_uri`: URI callback persis seperti yang didaftarkan.
- `response_type`: Harus `code`.
- `state`: String acak (*CSRF protection*).
- `code_challenge`: Hash S256 dari string rahasia PKCE.
- `code_challenge_method`: Harus `S256`.

### B. Token Endpoint (Menukar Code dengan Token)
Setelah *user* *login* dan kembali ke `redirect_uri` klien beserta parameter `code`, Klien mengirim *request server-to-server* untuk menukar *code* tersebut.
**Endpoint**: `POST /oauth/token`
**Parameter Wajib**:
- `grant_type`: `authorization_code`
- `client_id` & `client_secret`
- `redirect_uri`: (Harus sama dengan sebelumnya)
- `code`: Code yang didapat.
- `code_verifier`: String rahasia asli dari *code_challenge* PKCE.

### C. Mengambil Profil Pengguna
Setelah mendapatkan `access_token`, klien mengambil data *user* dengan menyematkan header `Authorization: Bearer <access_token>`.
**Endpoint**: `GET /api/user`

**Contoh Response:**
```json
{
  "id": 1,
  "name": "Administrator",
  "email": "admin@example.com",
  "avatar": "https://avatar.url/...",
  "is_active": true,
  "roles": ["super_admin", "staff"],
  "access_areas": ["keuangan", "akademik"]
}
```
*Catatan:* Jika klien tiba-tiba dinonaktifkan oleh *Admin SSO*, *request* ke `/api/user` akan merespons `403 Forbidden` dan token akan otomatis di-*revoke*. Klien harus memproses status 403 ini dengan melakukan *logout* lokal pada aplikasinya.

## 3. Menangani Webhook Global Logout

Untuk mendukung fitur **Global Logout** (ketika *user* *logout* di sistem pusat, ia juga akan di-*logout* di seluruh aplikasi klien), klien harus memiliki endpoint (misal: `POST /webhook/sso-logout`) untuk menerima data *webhook*.

### Payload Webhook
```json
{
  "event": "global_logout",
  "user_id": 1,
  "email": "admin@example.com",
  "timestamp": 1714120352,
  "issued_at": "2026-04-26T08:32:32Z"
}
```

### Validasi Keamanan Klien (Wajib Diimplementasikan)
Klien **TIDAK BOLEH** mempercayai payload *webhook* mentah. Klien harus melakukan verifikasi ganda:

1. **Replay Attack Prevention**:
   Ambil data `timestamp` dari payload (atau dari Header `X-SSO-Timestamp`). Bandingkan dengan Unix *timestamp* saat ini di sisi klien. Jika selisihnya lebih dari **300 detik (5 menit)**, **tolak webhook tersebut**.
2. **Signature Verification**:
   Server SSO menghitung *HMAC-SHA256* dari *raw body JSON* menggunakan `Webhook Secret`. Hasilnya dikirim di header `X-SSO-Signature`. Klien harus menghitung ulang hash dari *raw body* yang diterima dan membandingkannya (*timing safe equals*) dengan header tersebut.
   
```php
// Contoh Pseudo-code Validasi PHP
$payload = file_get_contents('php://input');
$signature = hash_hmac('sha256', $payload, $webhookSecret);

if (!hash_equals($signature, $request->header('X-SSO-Signature'))) {
    abort(401, 'Invalid Signature');
}
```
Menerima *webhook* yang valid berarti klien harus segera menghapus *session* lokal milik *user* dengan ID/Email tersebut.
