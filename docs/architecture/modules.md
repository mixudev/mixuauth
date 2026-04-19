# Arsitektur Modular Backend (Laravel)

Sistem backend utama dibangun menggunakan **Laravel 11**, namun tidak menggunakan pola MVC klasik tempat semua *controller* dan *model* tergabung dalam satu direktori besar. Sebaliknya, aplikasi ini dikembangkan dengan arsitektur **Domain-Driven Design (DDD)** yang diimplementasikan melalui pola Modular (direktori `app/Modules/`).

Pola modular ini membungkus fungsionalitas sedemikian rupa agar skalabel, mudah untuk diperbarui, dan membatasi *coupling* antara logika bisnis yang berbeda.

---

## Ringkasan 8 Modul Inti Backend

Pilar penopang sistem terbagi pada delepan modul yang mempunyai batasan otorisasi independen (Independent Bounded Contexts). 

### 1. Authentication Module
Modul ini bertugas menangani seluruh alur pintu masuk dan keluarnya entitas pengguna. 
- **Layanan:** `WebAuthController` dan `ApiController`.
- **Fitur Utama:** Verifikasi sandi, integrasi OTP Email/TOTP, pelacakan histori *session* login, manajemen *inactivity logout*, serta koordinasi ke *Security Module* (FastAPI).

### 2. Security Module
Modul terpenting yang menjadi penengah komunikasi antara Laravel dengan servis kecerdasan buatan, serta menjaga integritas sesi dari modifikasi tak terautorisasi.
- **Layanan:** `AiRiskClientService`, `DeviceFingerprintService`.
- **Fitur Utama:** Ekstraksi data origin permintaan klien, manajemen rate limiter berjenjang, sinkronisasi *fingerprint* unik perangkat, deteksi pola relai geolokasi, hingga mitigasi pembatasan per alamat IP.

### 3. Identity Module
Modul yang berkaitan erat dengan data profil dan siklus hidup representasi fisik pengguna / entitas di basis data.
- **Layanan:** Profilasi data, Avatar dan Pengaturan *Preferences*.
- **Fitur Utama:** Mutasi *password*, manajemen sesi lintas titik, aktivasi akun, histori verifikasi kredensial.

### 4. Authorization Module
Penanggungjawab akses hirarki perizinan pada masing-masing end-point di keseluruhan modul.
- **Fitur Utama:** *Role Based Access Control* (RBAC), Middleware pembatas otorisasi berdasarkan hak istimewa (misal: *Super Admin*, *Security Manager*), penegakan *policies*.

### 5. Timezone Module
Sistem kontrol penyesuaian perhitungan waktu lokal secara adaptif atau yang disebut sinkronisasi zona jam entitas yang tersebar secara geografis.
- **Fitur Utama:** Konversi stempel waktu (*timestamp*) GMT ke waktu presisi masing-masing admin dashboard secara harmonis (melalui middleware dan *Helper functions* di tingkat View).

### 6. Communication Module
Eksekutor notifikasi pesan di luar batas sistem, terutama diandalkan secara penuh oleh sistem antrean (*queue message*).
- **Fitur Utama:** Manajemen antrean SMTP, pengiriman token OTP melalui notifikasi elektronik adaptif, *alert* email peringatan keamanan bagi klien jika terjadi pembatasan di tingkat `Critical`.

### 7. Dashboard Module
Penyedia analisis visibilitas (UI) kontrol untuk ranah admin.
- **Fitur Utama:** Penyajian analitik real-time skor AI-Risk, grafik persentase tipe peringatan ancaman `(Low, Medium, High)`, daftar antrean *blacklist* IP, hingga *dashboard layouting*.

### 8. Common Module
Modul kolektif yang menghimpun dan mengekspor fitur-fitur independen dasar berskala global.
- **Fitur Utama:** Repositori konfigurasi utilitas dasar, sistem lokalisasi terjemahan respons sistem, pengelolaan standar penanganan *Error Code*, antarmuka pola repositori (*Repository Pattern Interfaces*).

---

## Pola Komunikasi Antar Modul

*Loose Coupling* dipastikan berjalan lancar dengan interaksi antar modul melalui konsep *Service Injection* di *ServiceProvider*. Tidak ada direktif Controller dari _Timezone Module_ yang memanggil Model langsung milik _Identity Module_.

Jika *Identity Module* membutuhkan pembaruan aktivitas yang di-trigger melalui *Authentication*, maka *Authentication* akan menerbitkan perantaraan aksi melalui standar event ke `Message Bus` internal Laravel, atau melalui Service layer tunggal yang ditetapkan pada file `AuthServiceProvider` secara eksplisit.
