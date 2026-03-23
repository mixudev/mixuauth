# 03. Panduan Mendalam Training & Optimasi AI

Dokumen ini menjelaskan cara membuat sistem Anda menjadi lebih cerdas melalui pengumpulan data dan pelatihan ulang model.

## 📊 1. Kenapa Harus Retrain?
Saat pertama kali diinstal, model ML menggunakan parameter baseline umum. Agar akurasinya mencapai >95%, model harus mengenal pola login yang spesifik pada platform Anda. Retrain (pelatihan ulang) sangat disarankan setiap Anda mendapatkan data login dalam jumlah besar.

## 📥 2. Siklus Pengumpulan Data (The Data Loop)

### Langkah A: Export dengan `ai:export-training`
Laravel mencatat setiap sinyal perilaku ke dalam tabel audit. Gunakan perintah berikut:
```bash
docker compose exec app php artisan ai:export-training
```
Perintah ini akan menyaring data audit dan memformatnya menjadi dataset yang siap dibaca oleh Python (Scikit-Learn). 
- **Output**: `laravel-auth-ai/storage/app/ai/training_data.csv`
- **Data yang diekspor**: `user_id`, `risk_score`, `is_trusted_device`, `is_known_ip`, `hour_of_day`, `day_of_week`.

### Langkah B: Melatih Model di FastAPI
Pindahkan CSV tersebut ke folder `ai-security/data/`. Masuk ke container FastAPI:
```bash
docker compose exec fastapi-risk python training/train_model.py
```

## 🔍 3. Memahami Output Training
Setelah proses selesai, akan muncul ringkasan seperti ini:
- **Mean Anomaly Score**: Menunjukkan rata-rata seberapa "aneh" data Anda.
- **Top 5 Critical Points**: Data yang dianggap paling anomali.
- **Model Size**: Ukuran file `.pkl` (biasanya sekitar 1MB - 5MB).

## 🎛️ 4. Optimasi Parameter (Fine-Tuning)
Anda bisa menyesuaikan sensitivitas AI melalui file `ai-security/.env`:

```env
# Semakin tinggi AI_RISK_WEIGHT, semakin besar pengaruh AI dibanding aturan statis
AI_RISK_WEIGHT=0.8
RULE_RISK_WEIGHT=0.2

# Ambang batas deteksi anomali pada algoritma Isolation Forest
# Default: 0.1 (Menyaring 10% data paling anomali)
MODEL_CONTAMINATION=0.05
```

## 🚨 5. Strategi "Cold Start"
Saat sistem baru berjalan dan belum ada data untuk training:
1. Pastikan `APP_ENV=development` di FastAPI.
2. Sistem akan menyandarkan keputusan lebih besar pada **Rule-based** (aturan statis seperti IP & Fingerprint).
3. Setelah 1 bulan berjalan, lakukan Retrain pertama Anda.

> [!TIP]
> **Data yang Berkualitas**: Jika Anda mendeteksi ada serangan hacker yang berhasil diidentifikasi secara manual, pastikan log audit untuk baris tersebut ditandai atau diekspor untuk memperkuat kemampuan deteksi anomali di masa depan.
