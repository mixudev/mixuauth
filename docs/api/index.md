# Spesifikasi API Inti

Bagian dokumentasi ini mendefinisikan rincian kontrak konektivitas (_API Contract_) antara sistem klien (Front-End/SPA/Aplikasi Mobile) dengan arsitektur **AI Auth System Backbone**. Sistem menggunakan standar representasi arsitektur **RESTful API** dengan pertukaran format berbasis `JSON`.

---

## Ketetapan URL Basis Tunggal

Bergantung kepada _environment_ penggelaran sistem, klien dilarang memanggil URL root (`/`) melainkan harus selalu merujuk alamat ke suplemen prefiks `/api`.

```text
http://<domain-inang>:8080/api
```

---

## Standar Tajuk Permintaan (Headers)

Seluruh permintaan ke terminal API disyaratkan melampirkan parameter kepala spesifik di bawah ini. Absennya _headers_ krusial akan memicu tolakan akses instan bersandi `406 Not Acceptable`.

| Argumen Header | Ketetapan Nilai | Tingkat Kepentingan | Penjelasan |
|---------|-------|-------|------------|
| `Accept` | `application/json` | **Wajib** | Memastikan bahwa lapisan pengurai Laravel merespons dengan MIME type data `JSON`, alih-alih merender HTML _Error Views_. |
| `Content-Type` | `application/json` | Bersyarat | Sangat diwajibkan bila skema permintaan melibatkan muatan (metode `POST`, `PUT`, `PATCH`). |
| `X-Device-Fingerprint` | *UUID Dinamik* / *Sandi Hash* | Dirujuk Cerdas (AI) | String identifikasi piranti peramban penjelajah dari pengguna (Dapat berupa SHA-256 _hash user-agent_ klien). Krusial bagi evaluasi risiko AI Edge. |
| `Authorization` | `Bearer <token>` | **Wajib (Rute Tertutup)** | Otorisasi sesi _JSON Web Token_ (JWT). Diberikan usai keberhasilan autentikasi pintu rute masuk utama. |

---

## Spesifikasi Formulasi Balasan (Response Envelope)

Sebagai strategi sentralisasi sistem pendataan, balasan yang diberikan dari arsitektur backend dilaraskan dalam bungkus *envelope* tunggal.

### 1. Respons Keberhasilan Akses (`200 OK` / `201 Created`)

```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "role": "admin"
  },
  "message": "Operasi selesai dengan akurat."
}
```

### 2. Respons Kegagalan Terencana (`4xx` Client Error)

```json
{
  "success": false,
  "message": "Kredensial yang diberikan gagal melewati mekanisme keamanan.",
  "errors": {
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## HTTP Status Codes

| Code | Arti |
|------|------|
| `200` | OK — Request berhasil |
| `201` | Created — Resource berhasil dibuat |
| `401` | Unauthorized — Token tidak valid / tidak ada |
| `403` | Forbidden — Tidak punya izin |
| `422` | Unprocessable Entity — Validasi gagal |
| `429` | Too Many Requests — Rate limit tercapai |
| `500` | Internal Server Error |

## Rate Limiting

| Endpoint | Limit | Window |
|----------|-------|--------|
| `POST /auth/login` | 10 req | 1 menit |
| `POST /auth/register` | 5 req | 1 menit |
| `POST /auth/verify-otp` | 5 req | 1 menit |
| `POST /auth/resend-otp` | 3 req | 1 menit |
| Endpoint lain (authenticated) | 60 req | 1 menit |

Saat limit tercapai, response akan berisi header:

```http
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
Retry-After: 45
```

## Daftar Endpoint

| Endpoint | Method | Auth | Keterangan |
|----------|--------|------|------------|
| `/auth/register` | POST | ❌ | Register akun baru |
| `/auth/login` | POST | ❌ | Login & request OTP |
| `/auth/verify-otp` | POST | ❌ | Verifikasi OTP |
| `/auth/resend-otp` | POST | ❌ | Kirim ulang OTP |
| `/auth/logout` | POST | ✅ | Logout & invalidate token |
| `/auth/me` | GET | ✅ | Data user saat ini |
| `/auth/refresh` | POST | ✅ | Refresh token |
