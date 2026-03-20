<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecureAuth') — AI Auth System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #080c10;
            --bg2:       #0d1117;
            --bg3:       #161b22;
            --border:    #21262d;
            --border2:   #30363d;
            --text:      #e6edf3;
            --text2:     #8b949e;
            --text3:     #484f58;
            --accent:    #00d4aa;
            --accent2:   #00a882;
            --danger:    #f85149;
            --warn:      #e3b341;
            --info:      #388bfd;
            --mono:      'Space Mono', monospace;
            --sans:      'DM Sans', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.6;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { color: var(--accent2); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg2); }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

        /* Layout */
        .app-shell { display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        .sidebar {
            width: 240px;
            flex-shrink: 0;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        .sidebar-logo {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-logo .logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-family: var(--mono);
            font-size: 14px;
            font-weight: 700;
            color: var(--bg);
        }
        .sidebar-logo .logo-text {
            font-family: var(--mono);
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: 0.05em;
        }
        .sidebar-logo .logo-sub {
            font-size: 10px;
            color: var(--text3);
            font-family: var(--mono);
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-label {
            font-size: 10px;
            font-family: var(--mono);
            color: var(--text3);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0 12px;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        .nav-label:first-child { margin-top: 0; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            color: var(--text2);
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg3); color: var(--text); }
        .nav-item.active { background: rgba(0,212,170,0.1); color: var(--accent); }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            background: var(--bg3);
        }
        .user-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--info));
            display: flex; align-items: center; justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--bg);
            font-family: var(--mono);
            flex-shrink: 0;
        }
        .user-name { font-size: 12px; font-weight: 600; color: var(--text); }
        .user-email { font-size: 11px; color: var(--text3); }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            margin-top: 8px;
            padding: 7px 12px;
            border-radius: 6px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text2);
            font-size: 12px;
            font-family: var(--sans);
            cursor: pointer;
            transition: all 0.15s;
        }
        .logout-btn:hover { border-color: var(--danger); color: var(--danger); background: rgba(248,81,73,0.05); }
        .logout-btn svg { width: 14px; height: 14px; }

        /* Main content */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        .topbar {
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg2);
            flex-shrink: 0;
        }
        .page-title { font-family: var(--mono); font-size: 14px; font-weight: 700; color: var(--text); }
        .page-sub { font-size: 12px; color: var(--text3); margin-top: 2px; }
        .status-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-family: var(--mono);
            color: var(--accent);
            background: rgba(0,212,170,0.08);
            border: 1px solid rgba(0,212,170,0.2);
            padding: 4px 10px;
            border-radius: 20px;
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .content-area { padding: 32px; flex: 1; }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid;
        }
        .status-badge.offline {
            color: var(--danger);
            background: rgba(248,81,73,0.08);
            border-color: rgba(248,81,73,0.2);
        }
        .status-dot.offline {
            background: var(--danger);
            animation: none;
        }
        .alert-success { background: rgba(0,212,170,0.08); border-color: rgba(0,212,170,0.25); color: var(--accent); }
        .alert-error { background: rgba(248,81,73,0.08); border-color: rgba(248,81,73,0.25); color: var(--danger); }
        .alert-info { background: rgba(56,139,253,0.08); border-color: rgba(56,139,253,0.25); color: var(--info); }
        .alert-warn { background: rgba(227,179,65,0.08); border-color: rgba(227,179,65,0.25); color: var(--warn); }
    </style>
    @stack('styles')
</head>
<body>

@auth
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">SA</div>
            <div>
                <div class="logo-text">SECURE<span style="color:var(--accent)">AUTH</span></div>
                <div class="logo-sub">AI-Powered Security</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Navigasi</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('audit.log') }}" class="nav-item {{ request()->routeIs('audit.log') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Audit Log
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">@yield('page-title', 'Dashboard')</div>
                <div class="page-sub">@yield('page-sub', '')</div>
            </div>
            <div class="status-badge {{ $aiOnline ?? false ? '' : 'offline' }}">
    <div class="status-dot {{ $aiOnline ?? false ? '' : 'offline' }}"></div>
    AI Risk Engine: {{ $aiOnline ?? false ? 'Online' : 'Offline' }}
</div>
        </div>
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">✗ {{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
@else
    @yield('content')
@endauth

@stack('scripts')
</body>
</html>