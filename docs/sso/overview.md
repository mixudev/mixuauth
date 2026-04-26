# Ikhtisar SSO Server

Sistem autentikasi ini telah diperluas untuk berfungsi sebagai **Single Sign-On (SSO) Identity Provider (IdP)**. Hal ini memungkinkan berbagai aplikasi kampus (Klien) untuk menggunakan satu sumber kebenaran (Source of Truth) untuk autentikasi dan identitas pengguna, menghilangkan kebutuhan registrasi terpisah di setiap aplikasi.

## Konsep Dasar

SSO mengimplementasikan standar **OAuth 2.0** dan **OpenID Connect (OIDC)** (secara parsial melalui token JWT) untuk menyediakan sistem autentikasi terpusat. 

Alur kerja (workflow) SSO secara garis besar adalah sebagai berikut:
1. **User** mencoba mengakses aplikasi klien (misal: AIS/Siakad).
2. **Klien** belum mengenali user, lalu mengarahkan (redirect) user ke sistem SSO (`/oauth/authorize`).
3. **SSO Server** memverifikasi identitas user, termasuk memeriksa izin (Access Areas) yang diperlukan oleh aplikasi klien tersebut.
4. Jika disetujui, SSO akan mengarahkan user kembali ke klien beserta **Authorization Code**.
5. **Klien** menukarkan *Code* tersebut dengan **Access Token** di *background* (`/oauth/token`).
6. **Klien** menggunakan token tersebut untuk mengambil profil user (`/api/user`) dan menganggap user telah login.

## Mengapa Laravel Passport?

Di balik layar, modul SSO memanfaatkan paket resmi **Laravel Passport** karena beberapa alasan strategis:
- **Kompatibilitas Standar**: Passport secara bawaan sangat patuh pada spesifikasi RFC 6749 (OAuth 2.0).
- **Keamanan Bawaan**: Passport sudah teruji dalam hal manajemen *key pair* (RSA), rotasi *token*, dan implementasi enkripsi asimetris.
- **Kustomisasi Fleksibel**: Sangat mudah meng-override rute dan controller bawaan untuk menyuntikkan (inject) aturan *Access Area* sebelum token diterbitkan.

## Keuntungan Sistem SSO Terpusat
- **User Experience (UX)**: Satu kali login untuk mengakses seluruh aplikasi kampus. Tidak perlu menghafal banyak *password*.
- **Keamanan (Security)**: Profil kredensial (seperti *password* dan data sensitif lainnya) **tidak pernah dikirim** ke aplikasi klien. Klien hanya menerima token JWT sementara.
- **Manajemen Akses**: Cabut hak akses user di SSO, maka secara otomatis akses ke seluruh aplikasi (yang mewajibkan login lewat SSO) akan terputus.
- **Standarisasi**: Memudahkan pengembangan aplikasi baru karena sistem registrasi dan login sudah ditangani sepenuhnya oleh SSO Server.
