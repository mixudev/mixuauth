@extends('layouts.app')

@section('title', 'Smart Auth With AI')

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

                    {{-- Hidden Input Timezone --}}
                    <input type="hidden" name="_timezone" id="_timezone_input">

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

<script>
(function () {
    'use strict';
 
    var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    if (!tz) return;
 
    // 1. Isi hidden input di form login jika ada di halaman ini
    //    Dengan ini, timezone ikut terkirim saat form di-submit secara normal
    var hiddenInput = document.getElementById('_timezone_input');
    if (hiddenInput) {
        hiddenInput.value = tz;
    }
 
    // 2. Juga kirim via fetch untuk set session (berlaku di semua halaman)
    //    Ini berguna untuk halaman yang tidak punya form login
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) return;
 
    // Skip jika timezone yang sama sudah pernah dikirim di tab ini
    var sentKey = 'tz_sent_' + tz;
    if (sessionStorage.getItem(sentKey)) return;
 
    fetch('/timezone/set', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content'),
            'X-Timezone':   tz,
        },
        body: JSON.stringify({ timezone: tz }),
    }).then(function (r) {
        if (r.ok) sessionStorage.setItem(sentKey, '1');
    }).catch(function () {
        // Gagal kirim — tidak masalah, fallback ke UTC
    });
})();
</script>

@endpush