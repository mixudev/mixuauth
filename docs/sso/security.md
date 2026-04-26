# Access Area & Security Layers

Sistem SSO ini dirancang dengan mentalitas **Zero Trust** dan implementasi pertahanan berlapis (defense-in-depth). Kami telah mengimplementasikan **10 Lapis Pengerasan Keamanan** standar industri untuk memastikan identitas *user* dan *token* aman dari berbagai ancaman siber.

## 1. Access Area Enforcement (AND Logic)
Tidak semua orang yang punya akun di kampus boleh masuk ke semua aplikasi. Fitur ini memungkinkan admin mewajibkan *Access Area* tertentu untuk mengakses sebuah klien.

**Cara Kerja (Strict AND Logic):**
- Jika Klien `A` mensyaratkan Area `X` dan Area `Y`.
- User harus memiliki **kedua** Area (`X` dan `Y`) untuk bisa masuk. Jika hanya punya `X`, akses ditolak.
- Jika Klien tidak diberi restriksi area, statusnya menjadi **Open Client** (siapapun yang berstatus aktif boleh *login*).

## 10 Lapis Keamanan (Security Hardening)

Berikut adalah 10 lapisan keamanan yang diterapkan pada *flow* SSO:

### Lapis 1: PKCE Enforcement
*Proof Key for Code Exchange* (PKCE) diwajibkan. Klien harus mengirimkan `code_challenge` (menggunakan metode S256) saat me-*request* autorisasi. Ini melindungi dari serangan *Authorization Code Interception* pada aplikasi SPA atau *mobile*.

### Lapis 2: State Parameter Validation
Setiap inisiasi *login* mewajibkan parameter `state` untuk mencegah serangan CSRF *(Cross-Site Request Forgery)* saat *OAuth flow*.

### Lapis 3: Strict Redirect URI Matching
Mencegah serangan *Open Redirect*. URI yang diminta (di- *request*) harus *exact match* (sama persis karakter demi karakter) dengan daftar URI yang didaftarkan pada saat registrasi klien.

### Lapis 4: Inactive Client Guard (Realtime)
Ketika klien dinonaktifkan oleh *Admin* di *Dashboard*:
1. *Login* baru akan segera ditolak.
2. Jika *user* telanjur punya *Access Token* yang masih aktif dan mencoba mengakses `/api/user`, sistem mendeteksi status "nonaktif" klien.
3. Token tersebut akan langsung **di-revoke (dihanguskan)** dan *user* diberi respons `403 Forbidden`.

### Lapis 5: Webhook Replay Prevention (Timestamping)
Saat terjadi *Global Logout*, server mengirim *webhook*. Untuk mencegah *hacker* menangkap (intercept) dan mengirim ulang (*replay*) data *webhook* tersebut untuk me-*logout* akun orang secara acak, payload webhook disisipi Unix Timestamp `X-SSO-Timestamp`. Klien diwajibkan menolak *webhook* yang berumur di atas 5 menit.

### Lapis 6: Comprehensive Rate Limiting
Sistem SSO membatasi *request* secara ekstrem untuk menangkis *DDoS* dan *Brute-force*:
- `/oauth/authorize`: Max **30 request/menit** per IP (Mencegah spam *consent*).
- `/oauth/token`: Max **10 request/menit** per IP (Mencegah tebak *code* atau *refresh_token*).
- `/api/*` (misal `/api/user`): Max **60 request/menit** per *Access Token*.

### Lapis 7: SsoSecurityHeadersMiddleware
Semua *response* yang keluar dari *route* SSO telah dilengkapi dengan proteksi HTTP Headers:
- `X-Frame-Options: DENY` (Anti Clickjacking).
- `X-Content-Type-Options: nosniff`.
- `Content-Security-Policy` yang ketat (Hanya mengizinkan muatan aset dari *self* atau sumber font terpercaya).
- `Strict-Transport-Security` (HSTS).

### Lapis 8: Dynamic CORS Hardening
Sistem tidak lagi menggunakan wildcard `*` untuk CORS (`Access-Control-Allow-Origin`). Konfigurasi CORS hanya akan mengizinkan origin (domain) milik Klien SSO yang statusnya **sedang aktif**. Jika klien dinonaktifkan, origin-nya otomatis dihapus dari *whitelist* CORS.

### Lapis 9: Minimal Privilege Scopes
Passport dikonfigurasi untuk hanya merespons *scopes* (`profile`, `areas`, `logout`). Klien tidak dapat secara arogan me-*request* *full access* atau izin lain yang tidak diregistrasi.

### Lapis 10: Audit Logging Terpusat
Setiap insiden ditolak (karena area salah, PKCE gagal, URL salah) maupun keberhasilan diterbitkannya token akan dicatat oleh `SsoAuditService` ke tabel `audit_logs` secara transparan untuk investigasi *security*.
