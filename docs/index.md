---
layout: home

hero:
  name: "AI Auth System"
  text: "Dokumentasi Arsitektur"
  tagline: Sistem autentikasi komprehensif skala enterprise dengan perlindungan berbasis Machine Learning, Rate Limiting adaptif, dan infrastruktur Docker yang terorkestrasikan secara ketat.
  actions:
    - theme: brand
      text: Pelajari Arsitektur →
      link: /architecture/modules
    - theme: alt
      text: Panduan Instalasi
      link: /guide/installation
    - theme: alt
      text: Referensi API
      link: /api/

features:
  - icon: 🔒
    title: Autentikasi Berlapis (Multi-Layer)
    details: Implementasi RBAC dengan proteksi sesi token dan Device Fingerprinting yang sangat ketat untuk kontrol akses granular.
  - icon: 🤖
    title: Intelegensi Risiko AI (FastAPI)
    details: Analisis heuristik dan machine-learning untuk memetakan model serangan komando, anomali geolokasi, dan menghasilkan skor risiko komprehensif.
  - icon: 🛡️
    title: Pertahanan Adaptif Lintas Entitas
    details: Pemblokiran entitas berbahaya dengan rate limiting otomatis sesuai skor ancaman AI terhadap IP dinamis dan percobaan anomali.
  - icon: 🐳
    title: Infrastruktur Isolasi Terpusat
    details: Orkestrasi Docker Compose — Nginx, Redis, MySQL, AI Edge, dan Laravel API semuanya terisolasi di jaringan internal.
---

<div class="home-content">

## Arsitektur Layanan

<div class="arch-grid">
  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">🔐</span>
      <div>
        <div class="node-name">Laravel API</div>
        <code class="node-port">:9000</code>
      </div>
    </div>
    <p class="node-detail">Backend utama pemroses autentikasi, RBAC, dan orkestrasi aliran bisnis via FastCGI.</p>
  </div>

  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">🤖</span>
      <div>
        <div class="node-name">FastAPI AI</div>
        <code class="node-port">:8000</code>
      </div>
    </div>
    <p class="node-detail">Layanan inferensi ML — menghitung risk score dari pola login, geolokasi, dan device fingerprint.</p>
  </div>

  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">🛡️</span>
      <div>
        <div class="node-name">Nginx Proxy</div>
        <code class="node-port">:8080</code>
      </div>
    </div>
    <p class="node-detail">Titik masuk tunggal — routing, TLS termination, dan header keamanan (HSTS, CSP).</p>
  </div>

  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">🗄️</span>
      <div>
        <div class="node-name">MySQL DB</div>
        <code class="node-port">:3306</code>
      </div>
    </div>
    <p class="node-detail">Penyimpanan persisten utama — InnoDB engine, user per-service, replikasi opsional.</p>
  </div>

  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">⚡</span>
      <div>
        <div class="node-name">Redis Cache</div>
        <code class="node-port">:6379</code>
      </div>
    </div>
    <p class="node-detail">In-memory store untuk sesi, OTP, rate-limiter token bucket, dan antrian background.</p>
  </div>

  <div class="arch-node">
    <div class="node-header">
      <span class="node-icon">📄</span>
      <div>
        <div class="node-name">VitePress</div>
        <code class="node-port">:8090</code>
      </div>
    </div>
    <p class="node-detail">Dokumentasi SSG — di-build otomatis dan di-deploy via pipeline CI/CD setiap push ke main.</p>
  </div>
</div>

## Tech Stack

| Lingkup | Teknologi | Port | Peran Utama |
|---|---|---|---|
| Backend Utama | Laravel 11 (PHP 8.2) | `9000` | Kontrol bisnis, autentikasi, orchestrator logika |
| AI Edge Service | FastAPI (Python 3.11) | `8000` | Inferensi ML untuk penilaian ancaman autentikasi |
| Database | MySQL 8.0 | `3306` | Persistensi data — InnoDB, proteksi per-service |
| Cache & Queue | Redis 7 Alpine | `6379` | Sessions, rate-limiter, OTP, message queue |
| Reverse Proxy | Nginx Alpine | `8080` | Routing, TLS termination, header keamanan |
| Dokumentasi | VitePress (SSG) | `8090` | Panduan sistem — regenerasi otomatis via CI/CD |

## Mulai Cepat

::: tip Prasyarat
Pastikan Docker dan Docker Compose sudah terinstal di sistem Anda sebelum melanjutkan.
:::

**Langkah 1** — Clone repositori

```bash
git clone <url_repositori>/ai-auth-system.git
cd ai-auth-system
```

**Langkah 2** — Jalankan inisiator infrastruktur

```bash
chmod +x setup.sh
./setup.sh
```

Script ini secara otomatis menangani: build image, pembuatan SSL sertifikat, validasi hak akses database, dan pendaftaran kredensial.

**Langkah 3** — Akses kontrol utama di **[http://localhost:8080](http://localhost:8080)**

</div>

<style>
.home-content {
  max-width: 960px;
  margin: 0 auto;
  padding: 2rem 1.5rem 4rem;
}

.arch-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 12px;
  margin: 1.5rem 0 2.5rem;
}

.arch-node {
  border: 1px solid var(--vp-c-divider);
  border-radius: 12px;
  padding: 1.1rem 1.25rem;
  background: var(--vp-c-bg);
  transition: border-color 0.2s, background 0.2s;
  cursor: default;
}

.arch-node:hover {
  border-color: var(--vp-c-brand-1);
  background: var(--vp-c-bg-soft);
}

.node-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.node-icon {
  font-size: 20px;
  line-height: 1;
}

.node-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--vp-c-text-1);
}

.node-port {
  font-size: 11px;
  color: var(--vp-c-brand-1);
  background: var(--vp-c-brand-soft);
  padding: 1px 6px;
  border-radius: 4px;
  font-family: var(--vp-font-family-mono);
}

.node-detail {
  font-size: 13px;
  color: var(--vp-c-text-2);
  line-height: 1.6;
  margin: 0;
}
</style>