---
layout: doc
---

<script setup>
import { ref } from 'vue'

const activeTab = ref('diagram')
const nodeInfo = ref(null)

const nodeData = {
  client:  { label: 'Client / web browser',        desc: 'Titik awal setiap permintaan. Browser atau klien mengirim request HTTP ke port 8080 yang dijaga Nginx.' },
  nginx:   { label: 'Nginx — reverse proxy :8080',  desc: 'Pintu gerbang tunggal. Menangani TLS termination, menyisipkan header keamanan (HSTS, CSP), dan meneruskan request ke Laravel via FastCGI.' },
  laravel: { label: 'Laravel App — PHP-FPM :9000',  desc: 'Otak sistem. Memproses autentikasi, RBAC, mendelegasikan kalkulasi risiko ke FastAPI, dan menjadwalkan pekerjaan asinkron via Redis queue.' },
  mysql:   { label: 'MySQL :3306',                  desc: 'Penyimpanan persisten utama — data pengguna, log autentikasi, konfigurasi RBAC — menggunakan engine InnoDB dengan user per-service.' },
  redis:   { label: 'Redis :6379',                  desc: 'In-memory store untuk sesi aktif, token OTP berbatas waktu, antrian background, dan token bucket rate-limiter.' },
  fastapi: { label: 'FastAPI — AI Edge :8000',      desc: 'Layanan inferensi ML. Menerima konteks permintaan dan mengembalikan skor risiko (0–100): low · medium · high · critical.' },
}

function showNode(key) { nodeInfo.value = nodeData[key] }
</script>

# Pengenalan Sistem Keamanan AI

**AI Auth System** adalah mekanisme autentikasi layar ganda kelas enterprise — perpaduan antara framework PHP Laravel 11 dengan pustaka Machine Learning (FastAPI) yang mendikte kontrol akses dan mitigasi akses mencurigakan secara real-time.

---

## Kapabilitas Sistem

<div class="cap-grid">
  <div class="cap-card">
    <div class="cap-icon cap-blue">🔐</div>
    <h4>Autentikasi kontekstual</h4>
    <p>OTP acak berbatas waktu, JWT lifecycle di Redis, verifikasi email asinkron.</p>
  </div>
  <div class="cap-card">
    <div class="cap-icon cap-amber">🤖</div>
    <h4>Inteligensi risiko AI</h4>
    <p>Skor ancaman <code>0–100</code> dengan level <code>low</code> · <code>medium</code> · <code>high</code> · <code>critical</code>.</p>
  </div>
  <div class="cap-card">
    <div class="cap-icon cap-green">🛡️</div>
    <h4>Rate limiting adaptif</h4>
    <p>Regulasi berbasis IP, sidik jari sesi, dan tipe endpoint API secara bersamaan.</p>
  </div>
  <div class="cap-card">
    <div class="cap-icon cap-gray">🐳</div>
    <h4>Isolasi infrastruktur</h4>
    <p>Enam layanan Docker terisolasi di jaringan internal tertutup.</p>
  </div>
</div>

---

## Lapisan Arsitektur

### Diagram interaksi komponen

<div class="arch-diagram">
  <div class="arch-line">
    <div class="arch-node-sm" @click="showNode('client')">Client / web browser</div>
    <div class="arch-arrow"></div>
    <div class="arch-node-sm" @click="showNode('nginx')">Nginx :8080</div>
  </div>
  <div class="arch-line">
    <div class="arch-node-sm" @click="showNode('nginx')">Nginx :8080</div>
    <div class="arch-arrow"></div>
    <div class="arch-node-sm" @click="showNode('laravel')">Laravel :9000</div>
  </div>
  <div class="arch-line">
    <div class="arch-node-sm" @click="showNode('laravel')">Laravel :9000</div>
    <div class="arch-arrow"></div>
    <div class="arch-node-sm" @click="showNode('mysql')">MySQL :3306</div>
  </div>
  <div class="arch-line">
    <div class="arch-node-sm" @click="showNode('laravel')">Laravel :9000</div>
    <div class="arch-arrow"></div>
    <div class="arch-node-sm" @click="showNode('redis')">Redis :6379</div>
  </div>
  <div class="arch-line">
    <div class="arch-node-sm" @click="showNode('laravel')">Laravel :9000</div>
    <div class="arch-arrow"></div>
    <div class="arch-node-sm" @click="showNode('fastapi')">FastAPI :8000</div>
  </div>
</div>

<div v-if="nodeInfo" class="node-info-card">
  <h4>{{ nodeInfo.label }}</h4>
  <p>{{ nodeInfo.desc }}</p>
</div>

<style>
.arch-diagram {
  margin: 2rem 0;
  padding: 1.5rem;
  background: var(--vp-c-bg-soft);
  border-radius: 12px;
  border: 1px solid var(--vp-c-divider);
}

.arch-line {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 1rem 0;
}

.arch-node-sm {
  padding: 0.6rem 1rem;
  border-radius: 8px;
  background: var(--vp-c-bg);
  border: 1px solid var(--vp-c-divider);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 160px;
  text-align: center;
}

.arch-node-sm:hover {
  border-color: var(--vp-c-brand-1);
  background: var(--vp-c-brand-soft);
}

.arch-arrow {
  width: 40px;
  height: 2px;
  background: var(--vp-c-divider);
  margin: 0 0.5rem;
  position: relative;
}

.arch-arrow::after {
  content: '';
  position: absolute;
  right: -6px;
  top: -3px;
  border-top: 6px solid transparent;
  border-bottom: 6px solid transparent;
  border-left: 6px solid var(--vp-c-divider);
}

.node-info-card {
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  background: var(--vp-c-bg-soft);
  border-left: 4px solid var(--vp-c-brand-1);
  border-radius: 6px;
}

.node-info-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 14px;
  color: var(--vp-c-brand-1);
}

.node-info-card p {
  margin: 0;
  font-size: 13px;
  color: var(--vp-c-text-2);
  line-height: 1.6;
}
</style>

### Detail kapabilitas per lapisan

::: details Autentikasi kontekstual berwujud
- Pendaftaran dan login dengan verifikasi asinkron berbasis email.
- Kode OTP acak berumur pendek — meminimalkan serangan iteratif (brute force).
- Manajemen siklus JWT di Redis dengan pola Auto-Invalidation dan Force-Logout.
:::

::: details Inteligensi cerdas analisis ancaman (AI Edge)
- Skor kuantitatif `0–100` untuk setiap permintaan autentikasi secara reaktif.
- Deteksi IP anonim, serangan repetisi masif, dan anomali geolokasi jarak jauh.
- Level respons adaptif: `low` · `medium` · `high` · `critical`.
:::

::: details Perlindungan penolakan lintas servis (Rate Limiting)
- Regulasi kecepatan berdasarkan IP origin, sidik jari sesi, dan tipe endpoint API.
- Basis data relasional terlindungi dari tumpukan log via mekanisme antrian Redis.
:::

---

## Peta Dokumen Sistem

| Dokumen | Isi |
|---|---|
| [Modul Arsitektur Internal](/architecture/modules) | Struktur backend Laravel di `app/Modules` |
| [Mesin Analisis Risiko AI](/architecture/ai-engine) | Parameter kalkulasi model FastAPI |
| [Manajemen Docker](/guide/docker) | Isolasi layanan via Docker Network internal |
| [Konfigurasi Environment](/guide/environment) | Seluruh opsi file `.env` |
| [Referensi API Autentikasi](/api/auth) | Payload dan respons endpoint klien |

<style>
.cap-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin: 1.5rem 0;
}
.cap-card {
  border: 1px solid var(--vp-c-divider);
  border-radius: 12px;
  padding: 1rem 1.1rem;
  background: var(--vp-c-bg);
  transition: border-color 0.2s;
}
.cap-card:hover { border-color: var(--vp-c-brand-1); }
.cap-icon {
  width: 32px; height: 32px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; margin-bottom: 10px;
}
.cap-blue  { background: #E6F1FB; }
.cap-amber { background: #FAEEDA; }
.cap-green { background: #EAF3DE; }
.cap-gray  { background: var(--vp-c-bg-soft); }
.cap-card h4 { font-size: 13px; font-weight: 600; margin-bottom: 5px; }
.cap-card p  { font-size: 13px; color: var(--vp-c-text-2); line-height: 1.55; margin: 0; }
</style>