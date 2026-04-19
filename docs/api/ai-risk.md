# Spesifikasi Deteksi Inferensi API (AI Edge)

Modul Python FastAPI mengkalibrasi ancaman menggunakan protokol jaringan internal berkecepatan tinggi. API ini secara reguler **tidak terekspos keluar dinding firewall** dan secara ketat ditugaskan hanya untuk melayani request internal FastCGI/PHP.

---

## 1. Terminal Skoring Deteksi (Analyze Endpoint)

Fungsi inti matematis sistem untuk melakukan konversi riwayat log klien menjadi skor probabilitas empiris (0.0 sampai 100.0).

**Alamat Internal Tersembunyi:** `POST http://fastapi-risk:8000/analyze`

**Penerapan Pembatasan:**
- Akses mutlak ditolak bila sandi `X-API-Key` diskrepansi dengan variabel lingkungan Docker.

### Atribut Masukan Validasi Pydantic (JSON Payload)

| Kunci | Format Tipe | Penjabaran Operasional |
|-------|-------------|------------|
| `ip_address` | String | Format IPv4/IPv6 tunggal. |
| `user_agent` | String | Deskripsi peramban (Browser Strings) untuk pemetaan proksi ancaman. |
| `failed_attempts` | Bilangan Bulat | Akumulatif perhitungan log kegagalan identitas dalam rentang 30 menit. |
| `is_new_device` | Boolean | True apabila klien belum pernah mengotentikasi dari SID/UUID (Fingerprint) yang serupa. |
| `geo_anomaly` | Float | Koefisien anomali pergerakan lintas negara dalam satuan jarak waktu (1.0 = Anomali Tinggi). |

### Ilustrasi Panggilan dari Aplikasi (Laravel HTTP Client)

```php
$response = Http::withHeaders([
    'X-API-Key' => config('security.ai_api_key'),
    'Accept' => 'application/json',
])->post('http://fastapi-risk:8000/analyze', [
    'ip_address' => '192.168.1.15',
    'user_agent' => 'Mozilla/5.0...',
    'failed_attempts' => 2,
    'is_new_device' => true,
    'geo_anomaly' => 0.0
]);
```

### Balasan Skoring Resolusi (200 OK)

Balasan kalkulasi dari AI yang dikomsumsi ulang oleh kontroler PHP untuk keputusan lanjutan.

```json
{
  "risk_score": 65.4,
  "risk_level": "High",
  "recommended_action": "require_mfa",
  "factors": [
    "Unidentified physical origin (New device)",
    "Repetitive credentials rejection behavior"
  ],
  "timestamp": "2026-04-19T20:15:30Z"
}
```

---

## 2. Pemeriksaan Denyut Kehidupan (Health Check)

Subsistem ini diotorisasi secara persisten oleh orstrator kontainer (`docker-compose healthcheck`) untuk me-restart layanan manakala mesin Python mengunci.

**Alamat Internal Tersembunyi:** `GET http://fastapi-risk:8000/health`

### Respon Sinkron (200 OK)

```json
{
  "status": "online",
  "model_loaded": true,
  "version": "1.0.1",
  "uptime_seconds": 12450
}
```
