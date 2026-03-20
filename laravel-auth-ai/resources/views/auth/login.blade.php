@extends('layouts.app')

@section('title', 'Smart Auth With AI')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: #FAFAFA;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ── LAYOUT ── */
    .auth-shell {
        min-height: 100vh;
        display: flex;
    }

    /* ── SIDEBAR ── */
    .auth-sidebar {
        display: none;
        position: relative;
        flex-direction: column;
        justify-content: space-between;
        padding: 32px;
        width: 42%;
        overflow: hidden;
        flex-shrink: 0;
    }
    @media (min-width: 1024px) {
        .auth-sidebar { display: flex; }
    }

    .sidebar-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sidebar-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.2) 60%, rgba(245,166,35,0.2) 100%);
    }

    /* Sidebar logo */
    .sidebar-logo {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sidebar-logo-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sidebar-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
    .sidebar-logo-text {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        letter-spacing: 0.12em;
        color: #fff;
        font-size: 15px;
    }

    /* Sidebar tagline */
    .sidebar-tagline {
        position: relative;
        z-index: 10;
        margin-top: auto;
        margin-bottom: 40px;
    }
    .sidebar-accent-bar {
        width: 36px;
        height: 4px;
        background: #F5A623;
        border-radius: 2px;
        margin-bottom: 18px;
    }
    .sidebar-headline {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        color: #fff;
        font-size: 2rem;
        line-height: 1.15;
    }
    .sidebar-headline .highlight { color: #F5A623; }
    .sidebar-sub {
        color: rgba(255,255,255,0.6);
        font-size: 13px;
        margin-top: 14px;
        font-weight: 300;
        line-height: 1.6;
    }

    /* AI feature pill (sidebar) */
    .sidebar-ai-pill {
        position: relative;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(245,166,35,0.15);
        border: 1px solid rgba(245,166,35,0.35);
        border-radius: 999px;
        padding: 7px 14px;
        font-size: 11px;
        color: #F5A623;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        backdrop-filter: blur(6px);
        margin-bottom: 10px;
    }
    .sidebar-ai-pill svg { flex-shrink: 0; }

    /* ── MAIN FORM AREA ── */
    .auth-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Mobile header */
    .mobile-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 24px;
        border-bottom: 1px solid #EBEBEB;
    }
    @media (min-width: 1024px) {
        .mobile-header { display: none; }
    }
    .mobile-logo-icon {
        width: 28px;
        height: 28px;
        border-radius: 5px;
        overflow: hidden;
    }
    .mobile-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
    .mobile-logo-text {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        color: #1A1B2E;
        letter-spacing: 0.1em;
        font-size: 14px;
    }

    /* Form center */
    .auth-center {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
    }
    .auth-box { width: 100%; max-width: 420px; }

    /* Heading */
    .form-heading {
        margin-bottom: 32px;
        animation: fadeUp 0.5s forwards;
    }
    .form-accent-bar {
        width: 36px;
        height: 4px;
        background: #F5A623;
        border-radius: 2px;
        margin-bottom: 20px;
    }
    .form-title {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 2rem;
        color: #1A1B2E;
        line-height: 1.1;
    }
    .form-subtitle {
        color: #9CA3AF;
        font-size: 13px;
        margin-top: 8px;
    }

    /* AI Protection Badge (above form) */
    .ai-protect-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(90deg, rgba(245,166,35,0.07) 0%, rgba(245,166,35,0.03) 100%);
        border: 1px solid rgba(245,166,35,0.22);
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 22px;
        animation: fadeUp 0.5s 0.05s both;
    }
    .ai-protect-icon {
        width: 30px;
        height: 30px;
        background: rgba(245,166,35,0.12);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ai-protect-text strong {
        font-size: 11px;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        color: #1A1B2E;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        display: block;
    }
    .ai-protect-text span {
        font-size: 11px;
        color: #9CA3AF;
        display: block;
        margin-top: 1px;
    }

    /* Alerts */
    .alert {
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 13px;
        margin-bottom: 16px;
        animation: fadeUp 0.4s ease;
    }
    .alert-error {
        background: rgba(239,68,68,0.06);
        border: 1px solid rgba(239,68,68,0.25);
        color: #ef4444;
    }
    .alert-info {
        background: rgba(59,130,246,0.06);
        border: 1px solid rgba(59,130,246,0.2);
        color: #3b82f6;
    }

    /* Rate limit box — minimal */
    .rate-limit-box {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #FECACA;
        border-left: 3px solid #ef4444;
        border-radius: 6px;
        padding: 12px 14px;
        margin-bottom: 20px;
        animation: fadeUp 0.4s ease;
    }
    .rate-limit-icon {
        color: #ef4444;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .rate-limit-body { flex: 1; min-width: 0; }
    .rate-limit-title {
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 700;
        color: #1A1B2E;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }
    .rate-limit-sub {
        font-size: 12px;
        color: #9CA3AF;
        font-family: 'DM Sans', sans-serif;
    }
    .rate-limit-timer {
        font-family: 'poppins', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: #ef4444;
        flex-shrink: 0;
        letter-spacing: 0.04em;
        transition: color 0.5s;
        min-width: 46px;
        text-align: right;
    }
    .rate-limit-ready {
        font-size: 11px;
        color: #22c55e;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
    }

    /* Form fields */
    .form-group {
        margin-bottom: 18px;
        animation: fadeUp 0.5s both;
    }
    .form-group:nth-child(1) { animation-delay: 0.08s; }
    .form-group:nth-child(2) { animation-delay: 0.14s; }
    .form-label {
        display: block;
        font-size: 10px;
        font-family: 'Syne', sans-serif;
        color: #6B7280;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .form-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #E5E7EB;
        background: #fff;
        font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        color: #1A1B2E;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        border-radius: 2px;
    }
    .form-input:focus {
        border-color: #F5A623;
        box-shadow: 0 0 0 3px rgba(245,166,35,0.1);
    }
    .form-input::placeholder { color: #C4C9D4; }
    .form-input.is-error { border-color: #ef4444; }
    .form-input:disabled { opacity: 0.45; cursor: not-allowed; }

    .field-error {
        font-size: 11px;
        color: #ef4444;
        margin-top: 6px;
        font-family: 'Syne', sans-serif;
        font-weight: 600;
    }

    /* Password field */
    .pwd-wrap { position: relative; }
    .pwd-toggle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #9CA3AF;
        padding: 4px;
        display: flex;
        align-items: center;
        transition: color 0.2s;
    }
    .pwd-toggle:hover { color: #1A1B2E; }

    /* Submit button */
    .btn-submit-wrap {
        margin-bottom: 14px;
        animation: fadeUp 0.5s 0.22s both;
    }
    .btn-submit {
        width: 100%;
        padding: 15px;
        background: #1A1B2E;
        color: #fff;
        border: none;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.2s, background 0.2s;
        border-radius: 2px;
    }
    .btn-submit:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(26,27,46,0.25);
    }
    .btn-submit:active:not(:disabled) { transform: translateY(0); }
    .btn-submit:disabled {
        background: #E5E7EB;
        color: #9CA3AF;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* Device info line */
    .device-info {
        text-align: center;
        margin-bottom: 24px;
        animation: fadeUp 0.5s 0.25s both;
    }
    .device-info a {
        font-size: 11px;
        color: #C4C9D4;
        text-decoration: none;
        font-family: 'Syne', sans-serif;
        letter-spacing: 0.04em;
        transition: color 0.2s;
    }
    .device-info a:hover { color: #9CA3AF; }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        animation: fadeUp 0.5s 0.28s both;
    }
    .divider-line { flex: 1; height: 1px; background: #F0F0F0; }
    .divider-text { font-size: 11px; color: #C4C9D4; font-family: 'Syne', sans-serif; white-space: nowrap; }

    /* Social buttons */
    .social-row {
        display: flex;
        gap: 10px;
        margin-bottom: 32px;
        animation: fadeUp 0.5s 0.3s both;
    }
    .btn-social {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        border: 2px solid #E5E7EB;
        background: transparent;
        font-size: 13px;
        font-family: 'DM Sans', sans-serif;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        border-radius: 2px;
    }
    .btn-social:hover {
        border-color: #1A1B2E;
        background: #FAFAFA;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Footer */
    .auth-footer {
        display: none;
        justify-content: space-between;
        align-items: center;
        padding: 16px 32px;
        border-top: 1px solid #F0F0F0;
    }
    @media (min-width: 640px) {
        .auth-footer { display: flex; }
    }
    .footer-brand {
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        letter-spacing: 0.2em;
        color: #D1D5DB;
        font-weight: 700;
        text-transform: uppercase;
    }
    .footer-ai {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-family: 'Syne', sans-serif;
        color: #C4C9D4;
        letter-spacing: 0.05em;
    }
    .footer-ai .ai-name { color: #F5A623; font-weight: 700; }

    /* Animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Pulse dot for AI indicator */
    .pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        position: relative;
        flex-shrink: 0;
    }
    .pulse-dot::after {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        background: rgba(34,197,94,0.3);
        animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.6); opacity: 0; }
    }
</style>
@endpush

@section('content')
<div class="auth-shell">

    {{-- ── SIDEBAR ── --}}
    <aside class="auth-sidebar">

        <img
            src="https://imgs.search.brave.com/8cCFJWwWrANtuYGajwxP9RbbiOWaKs-BMuGc8kcFoI0/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudW5zcGxhc2gu/Y29tL3Bob3RvLTE2/NTk0NjkzNzc3Njgt/NGY0MmYyZjA5MWM1/P2ZtPWpwZyZxPTYw/Jnc9MzAwMCZhdXRv/PWZvcm1hdCZmaXQ9/Y3JvcCZpeGxpYj1y/Yi00LjEuMCZpeGlk/PU0zd3hNakEzZkRC/OE1IeHpaV0Z5WTJo/OE5IeDhaR0Z5YXlV/eU1HZHlZV1JwWlc1/MGZHVnVmREI4ZkRC/OGZId3c"
            class="sidebar-bg"
            alt="" />

        <div class="sidebar-overlay"></div>

        {{-- Tagline --}}
        <div class="sidebar-tagline">
            <div class="sidebar-ai-pill">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                AI-Powered Security
            </div>
            <div class="sidebar-accent-bar"></div>
            <p class="sidebar-headline">
                SSO<br>
                <span class="highlight">Authentication.</span>
            </p>
            <p class="sidebar-sub">
                Single Sign-On (SSO)<br>
                Simple login for all your applications.<br>
                Protected by an intelligent AI Risk Engine.
            </p>
        </div>

    </aside>

    {{-- ── MAIN AREA ── --}}
    <main class="auth-main">

        {{-- Mobile header --}}
        {{-- <div class="mobile-header">
            <div class="mobile-logo-icon">
                <img src="https://imgs.search.brave.com/7MJlL-HdJvur9xi9304BQyj1mWDNm7kqsYadjHkOisg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wNjcv/MDQ3Lzg3Ni9zbWFs/bC9maWVyeS1jcnlz/dGFsLW0tbGV0dGVy/LWxvZ28tYS1ib2xk/LWFuZC1lbGVnYW50/LWRlc2lnbi1vbi10/cmFuc3BhcmVudC1i/YWNrZ3JvdW5kLXBu/Zy5wbmc" alt="">
            </div>
            <span class="mobile-logo-text">Smart Auth With AI</span>
        </div> --}}

        {{-- Form center --}}
        <div class="auth-center">
            <div class="auth-box">

                {{-- Heading --}}
                <div class="form-heading">
                    <div class="form-accent-bar"></div>
                    <h1 class="form-title">SSO Login</h1>
                    <p class="form-subtitle">Welcome back — masuk ke akun Anda.</p>
                </div>

                {{-- AI Protection Banner --}}
                <div class="ai-protect-banner">
                    <div class="ai-protect-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#F5A623" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="ai-protect-text">
                        <strong>AI Risk Engine Aktif</strong>
                        <span>Login Anda dipantau & dilindungi secara real-time</span>
                    </div>
                    <div class="pulse-dot" style="margin-left:auto;"></div>
                </div>

                {{-- Session info --}}
                @if(session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif

                {{-- Rate limit countdown --}}
                @if(session('rate_limited') && session('retry_after'))
                <div class="rate-limit-box" id="rate-limit-box">
                    <svg class="rate-limit-icon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="rate-limit-body">
                        <div class="rate-limit-title">Terlalu Banyak Percobaan</div>
                        <div class="rate-limit-sub" id="cd-sub">Tunggu hingga dapat mencoba kembali</div>
                    </div>
                    <div class="rate-limit-timer" id="cd-timer">00:00</div>
                </div>
                @endif

                {{-- Email error (non rate-limit) --}}
                @if($errors->has('email') && !session('rate_limited'))
                    <div class="alert alert-error">{{ $errors->first('email') }}</div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('login.post') }}" id="login-form">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="input-email">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="input-email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            value="{{ old('email') }}"
                            placeholder="nama@domain.com"
                            autofocus
                            required
                            {{ session('rate_limited') ? 'disabled' : '' }}
                        />
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="form-label" for="input-password">Password</label>
                        <div class="pwd-wrap">
                            <input
                                type="password"
                                name="password"
                                id="input-password"
                                class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                                placeholder="••••••••••"
                                required
                                {{ session('rate_limited') ? 'disabled' : '' }}
                            />
                            <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Toggle password">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="btn-submit-wrap">
                        <button type="submit" class="btn-submit" id="btn-submit" {{ session('rate_limited') ? 'disabled' : '' }}>
                            LOGIN
                        </button>
                    </div>

                </form>

                {{-- Device info --}}
                <div class="device-info">
                    <a href="#">your ip : 122.133.131.13 | your device : windows, chrome</a>
                </div>

                {{-- Divider --}}
                <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">or login with</span>
                    <div class="divider-line"></div>
                </div>

                {{-- Social --}}
                <div class="social-row">
                    <button class="btn-social" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/>
                        </svg>
                        Google
                    </button>
                    <button class="btn-social" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m10.213 1.471.691-1.26q.069-.124-.048-.192-.128-.057-.195.058l-.7 1.27A4.8 4.8 0 0 0 8.005.941q-1.032 0-1.956.404l-.7-1.27Q5.281-.037 5.154.02q-.117.069-.049.193l.691 1.259a4.25 4.25 0 0 0-1.673 1.476A3.7 3.7 0 0 0 3.5 5.02h9q0-1.125-.623-2.072a4.27 4.27 0 0 0-1.664-1.476ZM6.22 3.303a.37.37 0 0 1-.267.11.35.35 0 0 1-.263-.11.37.37 0 0 1-.107-.264.37.37 0 0 1 .107-.265.35.35 0 0 1 .263-.11q.155 0 .267.11a.36.36 0 0 1 .112.265.36.36 0 0 1-.112.264m4.101 0a.35.35 0 0 1-.262.11.37.37 0 0 1-.268-.11.36.36 0 0 1-.112-.264q0-.154.112-.265a.37.37 0 0 1 .268-.11q.155 0 .262.11a.37.37 0 0 1 .107.265q0 .153-.107.264M3.5 11.77q0 .441.311.75.311.306.76.307h.758l.01 2.182q0 .414.292.703a.96.96 0 0 0 .7.288.97.97 0 0 0 .71-.288.95.95 0 0 0 .292-.703v-2.182h1.343v2.182q0 .414.292.703a.97.97 0 0 0 .71.288.97.97 0 0 0 .71-.288.95.95 0 0 0 .292-.703v-2.182h.76q.436 0 .749-.308.31-.307.311-.75V5.365h-9zm10.495-6.587a.98.98 0 0 0-.702.278.9.9 0 0 0-.293.685v4.063q0 .406.293.69a.97.97 0 0 0 .702.284q.42 0 .712-.284a.92.92 0 0 0 .293-.69V6.146a.9.9 0 0 0-.293-.685 1 1 0 0 0-.712-.278m-12.702.283a1 1 0 0 1 .712-.283q.41 0 .702.283a.9.9 0 0 1 .293.68v4.063a.93.93 0 0 1-.288.69.97.97 0 0 1-.707.284 1 1 0 0 1-.712-.284.92.92 0 0 1-.293-.69V6.146q0-.396.293-.68"/>
                        </svg>
                        Mixu Apps
                    </button>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="auth-footer">
            <span class="footer-brand">Mixudev</span>
            <div class="footer-ai">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Protected by <span class="ai-name">&nbsp;AI Risk Engine</span>
            </div>
        </div>

    </main>

</div>
@endsection

@push('scripts')
<script>
    function togglePwd() {
        const input = document.getElementById('input-password');
        input.type = (input.type === 'password') ? 'text' : 'password';
    }
</script>

@if(session('rate_limited') && session('retry_after'))
<script>
(function () {
    const totalSeconds = {{ (int) session('retry_after') }};
    let remaining = totalSeconds;

    const elTimer   = document.getElementById('cd-timer');
    const elSub     = document.getElementById('cd-sub');
    const btnSubmit = document.getElementById('btn-submit');
    const inputEmail = document.getElementById('input-email');
    const inputPass  = document.getElementById('input-password');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        if (remaining <= 0) {
            elTimer.textContent = '00:00';
            elTimer.style.color = '#22c55e';
            elSub.innerHTML = '<span class="rate-limit-ready">✓ Dapat mencoba kembali</span>';
            btnSubmit.disabled   = false;
            inputEmail.disabled  = false;
            inputPass.disabled   = false;
            return;
        }

        const mins = Math.floor(remaining / 60);
        const secs = remaining % 60;
        elTimer.textContent = pad(mins) + ':' + pad(secs);

        if (remaining <= 30) elTimer.style.color = '#f59e0b';

        remaining--;
        setTimeout(tick, 1000);
    }

    tick();
})();
</script>
@endif
@endpush