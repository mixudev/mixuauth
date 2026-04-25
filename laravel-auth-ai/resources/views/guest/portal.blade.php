<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal — Akses Terbatas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0d0f14;
            --ink-mid: #1c2030;
            --ink-light: #2e3450;
            --surface: #f4f3ef;
            --surface-warm: #eceae3;
            --accent: #e8562a;
            --accent-glow: rgba(232, 86, 42, 0.15);
            --gold: #c9a84c;
            --gold-light: rgba(201, 168, 76, 0.12);
            --text-primary: #0d0f14;
            --text-secondary: #5a607a;
            --text-muted: #9298b0;
            --border: rgba(13,15,20,0.08);
            --border-strong: rgba(13,15,20,0.14);
            --card-bg: #ffffff;
            --radius: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background texture ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(ellipse 80% 50% at 70% -10%, rgba(232,86,42,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at -10% 80%, rgba(201,168,76,0.06) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='0.8' fill='%230d0f14' fill-opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* ── Layout ── */
        .shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        /* ── Header ── */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 40px;
            border-bottom: 1px solid var(--border);
            background: rgba(244,243,239,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-mark {
            width: 38px;
            height: 38px;
            background: var(--ink);
            border-radius: 10px;
            display: grid;
            place-items: center;
        }

        .logo-mark svg { width: 20px; height: 20px; }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.5px;
            color: var(--ink);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .badge-env {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--gold-light);
            color: var(--gold);
            border: 1px solid rgba(201,168,76,0.25);
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            background: transparent;
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            padding: 7px 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: var(--ink);
            color: #fff;
            border-color: var(--ink);
        }

        /* ── Main ── */
        main {
            padding: 48px 40px;
            max-width: 1080px;
            margin: 0 auto;
            width: 100%;
        }

        /* ── Hero banner ── */
        .hero {
            background: var(--ink);
            border-radius: 24px;
            padding: 48px 52px;
            margin-bottom: 36px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232,86,42,0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -80px; left: 30%;
            width: 240px; height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-avatar {
            flex-shrink: 0;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, #c43d18 100%);
            display: grid;
            place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            position: relative;
            z-index: 1;
            border: 3px solid rgba(255,255,255,0.12);
            box-shadow: 0 0 0 6px rgba(232,86,42,0.2);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(22px, 3vw, 30px);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 10px;
        }

        .hero-sub {
            font-size: 15px;
            color: rgba(255,255,255,0.55);
            line-height: 1.6;
            max-width: 420px;
        }

        /* ── Alert card ── */
        .alert-card {
            background: rgba(232,86,42,0.08);
            border: 1px solid rgba(232,86,42,0.22);
            border-radius: var(--radius);
            padding: 20px 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 36px;
            animation: slideIn 0.5s ease both;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 40px; height: 40px;
            border-radius: 10px;
            background: rgba(232,86,42,0.15);
            display: grid;
            place-items: center;
        }

        .alert-icon svg { color: var(--accent); }

        .alert-body h3 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 15px;
            color: var(--accent);
            margin-bottom: 4px;
        }

        .alert-body p {
            font-size: 13.5px;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        /* ── Grid ── */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        /* ── Card ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            animation: fadeUp 0.5s ease both;
        }

        .card:hover {
            box-shadow: 0 8px 32px rgba(13,15,20,0.08);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .card-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .card-icon.accent  { background: var(--accent-glow); color: var(--accent); }
        .card-icon.gold    { background: var(--gold-light); color: var(--gold); }
        .card-icon.ink     { background: rgba(13,15,20,0.06); color: var(--ink); }

        /* ── Stat ── */
        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 36px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* ── User info ── */
        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .info-row:last-child { border-bottom: none; }

        .info-key {
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-val {
            font-size: 13.5px;
            color: var(--text-primary);
            font-weight: 500;
            text-align: right;
        }

        /* ── Roles ── */
        .roles-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 500;
            background: rgba(13,15,20,0.05);
            border: 1px solid var(--border);
            color: var(--text-primary);
        }

        .role-pill .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
        }

        .empty-roles {
            font-size: 13px;
            color: var(--text-muted);
            font-style: italic;
        }

        /* ── Login logs ── */
        .log-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .log-item:last-child { border-bottom: none; }

        .log-dot {
            flex-shrink: 0;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
        }

        .log-dot.old { background: var(--text-muted); }

        .log-details { flex: 1; min-width: 0; }

        .log-ip {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-agent {
            font-size: 11.5px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .log-time {
            font-size: 11.5px;
            color: var(--text-muted);
            flex-shrink: 0;
            text-align: right;
        }

        /* ── Permissions ── */
        .perm-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .perm-chip {
            font-size: 11.5px;
            padding: 4px 10px;
            border-radius: 6px;
            background: var(--gold-light);
            color: #7a5c10;
            border: 1px solid rgba(201,168,76,0.2);
            font-weight: 500;
        }

        /* ── CTA ── */
        .cta-card {
            background: linear-gradient(135deg, var(--ink) 0%, #1c2030 100%);
            border-radius: var(--radius);
            padding: 32px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            animation: fadeUp 0.5s 0.2s ease both;
        }

        .cta-text h3 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            margin-bottom: 6px;
        }

        .cta-text p {
            font-size: 13.5px;
            color: rgba(255,255,255,0.5);
            line-height: 1.5;
        }

        .btn-cta {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-cta:hover {
            background: #c43d18;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232,86,42,0.4);
        }

        /* ── Footer ── */
        footer {
            padding: 20px 40px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-12px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .card:nth-child(1) { animation-delay: 0.05s; }
        .card:nth-child(2) { animation-delay: 0.10s; }
        .card:nth-child(3) { animation-delay: 0.15s; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .grid-3 { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            header { padding: 16px 20px; }
            .badge-env { display: none; }
            main { padding: 28px 20px; }
            .hero { flex-direction: column; padding: 32px 28px; gap: 20px; }
            .hero-avatar { width: 68px; height: 68px; font-size: 24px; }
            .grid-3 { grid-template-columns: 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
            .cta-card { flex-direction: column; align-items: flex-start; padding: 24px; }
            footer { flex-direction: column; gap: 6px; text-align: center; }
        }
    </style>
</head>
<body>
<div class="shell">

    {{-- ── Header ── --}}
    <header>
        <div class="logo">
            <div class="logo-mark">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 10 L10 3 L17 10 L10 17 Z" fill="white" opacity="0.9"/>
                    <circle cx="10" cy="10" r="3" fill="#e8562a"/>
                </svg>
            </div>
            <span class="logo-text">{{ config('app.name', 'Sistem') }}</span>
        </div>
        <div class="header-right">
            <span class="badge-env">Guest Portal</span>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="btn-logout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Keluar
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </header>

    {{-- ── Main ── --}}
    <main>

        {{-- Hero --}}
        <div class="hero">
            <div class="hero-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="hero-content">
                <div class="hero-eyebrow">Selamat datang kembali</div>
                <h1 class="hero-title">Halo, {{ $user->name }}!</h1>
                <p class="hero-sub">
                    Akun Anda aktif namun belum mendapatkan akses ke dasbor.
                    Hubungi administrator untuk mendapatkan hak akses yang sesuai.
                </p>
            </div>
        </div>

        {{-- Alert --}}
        <div class="alert-card">
            <div class="alert-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="alert-body">
                <h3>Akses Terbatas</h3>
                <p>Anda berhasil masuk ke sistem, namun peran Anda saat ini belum memiliki izin untuk mengakses dasbor utama. Silakan hubungi administrator sistem Anda.</p>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="grid-3">
            <div class="card">
                <div class="card-header">
                    <span class="card-label">Total Peran</span>
                    <div class="card-icon accent">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ $roles->count() }}</div>
                <div class="stat-desc">Peran ditetapkan</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-label">Izin Akses</span>
                    <div class="card-icon gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ $permissions->count() }}</div>
                <div class="stat-desc">Hak akses tersedia</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-label">Riwayat Login</span>
                    <div class="card-icon ink">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ $recentLogs->count() }}</div>
                <div class="stat-desc">Login terakhir</div>
            </div>
        </div>

        {{-- Info + Roles --}}
        <div class="grid-2">
            {{-- User Info --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-label">Informasi Akun</span>
                    <div class="card-icon ink">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-key">Nama Lengkap</span>
                    <span class="info-val">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Email</span>
                    <span class="info-val" style="font-size:12.5px;">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Status Akun</span>
                    <span class="info-val">
                        @if($user->email_verified_at)
                            <span style="color:#16a34a; font-size:12px;">✔ Terverifikasi</span>
                        @else
                            <span style="color:var(--accent); font-size:12px;">✖ Belum Diverifikasi</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-key">Bergabung</span>
                    <span class="info-val">{{ $user->created_at->format('d M Y') }}</span>
                </div>
            </div>

            {{-- Roles --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-label">Peran Aktif</span>
                    <div class="card-icon accent">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/>
                        </svg>
                    </div>
                </div>
                @if($roles->isNotEmpty())
                    <div class="roles-list">
                        @foreach($roles as $role)
                            <span class="role-pill">
                                <span class="dot"></span>
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="empty-roles">Tidak ada peran yang ditetapkan.</p>
                @endif

                @if($permissions->isNotEmpty())
                    <div style="margin-top:20px;">
                        <div class="card-label" style="margin-bottom:10px;">Izin Tersedia</div>
                        <div class="perm-grid">
                            @foreach($permissions->take(12) as $perm)
                                <span class="perm-chip">{{ $perm->name }}</span>
                            @endforeach
                            @if($permissions->count() > 12)
                                <span class="perm-chip" style="background:var(--border);color:var(--text-muted);border-color:transparent;">
                                    +{{ $permissions->count() - 12 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Login Logs --}}
        @if($recentLogs->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <span class="card-label">Aktivitas Login Terakhir</span>
                <div class="card-icon ink">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            @foreach($recentLogs as $idx => $log)
                <div class="log-item">
                    <div class="log-dot {{ $idx > 0 ? 'old' : '' }}"></div>
                    <div class="log-details">
                        <div class="log-ip">{{ $log->ip_address ?? '—' }}</div>
                        <div class="log-agent">{{ Str::limit($log->user_agent ?? 'Unknown Agent', 55) }}</div>
                    </div>
                    <div class="log-time">{{ $log->occurred_at->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- CTA --}}
        <div class="cta-card">
            <div class="cta-text">
                <h3>Butuh Akses Lebih?</h3>
                <p>Hubungi administrator sistem untuk mendapatkan peran dan izin yang diperlukan.</p>
            </div>
            <a href="mailto:admin@{{ request()->getHost() }}" class="btn-cta">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
                Hubungi Admin
            </a>
        </div>

    </main>

    {{-- ── Footer ── --}}
    <footer>
        <span>© {{ date('Y') }} {{ config('app.name', 'Sistem') }}. Semua hak dilindungi.</span>
        <span>Laravel {{ app()->version() }} &nbsp;·&nbsp; PHP {{ PHP_VERSION }}</span>
    </footer>

</div>
</body>
</html>