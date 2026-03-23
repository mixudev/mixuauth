/**
 * TIMEZONE DETECTOR
 * =================
 * Tambahkan kode ini ke resources/js/app.js (atau file JS utama Anda).
 *
 * Cara kerja:
 * 1. Deteksi timezone browser menggunakan Intl API (native, tanpa library)
 * 2. Kirim ke backend via AJAX agar tersimpan di session/DB
 * 3. Semua request berikutnya otomatis memakai timezone yang sudah tersimpan
 *
 * Tidak ada library tambahan yang dibutuhkan.
 */

(function () {
    'use strict';

    /**
     * Ambil CSRF token dari meta tag.
     * Pastikan layout Blade Anda punya: <meta name="csrf-token" content="{{ csrf_token() }}">
     */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Kirim timezone ke backend.
     * Hanya kirim sekali per session — cek localStorage sebagai flag.
     */
    function sendTimezone() {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (! timezone) return;

        // Cek apakah timezone yang sama sudah pernah dikirim di session ini
        // Ini mengurangi request tidak perlu ke server
        const sentKey = 'tz_sent_' + timezone;
        if (sessionStorage.getItem(sentKey)) return;

        fetch('/timezone/set', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':  getCsrfToken(),
                'Accept':        'application/json',
            },
            body: JSON.stringify({ timezone }),
        })
        .then(function (response) {
            if (response.ok) {
                // Tandai sudah dikirim agar tidak kirim ulang di tab yang sama
                sessionStorage.setItem(sentKey, '1');
            }
        })
        .catch(function () {
            // Gagal kirim timezone — tidak perlu crash, tampilan fallback ke UTC
        });
    }

    // Kirim setelah DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sendTimezone);
    } else {
        sendTimezone();
    }
})();


// ============================================================
// ALTERNATIF: Kirim via header setiap request (Axios — jika pakai Axios)
// ============================================================
//
// Jika project Anda menggunakan Axios (misalnya di Vue/React/Livewire),
// tambahkan kode ini untuk menyertakan timezone di SETIAP request AJAX:
//
// import axios from 'axios';
//
// const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
// axios.defaults.headers.common['X-Timezone'] = userTimezone;
//
// Dengan ini, TimezoneMiddleware akan otomatis menangkap timezone
// dari header X-Timezone di setiap request.
// ============================================================
