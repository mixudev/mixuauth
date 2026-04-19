# Mesin Deteksi Risiko (AI Engine)

Sistem autentikasi dilengkapi dengan penyusup deteksi pola menggunakan layanan FastAPI independen (Python 3.11). Modul ini bekerja di ranah *Edge Inference* dan berdiri sebagai pengkalkulasi probabilitas sebelum *Authentication Module* pada Laravel memproses atau memberikan JWT Sesi.

---

## Alur Orkestrasi Data Inferensi

Ketika sistem utama Laravel menangkap *request* koneksi dari jaringan luar (Internet), alih-alih langsung mengeksekusi pengecekan sandi ke basis data MySQL, ia akan mengirim paket muatan (*payload*) secara asinkronus terhadap API FastAPI.

```text
1. [ Klien Request Login     ] ─────────────────> Nginx Proxy (Pelapis TLS/Edge)
2. [ Validasi Header         ] ──────(IP Extract)──────> Laravel Auth API
3. [ Orkestrasi Pydantic     ] ──(Kirim Metadata)──> FastAPI Risk Service
4. [ AI Model Assessment     ] ──────(Skor 0-100)──────> FastAPI Risk Service
5. [ Keputusan Diaplikasikan ] <─────(Blok/Challenge)─── Laravel Auth API
```

---

## Parameter Input Kalkulasi (Fitur Analisis)

Model memformulasikan matriks probabilitas ancaman berdasarkan struktur data yang diselaraskan melalui *Pydantic Schemas*:

| Dimensi | Parameter | Keterangan Indikator |
|---------|-----------|----------------------|
| **Konteks IP** | `ip_address`, `geo_anomaly` | Menilai reputasi alamat IP (terdapat pola *botnet*, *TOR nodes*, atau negara masuk dalam *blacklist* dinamis). |
| **Konteks Perangkat** | `user_agent`, `new_device` | Menaksir probabilitas peretasan akibat anomali profil perangkat secara komparatif dengan aktivitas *fingerprint* historis milik pengguna. |
| **Konteks Temporal** | `timestamp`, `unusual_time` | Klasifikasi probabilitas akses di luar rutinitas harian entitas klien. |
| **Pola Relasional** | `failed_attempts`, `ip_changed`| Kuantifikasi laju dan repetisi kegagalan yang menyiratkan eksistensi percobaan sandi *Brute-Force Attack*. |

---

## Klasifikasi Skor dan Penindaklanjutan (Thresholds)

Algoritma mengkonversi nilai inferensi probabilitas final menjadi **Risk Score (0–100)**:

### 1. `Low Risk` (Skor: 0–29)
- **Status:** Dipercaya (Trusted).
- **Tindak Lanjut:** Skema OTP reguler atau *direct login* bagi identitas terdaftar berjalan secara efisien. Tidak ada perlambatan laju akses. 

### 2. `Medium Risk` (Skor: 30–59)
- **Status:** Anomali Subtil (Misal: Pergantian jaringan sementara atau geografi regional bertukar jarak normal).
- **Tindak Lanjut:** Login diotorisasi dengan validasi OTP yang ketat, dan platform akan membangkitkan `warning logging` agar notifikasi aktivitas login asing terkirim ke surel identitas pengguna bersangkutan.

### 3. `High Risk` (Skor: 60–84)
- **Status:** Anomali Serius (Misal: Anomali waktu operasi komparatif dengan pergantian `user-agent` mayor, peningkatan kegagalan beruntun 4x berturut-turut).
- **Tindak Lanjut:** Permintaan klien diberikan *tahanan bersyarat* (Challenge); klien diwajibkan melewati validasi *CAPTCHA* tambahan (*bila dikonfigurasi*), plus pembatasan tingkat laju antrean (*Rate-Limiting*) khusus yang lambat.

### 4. `Critical Risk` (Skor: 85–100)
- **Status:** Diidentifikasi sebagai Operasi Peretasan (Serangan *Script*, Reputasi IP Buruk, pola *Credential Stuffing* massal).
- **Tindak Lanjut:** Komunikasi HTTP putus seketika dengan respon 403 atau 429 yang diatur otomatis, entitas IP ditempatkan ke *blacklist table* sentral berbasis persisten ke Redis dan SQL.
