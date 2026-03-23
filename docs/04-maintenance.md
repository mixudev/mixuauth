# 04. Manajemen Operasional & Pemeliharaan

Dokumen ini adalah panduan harian untuk IT Administrator yang mengoperasikan sistem ini guna memastikan skalabilitas dan keamanan tetap terjaga.

## ⚙️ 1. Routine Maintenance Checklist

### Harian (Daily)
- [ ] Cek status container: `docker compose ps`
- [ ] Pantau log keamanan untuk anomali masal: `docker compose logs --tail=100 app`
- [ ] Pastikan queue worker tidak macet: `docker compose exec app php artisan queue:monitor`

### Mingguan (Weekly)
- [ ] Bersihkan file cache Laravel: `docker compose exec app php artisan cache:clear`
- [ ] Periksa disk usage (terutama log yang membengkak): `docker compose exec app du -sh storage/logs`

### Bulanan (Monthly)
- [ ] Lakukan training ulang (Retrain) AI jika data login terkumpul cukup banyak.
- [ ] Update Docker Base Image untuk patch keamanan: `docker compose pull && docker compose build --no-cache`

---

## 📦 2. Backup & Disaster Recovery

Keamanan data adalah prioritas. Jangan abaikan backup database di production!

### Backup MySQL
```bash
docker compose exec db mysqldump -u root -p[ROOT_PASSWORD] secure_auth > backup_$(date +%F).sql
```

### Restore MySQL
```bash
docker compose exec -T db mysql -u root -p[ROOT_PASSWORD] secure_auth < backup_file.sql
```

---

## 🔑 3. Pengelolaan API Key & SSL Certificate

### Keamanan SSL/TLS
Jika menggunakan VPS, gunakan **Certbot** untuk memperbarui sertifikat secara otomatis. Tambahkan cron job ini di OS host:
```bash
0 0 1 * * certbot renew --quiet && docker compose restart nginx
```

### Rotasi AI-Key Otomatis
Jika Anda ingin mengganti kunci komunikasi setiap bulan secara otomatis:
1. Buat cron task di server host.
2. Jalankan perintah: `docker compose exec app php artisan ai:generate-key`.

---

## 📈 4. Scaling (Penskalaan)
Jika trafik mulai tinggi (>10.000 user aktif):
- **Tambah Worker**: Ubah `docker-compose.yml` untuk menambah jumlah container worker:
  ```yaml
  deploy:
    replicas: 3
  ```
- **External Database**: Pindahkan MySQL ke service managed (seperti AWS RDS atau Google Cloud SQL) dan arahkan `.env` ke host database tersebut.
- **Dedicater Redis Cluster**: Untuk menangani jutaan session sekaligus.
