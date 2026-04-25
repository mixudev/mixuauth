@extends('layouts.auth')

@section('title', 'Reset Password')
@section('auth_title', 'Reset Password')
@section('auth_subtitle', 'Buat password baru yang kuat dan mudah Anda ingat.')
@section('show_ai_banner', true)

@section('auth_content')

    {{-- Email error --}}
    @error('email')
        <div class="alert alert-error">
            {{ $message }}
        </div>
    @enderror

    {{-- Form --}}
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        {{-- Password baru --}}
        <div class="form-group">
            <label for="password" class="form-label">Password Baru</label>
            <div class="pwd-wrap">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    placeholder="••••••••••••"
                    class="form-input @error('password') is-error @enderror"
                >
                <button type="button" class="pwd-toggle" onclick="togglePwd('password')">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268-2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password strength bar --}}
        <div style="margin-bottom: 24px;">
            <div style="display: flex; gap: 4px; margin-bottom: 8px;">
                @for($i = 0; $i < 4; $i++)
                <div style="height: 3px; flex: 1; background: #F3F4F6; border-radius: 99px; overflow: hidden;">
                    <div class="strength-fill" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                </div>
                @endfor
            </div>
            <p id="strengthLabel" style="font-size: 10px; color: #9CA3AF; font-family: 'Syne', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;">
                Masukkan password
            </p>
        </div>

        {{-- Konfirmasi password --}}
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <div class="pwd-wrap">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    placeholder="••••••••••••"
                    class="form-input"
                >
                <button type="button" class="pwd-toggle" onclick="togglePwd('password_confirmation')">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268-2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <p id="matchLabel" style="font-size: 10px; margin-top: 6px; font-family: 'Syne', sans-serif;"></p>
        </div>

        {{-- Submit --}}
        <div class="btn-submit-wrap">
            <button type="submit" class="btn-submit">
                Simpan Password Baru
            </button>
        </div>

    </form>

@endsection

@push('scripts')
<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

(function () {
    const pwInput   = document.getElementById('password');
    const cfInput   = document.getElementById('password_confirmation');
    const fills     = document.querySelectorAll('.strength-fill');
    const label     = document.getElementById('strengthLabel');
    const matchLbl  = document.getElementById('matchLabel');

    const levels = [
        { label: 'Sangat lemah', color: '#ef4444' },
        { label: 'Lemah',        color: '#f59e0b' },
        { label: 'Cukup kuat',   color: '#3b82f6' },
        { label: 'Sangat kuat',  color: '#22c55e' },
    ];

    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
        if (/\d/.test(pw))   score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return Math.min(Math.ceil(score / 1.25), 4);
    }

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        if (!val) {
            fills.forEach(f => f.style.width = '0%');
            label.textContent = 'Masukkan password';
            label.style.color = '#9CA3AF';
            return;
        }
        const s = getStrength(val);
        const lv = levels[s - 1];
        fills.forEach((f, i) => {
            f.style.width = i < s ? '100%' : '0%';
            f.style.background = i < s ? lv.color : '#F3F4F6';
        });
        label.textContent = lv.label;
        label.style.color = lv.color;
        checkMatch();
    });

    function checkMatch() {
        const pw = pwInput.value;
        const cf = cfInput.value;
        if (!cf) { matchLbl.textContent = ''; return; }
        if (pw === cf) {
            matchLbl.textContent = 'Password cocok';
            matchLbl.style.color = '#22c55e';
        } else {
            matchLbl.textContent = 'Password tidak cocok';
            matchLbl.style.color = '#ef4444';
        }
    }

    cfInput.addEventListener('input', checkMatch);
})();
</script>
@endpush