# Spesifikasi Endpoint (Authentication Module)

Modul otentikasi memfasilitasi penerbitan sesi izin terotorisasi (`Bearer`). Pengamanan divalidasi oleh subsistem **FastAPI Risk Endpoint** untuk memastikan tidak terjadinya infiltrasi berbahaya.

---

## 1. Permintaan Hak Masuk (Login)

Inokulasi pertukaran _credentials_ pengguna untuk peluncuran otorisasi API Token.

**Alamat Relatif:** `POST /api/auth/login`

### Atribut Payload Permintaan (JSON)

| Label | Tipe Data | Sifat | Penjabaran Logika |
|-------|-----------|-------|------------|
| `email` | String | Wajib | Kredensial surat elektronik dengan format tervalidasi. |
| `password` | String | Wajib | Frasa sandi statis. |
| `device_name` | String | Opsional | Informasi perangkat klien untuk keperluan manajemen *Multiple Devices* (default: `unknown`). |

### Skenario Balasan 1: Sukses Terverifikasi (200 OK)

Balasan yang timbul apabila skor anomali AI di bawah taraf menengah (*Low Risk*) serta kredensial cocok pada pangkalan data SQL.

```json
{
  "success": true,
  "data": {
    "token": "1|sOMeV3ryS3cur3T0k3nStr1ng...",
    "user": {
      "id": 1,
      "email": "admin@domain.com",
      "role": "admin"
    },
    "requires_mfa": false
  },
  "message": "Login berhasil diregistrasi oleh subsistem otorisasi."
}
```

### Skenario Balasan 2: Pemblokiran Pintu Berlapis (Membutuhkan MFA)

Jika skor algoritma FastAPI memberikan nilai `Medium Risk` hingga `High Risk`, klien dipaksa untuk lolos fase *Challenge* via OTP.

```json
{
  "success": true,
  "data": {
    "requires_mfa": true,
    "mfa_token": "temp_token_5828a2..."
  },
  "message": "Deteksi aktivitas tidak lumrah. Otentikasi lapis kedua (Multi-Factor) sedang dikirimkan."
}
```

---

## 2. Proses Otorisasi MFA (Mutli-Factor Validation)

Mengesahkan _challenge-token_ OTP bila status relasi sebelumnya mewajibkan (`requires_mfa: true`). 

**Alamat Relatif:** `POST /api/auth/mfa/verify`

### Atribut Payload Permintaan (JSON)

| Label | Tipe Data | Sifat | Penjabaran Logika |
|-------|-----------|-------|------------|
| `mfa_token` | String | Wajib | Deret karakter sementara (yang diperoleh dari `/login` pada Skenario 2). |
| `otp` | String | Wajib | Nilai numerik 6-digit validasi. |

### Contoh Pemanggilan via cURL

```bash
curl -X POST http://localhost:8080/api/auth/mfa/verify \
  -H "Content-Type: application/json" \
  -d '{"mfa_token": "temp_token...", "otp": "481516"}'
```

### Format Respon Kesepakatan (200 OK)

```json
{
  "success": true,
  "data": {
    "token": "2|an0th3rV3ryS3cur3T0k3n..."
  },
  "message": "Token MFA lolos evaluasi matematis. Sesi diizinkan."
}
```

---

## 3. Pemutusan Siklus Sesi (Logout)

Pembatalan fungsi *Bearer Token* di lingkungan jaringan internal (baik RAM/Redis maupun MySQL Record) dengan pendekatan *Force Invalidation*.

**Alamat Relatif:** `POST /api/auth/logout`

**Persyaratan Infrastruktur Header:**
- `Authorization`: `Bearer <token>`

### Format Balasan Terminasi (200 OK)

```json
{
  "success": true,
  "message": "Sesi telah dieliminasi sepenuhnya dari ekosistem.",
  "data": null
}
```
