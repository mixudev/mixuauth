@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<style>
    body { background: var(--bg); }
    .auth-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .auth-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--border) 1px, transparent 1px),
            linear-gradient(90deg, var(--border) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: 0.3;
        mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
    }
    .auth-wrap::after {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,212,170,0.06) 0%, transparent 70%);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .auth-card {
        width: 100%;
        max-width: 420px;
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 40px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.4s ease;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .auth-header { text-align: center; margin-bottom: 32px; }
    .auth-logo {
        width: 48px; height: 48px;
        background: var(--accent);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--mono);
        font-size: 18px;
        font-weight: 700;
        color: var(--bg);
        margin: 0 auto 16px;
        box-shadow: 0 0 30px rgba(0,212,170,0.3);
    }
    .auth-title {
        font-family: var(--mono);
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.02em;
    }
    .auth-subtitle { font-size: 13px; color: var(--text3); margin-top: 6px; }
    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text2);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .form-input {
        width: 100%;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 14px;
        font-family: var(--sans);
        color: var(--text);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,212,170,0.1); }
    .form-input::placeholder { color: var(--text3); }
    .form-input.is-error { border-color: var(--danger); }
    .form-input:disabled { opacity: 0.5; cursor: not-allowed; }
    .field-error { font-size: 12px; color: var(--danger); margin-top: 6px; font-family: var(--mono); }
    .btn-primary {
        width: 100%;
        padding: 11px;
        background: var(--accent);
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: var(--sans);
        color: var(--bg);
        cursor: pointer;
        transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        margin-top: 8px;
        letter-spacing: 0.01em;
    }
    .btn-primary:hover { background: var(--accent2); box-shadow: 0 4px 20px rgba(0,212,170,0.3); }
    .btn-primary:active { transform: scale(0.99); }
    .btn-primary:disabled {
        background: var(--border2);
        color: var(--text3);
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* Rate limit box */
    .rate-limit-box {
        background: rgba(248,81,73,0.06);
        border: 1px solid rgba(248,81,73,0.25);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
        text-align: center;
    }
    .rate-limit-title {
        font-family: var(--mono);
        font-size: 12px;
        color: var(--danger);
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .countdown-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .countdown-block {
        background: var(--bg);
        border: 1px solid var(--border2);
        border-radius: 6px;
        padding: 8px 12px;
        min-width: 52px;
        text-align: center;
    }
    .countdown-number {
        font-family: var(--mono);
        font-size: 24px;
        font-weight: 700;
        color: var(--danger);
        line-height: 1;
        display: block;
    }
    .countdown-label {
        font-family: var(--mono);
        font-size: 9px;
        color: var(--text3);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 4px;
        display: block;
    }
    .countdown-sep {
        font-family: var(--mono);
        font-size: 20px;
        color: var(--text3);
        margin-bottom: 8px;
    }
    .rate-limit-sub {
        font-size: 11px;
        color: var(--text3);
        font-family: var(--mono);
    }
    .rate-limit-ready {
        font-size: 12px;
        color: var(--accent);
        font-family: var(--mono);
        font-weight: 700;
        animation: fadeUp 0.3s ease;
    }

    /* Progress bar */
    .countdown-progress {
        height: 3px;
        background: var(--border);
        border-radius: 2px;
        margin-top: 10px;
        overflow: hidden;
    }
    .countdown-progress-fill {
        height: 100%;
        background: var(--danger);
        border-radius: 2px;
        transition: width 1s linear;
    }

    .ai-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 24px;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text3);
    }
    .ai-badge span { color: var(--accent); }
</style>
@endpush

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">SA</div>
            <div class="auth-title">SECURE<span style="color:var(--accent)">AUTH</span></div>
            <div class="auth-subtitle">Masuk dengan perlindungan AI</div>
        </div>

        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        {{-- Rate limit countdown --}}
        @if(session('rate_limited') && session('retry_after'))
        <div class="rate-limit-box" id="rate-limit-box">
            <div class="rate-limit-title">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Terlalu Banyak Percobaan
            </div>
            <div class="countdown-wrap">
                <div class="countdown-block">
                    <span class="countdown-number" id="cd-minutes">00</span>
                    <span class="countdown-label">menit</span>
                </div>
                <span class="countdown-sep">:</span>
                <div class="countdown-block">
                    <span class="countdown-number" id="cd-seconds">00</span>
                    <span class="countdown-label">detik</span>
                </div>
            </div>
            <div class="countdown-progress">
                <div class="countdown-progress-fill" id="cd-progress" style="width:100%"></div>
            </div>
            <div class="rate-limit-sub" id="cd-sub">Tunggu hingga dapat mencoba kembali</div>
        </div>
        @endif

        @if($errors->has('email') && !session('rate_limited'))
            <div class="alert alert-error">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="login-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
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
                >
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    id="input-password"
                    class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                    placeholder="••••••••"
                    required
                    {{ session('rate_limited') ? 'disabled' : '' }}
                >
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-primary" id="btn-submit" {{ session('rate_limited') ? 'disabled' : '' }}>
                Masuk
            </button>
        </form>

        <div class="ai-badge">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Dilindungi oleh <span>AI Risk Engine</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(session('rate_limited') && session('retry_after'))
<script>
(function() {
    const totalSeconds = {{ (int) session('retry_after') }};
    let remaining = totalSeconds;

    const elMinutes  = document.getElementById('cd-minutes');
    const elSeconds  = document.getElementById('cd-seconds');
    const elProgress = document.getElementById('cd-progress');
    const elSub      = document.getElementById('cd-sub');
    const elBox      = document.getElementById('rate-limit-box');
    const btnSubmit  = document.getElementById('btn-submit');
    const inputEmail = document.getElementById('input-email');
    const inputPass  = document.getElementById('input-password');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        if (remaining <= 0) {
            // Countdown selesai — enable form
            elSub.innerHTML = '<span class="rate-limit-ready">✓ Anda dapat mencoba kembali sekarang</span>';
            elProgress.style.width = '0%';
            elProgress.style.background = 'var(--accent)';
            elMinutes.textContent = '00';
            elSeconds.textContent = '00';
            elMinutes.style.color = 'var(--accent)';
            elSeconds.style.color = 'var(--accent)';

            btnSubmit.disabled  = false;
            inputEmail.disabled = false;
            inputPass.disabled  = false;
            btnSubmit.textContent = 'Masuk';
            return;
        }

        const mins = Math.floor(remaining / 60);
        const secs = remaining % 60;

        elMinutes.textContent = pad(mins);
        elSeconds.textContent = pad(secs);

        // Progress bar mengecil seiring waktu
        const pct = (remaining / totalSeconds) * 100;
        elProgress.style.width = pct + '%';

        // Warna berubah ke warn saat hampir selesai
        if (remaining <= 30) {
            elProgress.style.background = 'var(--warn)';
            elMinutes.style.color = 'var(--warn)';
            elSeconds.style.color = 'var(--warn)';
        }

        elSub.textContent = 'Tunggu hingga dapat mencoba kembali';
        remaining--;
        setTimeout(tick, 1000);
    }

    tick();
})();
</script>
@endif
@endpush