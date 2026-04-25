@extends('layouts.auth')

@section('title', 'Verifikasi Keamanan')
@section('auth_title', 'Verifikasi Kode')
@section('auth_subtitle')
    @if(($type ?? 'email') === 'totp')
        Buka aplikasi Authenticator dan masukkan 6 digit kode untuk akun ini.
    @else
        Masukkan 6 digit kode yang kami kirimkan ke alamat email Anda: <strong>{{ $email ?? 'user@example.com' }}</strong>
    @endif
@endsection
@section('show_ai_banner', true)

@section('auth_content')

    {{-- Error --}}
    @error('code')
        <div class="alert alert-error">
            {{ $message }}
        </div>
    @enderror

    {{-- Form --}}
    {{-- Form --}}
    <form action="{{ route('auth.mfa.verify.post') }}" method="POST" id="mfaForm">
        @csrf
        <input type="hidden" name="code" id="mfaCodeHidden">
        <input type="hidden" name="recovery_mode" id="mfaRecoveryMode" value="0">

        {{-- TOTP Mode (Digits) --}}
        <div id="totpSection">
            <div style="display: flex; gap: 8px; margin-bottom: 24px;">
                @for($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        class="digit-input form-input"
                        style="width: 100%; aspect-ratio: 1/1; text-align: center; font-size: 20px; font-weight: 700; padding: 0;"
                        maxlength="1"
                        inputmode="numeric"
                        data-index="{{ $i }}"
                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    >
                @endfor
            </div>

            {{-- Progress Bar --}}
            <div style="width: 100%; height: 2px; background: #F3F4F6; border-radius: 99px; overflow: hidden; margin-bottom: 24px;">
                <div id="mfaProgressBar" style="height: 100%; width: 0%; background: #1A1B2E; transition: all 0.3s;"></div>
            </div>
        </div>

        {{-- Backup Code Mode (Single Input) --}}
        <div id="backupSection" style="display: none; margin-bottom: 24px;">
            <div class="form-group">
                <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6B7280; letter-spacing: 0.05em; margin-bottom: 8px; display: block;">
                    Masukkan Kode Cadangan
                </label>
                <input 
                    type="text" 
                    id="backupCodeInput"
                    placeholder="Contoh: ABC123DEFG"
                    class="form-input"
                    style="text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; text-align: center;"
                >
                <p style="font-size: 11px; color: #9CA3AF; mt: 8px; line-height: 1.5;">
                    * Setiap kode cadangan hanya bisa digunakan satu kali.
                </p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="btn-submit-wrap">
            <button type="submit" id="mfaSubmitBtn" disabled class="btn-submit">
                <span id="btnText">Verifikasi</span>
            </button>
        </div>
    </form>

    {{-- Footer --}}
    <div style="margin-top: 24px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
        @if(($type ?? 'email') === 'email')
        <p style="font-size: 13px; color: #9CA3AF;">
            Tidak menerima kode?
            <button type="button" id="resendBtn" disabled style="background: none; border: none; padding: 0; color: #1A1B2E; font-weight: 700; cursor: pointer; text-decoration: underline;">Kirim ulang</button>
            <span id="resendTimer" style="font-size: 11px; color: #D1D5DB;">(60s)</span>
        </p>
        @endif

        @if(($type ?? 'email') === 'totp')
            <button 
                type="button" 
                id="toggleModeBtn"
                class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-600 transition-colors duration-200 focus:outline-none"
            >
                <i class="fa-solid fa-shield-halved text-[11px]"></i>
                <span id="toggleText">Gunakan kode cadangan</span>
            </button>
        @endif
    </div>

@endsection

@section('auth_footer_extra')
    <a href="{{ route('login') }}" style="font-size: 12px; color: #9CA3AF; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke login
    </a>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const digits = document.querySelectorAll('.digit-input');
    const hiddenInput = document.getElementById('mfaCodeHidden');
    const recoveryModeInput = document.getElementById('mfaRecoveryMode');
    const submitBtn = document.getElementById('mfaSubmitBtn');
    const progressBar = document.getElementById('mfaProgressBar');
    const form = document.getElementById('mfaForm');
    const btnText = document.getElementById('btnText');
    const resendBtn = document.getElementById('resendBtn');
    const timerTxt = document.getElementById('resendTimer');
    
    // Recovery Mode Elements
    const totpSection = document.getElementById('totpSection');
    const backupSection = document.getElementById('backupSection');
    const toggleModeBtn = document.getElementById('toggleModeBtn');
    const toggleText = document.getElementById('toggleText');
    const backupCodeInput = document.getElementById('backupCodeInput');
    const subtitle = document.querySelector('.auth-subtitle');

    let isRecoveryMode = false;

    // Toggle Mode Logic
    if (toggleModeBtn) {
        toggleModeBtn.addEventListener('click', () => {
            isRecoveryMode = !isRecoveryMode;
            recoveryModeInput.value = isRecoveryMode ? '1' : '0';
            
            if (isRecoveryMode) {
                totpSection.style.display = 'none';
                backupSection.style.display = 'block';
                toggleText.textContent = 'Gunakan kode autentikator';
                submitBtn.disabled = backupCodeInput.value.length < 8;
                backupCodeInput.focus();
            } else {
                totpSection.style.display = 'block';
                backupSection.style.display = 'none';
                toggleText.textContent = 'Gunakan kode cadangan';
                updateTotpStatus();
                digits[0].focus();
            }
        });
    }

    // Backup Code Input Logic
    backupCodeInput.addEventListener('input', () => {
        const val = backupCodeInput.value.trim().toUpperCase();
        backupCodeInput.value = val;
        hiddenInput.value = val;
        submitBtn.disabled = val.length < 8;
    });

    let sec = 60;
    const tick = setInterval(() => {
        sec--;
        if (timerTxt) timerTxt.textContent = sec > 0 ? `(${sec}s)` : '';
        if (sec <= 0) { 
            clearInterval(tick); 
            if(resendBtn) resendBtn.disabled = false; 
        }
    }, 1000);

    const updateTotpStatus = () => {
        const code = Array.from(digits).map(d => d.value).join('');
        hiddenInput.value = code;
        if (progressBar) progressBar.style.width = (code.length / 6 * 100) + '%';
        submitBtn.disabled = code.length < 6;
    };

    digits.forEach((d, i) => {
        d.addEventListener('input', e => {
            d.value = e.target.value.replace(/[^a-zA-Z0-9]/g,'').slice(-1);
            if (d.value && i < 5) digits[i+1].focus();
            updateTotpStatus();
        });
        d.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !d.value && i > 0) {
                digits[i-1].focus();
            }
            if (e.key === 'ArrowLeft' && i > 0) digits[i-1].focus();
            if (e.key === 'ArrowRight' && i < 5) digits[i+1].focus();
        });
        d.addEventListener('focus', () => d.select());
        d.addEventListener('paste', e => {
            e.preventDefault();
            const data = e.clipboardData.getData('text').trim().split('');
            data.forEach((c, j) => { if (digits[i+j]) digits[i+j].value = c; });
            updateTotpStatus();
            if (digits[Math.min(i + data.length, 5)]) {
                digits[Math.min(i + data.length, 5)].focus();
            }
        });
    });

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        btnText.textContent = 'Memverifikasi...';
    });

    if (digits.length > 0) digits[0].focus();
});
</script>
@endpush