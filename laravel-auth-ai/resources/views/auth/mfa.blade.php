@extends('layouts.app')

@section('title', 'Verifikasi Keamanan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    body { font-family: 'DM Sans', sans-serif; background: #f8f8f6; }
    .font-mono { font-family: 'DM Mono', monospace; }

    .digit-input:focus { transform: translateY(-1px); }
    .digit-input.filled { border-color: #1a1a1a !important; background: #ffffff !important; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin { animation: spin 0.7s linear infinite; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#f8f8f6] flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-[420px]">

        {{-- Card --}}
        <div class="bg-white border border-[#e2e2de] rounded-[20px] px-10 py-12 relative">

            {{-- Top accent line --}}
            <div class="absolute top-0 left-10 right-10 h-px bg-gradient-to-r from-transparent via-[#1a1a1a] to-transparent"></div>

            {{-- Header --}}
            <div class="flex flex-col items-center text-center mb-10">
                <div class="w-[52px] h-[52px] bg-[#1a1a1a] rounded-[14px] flex items-center justify-center mb-6">
                    @if(($type ?? 'email') === 'totp')
                        <svg class="w-[22px] h-[22px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    @else
                        <svg class="w-[22px] h-[22px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    @endif
                </div>

                <h1 class="text-[22px] font-medium text-[#1a1a1a] tracking-tight mb-2">Verifikasi kode</h1>

                <p class="text-[13.5px] text-[#888884] leading-relaxed max-w-[300px]">
                    @if(($type ?? 'email') === 'totp')
                        Buka aplikasi Authenticator dan masukkan 6 digit kode untuk akun ini.
                    @else
                        Masukkan 6 digit kode yang kami kirimkan ke alamat email Anda.
                    @endif
                </p>

                @if(($type ?? 'email') === 'email')
                <div class="mt-4 inline-flex items-center gap-1.5 px-3 py-1 bg-[#f3f3f0] border border-[#e2e2de] rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0"></span>
                    <span class="font-mono text-[11.5px] text-[#555552]">{{ $email ?? 'user@example.com' }}</span>
                </div>
                @endif
            </div>

            {{-- Error --}}
            @error('code')
            <div class="mb-7 flex items-start gap-2.5 p-3 bg-red-50 border border-red-100 rounded-xl text-[13px] text-red-600 leading-relaxed">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                {{ $message }}
            </div>
            @enderror

            {{-- Form --}}
            <form action="{{ route('auth.mfa.verify.post') }}" method="POST" id="mfaForm">
                @csrf
                <input type="hidden" name="code" id="mfaCodeHidden">

                {{-- Digit Inputs --}}
                <div class="flex gap-2 mb-5">
                    @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            class="digit-input w-full aspect-square border border-[#e2e2de] rounded-xl bg-[#fafaf8] text-center font-mono text-[22px] font-medium text-[#1a1a1a] outline-none transition-all duration-150 focus:border-[#1a1a1a] focus:bg-white"
                            maxlength="1"
                            inputmode="numeric"
                            data-index="{{ $i }}"
                            autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                        >
                    @endfor
                </div>

                {{-- Progress --}}
                <div class="w-full h-px bg-[#e8e8e4] rounded-full overflow-hidden mb-7">
                    <div id="mfaProgressBar" class="h-full bg-[#1a1a1a] transition-all duration-300 w-0"></div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="mfaSubmitBtn" disabled
                    class="w-full py-3.5 bg-[#1a1a1a] disabled:bg-[#f0f0ec] disabled:text-[#b0b0ab] text-white text-[14px] font-medium rounded-xl flex items-center justify-center gap-2 transition-all duration-150 hover:bg-[#2d2d2d] active:scale-[0.99]">
                    <span id="btnText">Verifikasi</span>
                    <svg id="btnLoader" class="hidden animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-8 pt-6 border-t border-[#f0f0ec] flex flex-col items-center gap-4">
                @if(($type ?? 'email') === 'email')
                <p class="text-[13px] text-[#888884]">
                    Tidak menerima kode?
                    <button type="button" id="resendBtn" disabled class="font-medium text-[#1a1a1a] underline underline-offset-2 disabled:text-[#ccc] disabled:no-underline">Kirim ulang</button>
                    <span id="resendTimer" class="font-mono text-[11px] text-[#aaa]">(60d)</span>
                </p>
                @endif

                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[13px] text-[#888884] hover:text-[#1a1a1a] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke login
                </a>
            </div>
        </div>

        {{-- Security badge --}}
        <div class="mt-6 flex items-center justify-center gap-1.5 text-[10.5px] text-[#aaa] tracking-wide">
            <svg class="w-[11px] h-[11px]" fill="none" stroke="#4ade80" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            Koneksi terenkripsi &amp; aman
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const digits = document.querySelectorAll('.digit-input');
    const hiddenInput = document.getElementById('mfaCodeHidden');
    const submitBtn = document.getElementById('mfaSubmitBtn');
    const progressBar = document.getElementById('mfaProgressBar');
    const form = document.getElementById('mfaForm');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    const resendBtn = document.getElementById('resendBtn');
    const timerTxt = document.getElementById('resendTimer');

    let sec = 60;
    const tick = setInterval(() => {
        sec--;
        timerTxt.textContent = sec > 0 ? `(${sec}d)` : '';
        if (sec <= 0) { clearInterval(tick); if(resendBtn) resendBtn.disabled = false; }
    }, 1000);

    const update = () => {
        const code = Array.from(digits).map(d => d.value).join('');
        hiddenInput.value = code;
        progressBar.style.width = (code.length / 6 * 100) + '%';
        submitBtn.disabled = code.length < 6;
        digits.forEach(d => d.value ? d.classList.add('filled') : d.classList.remove('filled'));
    };

    digits.forEach((d, i) => {
        d.addEventListener('input', e => {
            d.value = e.target.value.replace(/\D/g,'').slice(-1);
            if (d.value && i < 5) digits[i+1].focus();
            update();
        });
        d.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !d.value && i > 0) digits[i-1].focus();
            if (e.key === 'ArrowLeft' && i > 0) digits[i-1].focus();
            if (e.key === 'ArrowRight' && i < 5) digits[i+1].focus();
        });
        d.addEventListener('focus', () => d.select());
        d.addEventListener('paste', e => {
            e.preventDefault();
            const data = e.clipboardData.getData('text').replace(/\D/g,'').split('');
            data.forEach((c, j) => { if (digits[i+j]) digits[i+j].value = c; });
            update();
            digits[Math.min(i + data.length, 5)].focus();
        });
    });

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        btnText.textContent = 'Memverifikasi...';
        btnLoader.classList.remove('hidden');
    });

    digits[0].focus();
});
</script>
@endpush