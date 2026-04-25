@extends('layouts.app-dashboard')

@section('title', 'Security Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub', 'Monitoring keamanan & aktivitas login secara realtime')

@section('content')

{{-- ============================================================
     KOMPATIBILITAS DENGAN LAYOUT:
     - Tidak ada Alpine.js (layout tidak load Alpine)
     - Tidak ada duplicate header / notif / dark toggle
     - isDark() → cek classList('dark') pada <html>, sama persis dengan layout
     - rebuildCharts() → di-expose ke window → dipanggil toggleDark() di layout
     - Chart.js TIDAK di-load ulang (sudah ada di <head> layout)
     - Dark mode key: localStorage('theme'), bukan 'darkMode'
     - Period selector: plain JS (window.setPeriod), redirect dengan ?period=
     ============================================================ --}}

{{-- ── TOOLBAR ── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Security Dashboard </h1>
        <p class="text-xs text-slate-400 mt-0.5">Monitoring keamanan &amp; aktivitas login secara realtime</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
            @foreach(['24h' => '24 Jam', '7d' => '7 Hari', '30d' => '30 Hari'] as $val => $label)
            <button
                data-period="{{ $val }}"
                onclick="setPeriod('{{ $val }}')"
                class="period-btn px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 {{ $val === ($currentPeriod ?? '7d') ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            >{{ $label }}</button>
            @endforeach
        </div>
        <button
            onclick="refreshDashboard()"
            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
        >
            <svg id="refreshIcon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    </div>
</div>

{{-- ── ALERT BANNER ── --}}
@if(isset($criticalAlerts) && $criticalAlerts->count() > 0)
<div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-sm p-4 mb-6">
    <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-500/20 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ $criticalAlerts->count() }} Notifikasi Keamanan Aktif</p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5 truncate">{{ $criticalAlerts->first()->message ?? 'Terdapat ancaman yang membutuhkan perhatian segera.' }}</p>
    </div>
    <a href="{{ route('dashboard.notifications.all') }}" class="flex-shrink-0 text-xs font-medium text-red-700 dark:text-red-400 hover:underline">Lihat semua →</a>
</div>
@endif

{{-- ── STAT CARDS ── --}}
@include('admin.dashboard.home.stats_card') 

{{-- ══════════════════════════════════════════════════════════════
     ── ROW BARU: 3 Chart Aktivitas Hari Ini ──
     Data yang dibutuhkan dari controller (array per jam, 0–23):
       $todaySuccessHourly  → login sukses per jam
       $todayOtpHourly      → login OTP per jam
       $todayFailedHourly   → login gagal per jam
       $todayBlockedHourly  → login blocked per jam
     Jika belum ada di controller, cukup kirimkan array kosong [].
══════════════════════════════════════════════════════════════ --}}
@include('admin.dashboard.home.chart_today')

{{-- ── ROW 1: Login Activity + Keputusan AI ── --}}
@include('admin.dashboard.home.row_1')

{{-- ── ROW 2: Risk Score + Top Threat IPs ── --}}
@include('admin.dashboard.home.row_2')

{{-- ── ROW 3: Recent Logs + Mini Cards ── --}}
@include('admin.dashboard.home.row_3')


{{-- ============================================================
     SCRIPT — IIFE agar tidak polusi global scope
     Chart.js sudah ada di layout, tidak di-load ulang.
     window.rebuildCharts → dipanggil toggleDark() di layout.
     window.refreshDashboard → dipanggil tombol Refresh.
     window.setPeriod → dipanggil period pill.
     ============================================================ --}}

@include('admin.dashboard.home.script')

@endsection