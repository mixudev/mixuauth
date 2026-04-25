---
layout: home

hero:
  name: "AI Auth System"
  text: "Dokumentasi Teknis & Operasional"
  tagline: "Panduan enterprise-grade untuk instalasi, arsitektur, API, dan troubleshooting sistem autentikasi berbasis AI risk scoring."
  actions:
    - theme: brand
      text: Mulai Panduan Cepat
      link: /guide/
    - theme: alt
      text: Pelajari Layer Keamanan
      link: /security/login-layers
    - theme: alt
      text: Referensi API
      link: /api/

features:
  - title: Arsitektur Keamanan Berlapis
    details: Setiap layer autentikasi dijelaskan secara mendetail mulai dari pre-auth rate limiting, device fingerprinting, hingga AI risk scoring dan Multi-Factor Authentication.
    icon: 
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>'
  - title: Transparansi Kode (Code Snippets)
    details: Memudahkan analisa dan debugging dengan menampilkan secara langsung potongan kode (code snippets) krusial dari middleware, controller, dan service pada setiap penjelasan alur.
    icon:
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-2"><path d="m18 16 4-4-4-4"/><path d="m6 8-4 4 4 4"/><path d="m14.5 4-5 16"/></svg>'
  - title: Runbook Operasional Siap Pakai
    details: Panduan pemecahan masalah (troubleshooting) komprehensif yang dirancang sebagai runbook insiden untuk tim DevOps dan Security Engineers.
    icon:
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>'
---

## Gambaran Umum Sistem

AI Auth System adalah solusi autentikasi modern yang dirancang untuk mengatasi serangan _credential stuffing_, _brute force_, dan pengambilalihan akun secara _real-time_. Dokumentasi ini dirancang bagi para _Software Engineers_, _DevOps_, dan _Security Analysts_ untuk memahami, mengoperasikan, dan melakukan audit keamanan pada sistem.

### Peta Penjelajahan

Berikut adalah titik masuk utama ke dalam dokumentasi, disusun berdasarkan kebutuhan pembaca:

| Kategori | Deskripsi | Tautan |
|---|---|---|
| **Persiapan & Instalasi** | Panduan setup sistem lokal dan _production_ menggunakan Docker, konfigurasi `.env`, dan panduan inisialisasi awal. | [Mulai Panduan](/guide/) |
| **Arsitektur Sistem** | Desain modular backend Laravel, flow integrasi FastAPI Risk Engine, dan pemetaan komponen sistem. | [Arsitektur Modules](/architecture/modules) |
| **Layer Keamanan** | Kajian mendalam dari setiap langkah dalam _Auth Flow_, analisa _source code_ untuk Device Fingerprinting, MFA, dan AI. | [Autentikasi & Keamanan](/security/login-layers) |
| **Referensi Integrasi** | Kontrak API untuk klien Frontend/Mobile, daftar lengkap error codes (`429`, `403`, `202`, dll). | [Referensi API](/api/) |
| **Operasional** | Diagnosa error umum, penanganan cache, pemeliharaan server, dan panduan _runbook_. | [Troubleshooting](/guide/troubleshooting) |

::: tip Cara Menggunakan Dokumentasi
Gunakan fitur pencarian bawaan (`Ctrl + K` atau `Cmd + K`) untuk dengan cepat melompat ke komponen atau konfigurasi spesifik yang sedang Anda cari. Setiap halaman keamanan juga menyertakan _Code Snippet_ untuk memudahkan Anda melacak fungsi di _repository_ utama.
:::

