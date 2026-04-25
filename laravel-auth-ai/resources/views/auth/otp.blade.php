@extends('layouts.auth')

@section('title', 'Verifikasi OTP')
@section('auth_title', 'Verifikasi Identitas')
@section('auth_subtitle', 'Kode 6 digit telah dikirimkan ke ' . (session('otp_email') ?? 'email Anda') . '.')
@section('show_ai_banner', true)

@section('auth_content')

    {{-- ── Error ── --}}
    @error('otp_code')
        <div class="alert alert-error">
            {{ $message }}
        </div>
    @enderror

    {{-- ── Form ── --}}
    <form method="POST" action="{{ route('otp.verify.post') }}" id="otpForm">
        @csrf
        <input type="hidden" name="otp_code" id="otpHidden">

        <p style="font-family: 'Syne', sans-serif; font-size: 10px; uppercase; letter-spacing: 0.15em; color: #9CA3AF; text-align: center; margin-bottom: 16px;">
            MASUKKAN KODE OTP
        </p>

        {{-- Digit boxes --}}
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 24px;">
            @for($i = 0; $i < 3; $i++)
                <input
                    type="text"
                    class="otp-digit form-input"
                    style="width: 48px; height: 56px; text-align: center; font-size: 20px; font-weight: 700;"
                    maxlength="1"
                    inputmode="numeric"
                    data-index="{{ $i }}"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    aria-label="Digit {{ $i + 1 }}"
                >
            @endfor

            <span style="color: #E5E7EB; font-size: 20px; font-weight: 300;">—</span>

            @for($i = 3; $i < 6; $i++)
                <input
                    type="text"
                    class="otp-digit form-input"
                    style="width: 48px; height: 56px; text-align: center; font-size: 20px; font-weight: 700;"
                    maxlength="1"
                    inputmode="numeric"
                    data-index="{{ $i }}"
                    autocomplete="off"
                    aria-label="Digit {{ $i + 1 }}"
                >
            @endfor
        </div>

        {{-- Submit button --}}
        <div class="btn-submit-wrap">
            <button
                type="submit"
                id="submitBtn"
                disabled
                class="btn-submit"
            >
                Verifikasi Sekarang
            </button>
        </div>
    </form>

    {{-- ── Footer ── --}}
    <div style="margin-top: 24px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #9CA3AF;">
            <div id="timerDot" class="pulse-dot"></div>
            <span>Berlaku selama</span>
            <span id="timerDisplay" style="color: #F5A623; font-weight: 700;">
                {{ session('otp_expires_in', '05:00') }}
            </span>
        </div>
    </div>

@endsection

@section('auth_footer_extra')
    <a href="{{ route('login') }}" style="font-size: 12px; color: #9CA3AF; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke halaman login
    </a>
@endsection

@push('scripts')
<script>
(function () {
    const digits    = document.querySelectorAll('.otp-digit');
    const hidden    = document.getElementById('otpHidden');
    const submitBtn = document.getElementById('submitBtn');

    function updateState() {
        const val    = Array.from(digits).map(d => d.value).join('');
        hidden.value = val;
        submitBtn.disabled = val.length < 6;
    }

    digits.forEach((input, i) => {
        input.addEventListener('input', e => {
            const clean = e.target.value.replace(/\D/g, '');
            e.target.value = clean.slice(-1);
            if (clean) {
                if (i < 5) digits[i + 1].focus();
            }
            updateState();
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace') {
                if (!e.target.value && i > 0) {
                    digits[i - 1].value = '';
                    digits[i - 1].focus();
                } else {
                    e.target.value = '';
                }
                updateState();
                e.preventDefault();
            }
            if (e.key === 'ArrowLeft'  && i > 0) digits[i - 1].focus();
            if (e.key === 'ArrowRight' && i < 5) digits[i + 1].focus();
        });

        input.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            text.split('').slice(0, 6).forEach((char, j) => { if (digits[j]) digits[j].value = char; });
            digits[Math.min(text.length, 5)].focus();
            updateState();
        });

        input.addEventListener('click', () => input.select());
    });

    digits[0].focus();

    /* ── Countdown ── */
    const timerEl  = document.getElementById('timerDisplay');
    const timerDot = document.getElementById('timerDot');
    const mmss     = timerEl.textContent.trim().match(/^(\d+):(\d{2})$/);
    let   seconds  = mmss ? parseInt(mmss[1]) * 60 + parseInt(mmss[2]) : 300;

    const tick = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(tick);
            timerEl.textContent = 'Kedaluwarsa';
            timerEl.style.color = '#ef4444';
            timerDot.style.background = '#ef4444';
            return;
        }
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
    }, 1000);
})();
</script>
@endpush