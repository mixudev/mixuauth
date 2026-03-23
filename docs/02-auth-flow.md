# 02. Logika Deteksi Risiko & Alur Autentikasi

Dokumen ini menjelaskan bagaimana sistem mengambil keputusan cerdas saat proses autentikasi berlangsung.

## 🛡️ Tahapan Verifikasi Login

Setiap kali pengguna memasukkan kredensial, Laravel akan menjalankan urutan verifikasi berikut:

### 1. Pre-Auth Rate Limiting (Guard 1)
Sistem mengecek berapa kali percobaan dilakukan dari IP dan Email tersebut dalam 15 menit terakhir.
- Jika > 5 kali: Request ditolak sebelum query ke database dilakukan. Ini melindungi MySQL dari serangan brute-force massal.

### 2. Kredensial & Status Akun (Guard 2)
- Verifikasi password (Argon2id).
- Cek status akun (`is_active`). Akun yang dinonaktifkan tidak akan lanjut ke tahap AI.

### 3. Ekstraksi Sinyal (Guard 3)
Laravel mengumpulkan 5 sinyal perilaku utama:
- **IP Address**: Lokasi geografis dan reputasi IP.
- **User Agent**: Browser, OS, dan versi perangkat.
- **Device Fingerprint**: ID unik perangkat yang didasarkan pada karakteristik hardware/browser.
- **Timestamp**: Waktu login (apakah di luar jam kerja normal?).
- **Success Rate**: Perbandingan sukses vs gagal login di masa lalu.

---

## 🤖 Cara Kerja AI Risk Assessment (FastAPI)

Data di atas dikirim ke FastAPI dalam format JSON terenkripsi.

### Bagaimana Algoritma Menilai?
FastAPI menggunakan **Hybrid Assessment Logic**:

#### A. Rule-Based Scores (Statis)
Beberapa kondisi langsung menambah poin risiko:
- **Perangkat Baru (Belum pernah digunakan)**: +25 poin.
- **Lokasi Geografis Berubah Drastis (Misal: Jakarta -> Rusia)**: +60 poin.
- **Login di Jam Ganjil (01:00 - 05:00 Pagi)**: +15 poin.

#### B. Machine Learning Inference (Dinamis)
Algoritma **Isolation Forest** menghitung skor anomali. Ia tidak mencari pola "jahat", melainkan mencari seberapa jauh perilaku saat ini melenceng dari "Normal Baseline" user tersebut.
- Jika anomali terdeteksi, ML akan memberikan kontribusi skor hingga +100 poin.

---

## ⚖️ Penentuan Keputusan Final (The Thresholds)

Sistem menggunakan sistem ambang batas (Threshold) yang bisa dikonfigurasi di `.env`:

| Skor Risiko | Keputusan (Decision) | UX Impact | Alasan Keamanan |
| :--- | :--- | :--- | :--- |
| **0 - 30** | **ALLOW** | Mulus | Perilaku sangat mirip dengan kebiasaan normal. |
| **31 - 69** | **OTP** | Interupsi (Enter Code) | Ada ketidakcocokan data, butuh bukti kepemilikan email. |
| **70 - 100** | **BLOCK** | Terblokir | Sangat anomali. Terdeteksi sebagai serangan bot atau kredensial curian. |

### Contoh Kasus:
- **User A** login dari laptop yang sama setiap hari jam 8 pagi -> **Skor 5** -> **Masuk Langsung**.
- **User A** login dari HP baru di hari libur -> **Skor 45** -> **Minta OTP**.
- **User A** login dari IP tak dikenal di jam 3 pagi dengan 3x percobaan gagal -> **Skor 85** -> **Blokir Akun Sementara**.
