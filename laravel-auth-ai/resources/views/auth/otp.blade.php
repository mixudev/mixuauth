@extends('layouts.app')

@section('title', 'Verifikasi OTP')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="min-h-screen bg-blue-50 flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Ambient blobs --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-200 rounded-full opacity-40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-purple-200 rounded-full opacity-30 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-yellow-100 rounded-full opacity-50 blur-3xl pointer-events-none"></div>

    {{-- Dot grid --}}
    <div class="absolute inset-0 pointer-events-none opacity-40"
         style="background-image: radial-gradient(circle, #92400e20 1px, transparent 1px); background-size: 28px 28px;">
    </div>

    {{-- Card wrapper --}}
    <div class="relative w-full max-w-md">

        {{-- Top accent line --}}
        <div class="absolute -top-px left-16 right-16 h-px bg-gradient-to-r from-transparent via-blue-400 to-transparent z-10"></div>

        {{-- Card --}}
        <div class="bg-white/80 backdrop-blur-xl border border-blue-100 rounded-3xl shadow-2xl shadow-blue-100/70 px-10 py-12">

            {{-- ── Header ── --}}
            <div class="flex flex-col items-center text-center mb-10">

                {{-- Lock icon --}}
                <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border border-blue-300 opacity-30 animate-ping"></div>
                    <div class="absolute inset-2 rounded-full border border-blue-200 opacity-60 animate-pulse"></div>
                    <div class="relative w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-400 rounded-2xl shadow-lg shadow-blue-300/50 flex items-center justify-center rotate-3 transition-transform duration-300 hover:rotate-0">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                    </div>
                </div>

                <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight">
                    Verifikasi <em class="text-blue-500 not-italic italic">Identitas</em>
                </h1>

                <p class="mt-3 text-sm text-stone-400 leading-relaxed">
                    Kode 6 digit telah dikirimkan ke
                </p>

                <span class="mt-2 inline-flex items-center gap-1.5 font-['JetBrains_Mono'] text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full px-3 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    {{ session('otp_email', 'email@kamu.com') }}
                </span>
            </div>

            {{-- ── Error ── --}}
            @error('otp_code')
                <div class="flex items-center gap-2.5 bg-red-50 border border-red-100 text-red-500 rounded-xl px-4 py-3 mb-6 font-['JetBrains_Mono'] text-xs">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror

            {{-- ── Form ── --}}
            <form method="POST" action="{{ route('otp.verify.post') }}" id="otpForm">
                @csrf
                <input type="hidden" name="otp_code" id="otpHidden">

                <p class="font-['JetBrains_Mono'] text-[10px] uppercase tracking-[0.2em] text-stone-400 text-center mb-4">
                    Masukkan kode OTP
                </p>

                {{-- Digit boxes --}}
                <div class="flex items-center justify-center gap-2 mb-3">
                    @for($i = 0; $i < 3; $i++)
                        <input
                            type="text"
                            class="otp-digit w-12 h-14 bg-stone-50 border border-stone-200 rounded-xl text-center font-['JetBrains_Mono'] text-xl font-semibold text-stone-700 outline-none transition-all duration-200 focus:border-blue-400 focus:bg-blue-50/60 focus:shadow-[0_0_0_3px_rgba(251,191,36,0.15)] focus:-translate-y-0.5"
                            maxlength="1"
                            inputmode="numeric"
                            data-index="{{ $i }}"
                            autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                            aria-label="Digit {{ $i + 1 }}"
                        >
                    @endfor

                    <span class="text-stone-300 text-2xl font-light select-none pb-1">·</span>

                    @for($i = 3; $i < 6; $i++)
                        <input
                            type="text"
                            class="otp-digit w-12 h-14 bg-stone-50 border border-stone-200 rounded-xl text-center font-['JetBrains_Mono'] text-xl font-semibold text-stone-700 outline-none transition-all duration-200 focus:border-blue-400 focus:bg-blue-50/60 focus:shadow-[0_0_0_3px_rgba(251,191,36,0.15)] focus:-translate-y-0.5"
                            maxlength="1"
                            inputmode="numeric"
                            data-index="{{ $i }}"
                            autocomplete="off"
                            aria-label="Digit {{ $i + 1 }}"
                        >
                    @endfor
                </div>

                {{-- Progress bar --}}
                <div class="h-0.5 bg-stone-100 rounded-full mx-2 mb-7 overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-blue-400 to-purple-300 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                </div>

                {{-- Submit button --}}
                <button
                    type="submit"
                    id="submitBtn"
                    disabled
                    class="w-full flex items-center justify-center gap-2.5 bg-gradient-to-br from-blue-400 to-purple-400 hover:from-blue-300 hover:to-purple-300 disabled:from-stone-200 disabled:to-stone-200 disabled:text-stone-400 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-blue-200/60 disabled:shadow-none hover:shadow-blue-300/60 hover:-translate-y-0.5 disabled:translate-y-0 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 12c0 6.627 5.373 12 12 12s12-5.373 12-12c0-2.127-.557-4.124-1.534-5.857"/>
                    </svg>
                    Verifikasi Sekarang
                </button>
            </form>

            {{-- ── Footer ── --}}
            <div class="mt-7 flex flex-col items-center gap-4">

                <div class="flex items-center gap-2 font-['JetBrains_Mono'] text-xs text-stone-400">
                    <span id="timerDot" class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse flex-shrink-0"></span>
                    <span>Berlaku selama</span>
                    <span id="timerDisplay" class="text-blue-500 font-medium">
                        {{ session('otp_expires_in', '05:00') }}
                    </span>
                </div>

                <div class="w-full h-px bg-stone-100"></div>

                <a href="{{ route('login') }}" class="group inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-stone-600 font-['JetBrains_Mono'] transition-colors duration-200">
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke halaman login
                </a>

            </div>
        </div>

        {{-- Depth shadow layer --}}
        <div class="absolute -bottom-3 left-8 right-8 h-8 bg-blue-200/50 rounded-3xl blur-lg -z-10"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const digits    = document.querySelectorAll('.otp-digit');
    const hidden    = document.getElementById('otpHidden');
    const submitBtn = document.getElementById('submitBtn');
    const progress  = document.getElementById('progressBar');

    function setFilled(el, filled) {
        if (filled) {
            el.classList.add('border-blue-300', 'bg-blue-50', 'text-blue-600');
            el.classList.remove('border-stone-200', 'bg-stone-50', 'text-stone-700');
        } else {
            el.classList.remove('border-blue-300', 'bg-blue-50', 'text-blue-600');
            el.classList.add('border-stone-200', 'bg-stone-50', 'text-stone-700');
        }
    }

    function updateState() {
        const val    = Array.from(digits).map(d => d.value).join('');
        hidden.value = val;
        progress.style.width    = (val.length / 6 * 100) + '%';
        submitBtn.disabled      = val.length < 6;
        digits.forEach(d => setFilled(d, !!d.value));
    }

    digits.forEach((input, i) => {
        input.addEventListener('input', e => {
            const clean = e.target.value.replace(/\D/g, '');
            e.target.value = clean.slice(-1);
            if (clean) {
                input.style.transform = 'scale(1.14) translateY(-3px)';
                setTimeout(() => { input.style.transform = ''; }, 170);
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
            timerEl.classList.replace('text-blue-500', 'text-red-400');
            timerDot.classList.replace('bg-blue-400', 'bg-red-400');
            return;
        }
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
    }, 1000);
})();
</script>
@endpush