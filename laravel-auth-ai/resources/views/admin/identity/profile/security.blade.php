@extends('identity::profile.layout', ['title' => 'Keamanan & Sandi'])

@section('profile-content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
    
    <!-- CHANGE PASSWORD SECTION -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
            <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-key text-violet-500 text-sm"></i>
                Ubah Kata Sandi
            </h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Gunakan kata sandi yang kuat dan unik untuk melindungi akun Anda.</p>
        </div>
        <div class="p-8">
            <form action="{{ route('dashboard.profile.password') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider ml-1">Kata Sandi Saat Ini</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400 text-[10px] group-focus-within:text-violet-500 transition-colors"></i>
                        </div>
                        <input type="password" name="current_password" placeholder="••••••••"
                            class="w-full h-11 pl-10 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                    </div>
                    @error('current_password') <p class="text-[10px] text-red-500 ml-1 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider ml-1">Kata Sandi Baru</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-shield-halved text-slate-400 text-[10px] group-focus-within:text-violet-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" placeholder="••••••••"
                                class="w-full h-11 pl-10 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                        </div>
                        @error('password') <p class="text-[10px] text-red-500 ml-1 mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider ml-1">Konfirmasi Sandi</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-check-double text-slate-400 text-[10px] group-focus-within:text-violet-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password_confirmation" placeholder="••••••••"
                                class="w-full h-11 pl-10 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="confirmResetSandi()" class="h-10 px-6 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 text-[11px] font-bold transition-all active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-envelope-open-text text-[10px]"></i>
                        Lupa Sandi
                    </button>
                    <button type="submit" class="h-10 px-6 rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-[11px] font-semibold transition-all shadow-sm active:scale-[0.98] flex items-center gap-2">
                        <i class="fa-solid fa-save text-[10px]"></i>
                        Ganti Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MFA SECTION -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-shield-virus text-violet-500 text-sm"></i>
                    Aplikasi Authenticator (TOTP)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">Amankan akun Anda dengan lapisan keamanan tambahan.</p>
            </div>
            @if(Auth::user()->mfa_enabled && Auth::user()->mfa_type === 'totp')
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-200 dark:border-emerald-800/50">Aktif</span>
            @endif
        </div>
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-16 h-16 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <i class="fa-solid fa-mobile-screen-button text-2xl"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Autentikasi Dua Faktor</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Lindungi akun Anda dengan kode verifikasi dari aplikasi seperti <strong>Google Authenticator</strong> atau <strong>Authy</strong>. Ini mencegah akses tidak sah meskipun kata sandi Anda diketahui orang lain.
                    </p>
                </div>
                <div class="shrink-0">
                    @if(Auth::user()->mfa_enabled && Auth::user()->mfa_type === 'totp')
                        <button type="button" onclick="openDisableMfaModal()" class="h-10 px-6 rounded-lg border border-red-200 dark:border-red-900/30 text-red-600 dark:text-red-400 text-[11px] font-bold hover:bg-red-50 dark:hover:bg-red-900/10 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                            Matikan MFA
                        </button>
                    @else
                        <button type="button" onclick="startMfaSetup()" class="h-10 px-6 rounded-lg bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-[11px] font-semibold transition-all shadow-sm active:scale-[0.98] flex items-center gap-2">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            Aktifkan Sekarang
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORMS & MODALS (From original profile.blade.php) -->
<form id="reset-link-form" action="{{ route('dashboard.profile.password.reset_request') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- MFA SETUP MODAL -->
<div id="mfaSetupModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-6">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Setup Authenticator</h3>
                    <button onclick="closeMfaModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mfaStep1" class="space-y-8">
                    <div class="text-center space-y-6">
                        <div id="mfaQrPlaceholder" class="mx-auto w-48 h-48 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                            <div class="animate-spin h-8 w-8 border-2 border-slate-500 border-t-transparent rounded-full"></div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed px-4 font-medium">
                                Scan kode QR di atas atau masukkan kode rahasia secara manual untuk mulai mengamankan akun Anda.
                            </p>
                            <div class="py-3 px-5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 group cursor-pointer transition-all">
                                <p class="text-[9px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest mb-1">Manual Secret Key</p>
                                <code id="mfaSecretText" class="block text-sm font-mono font-bold text-slate-800 dark:text-white tracking-widest uppercase">--------</code>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Konfirmasi 6-Digit Kode</label>
                        <input type="text" id="mfaConfirmCode" placeholder="000 000" maxlength="6"
                            class="w-full h-12 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center font-mono text-2xl tracking-[0.5em] focus:border-slate-400 focus:ring-1 focus:ring-slate-400 outline-none transition-all">
                        <p id="mfaSetupError" class="hidden text-[10px] text-red-500 ml-1 mt-1 font-bold"></p>
                    </div>
                    <button onclick="confirmMfaSetup()" id="mfaConfirmBtn" class="w-full h-12 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold text-sm shadow-sm active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        Verifikasi & Aktifkan
                    </button>
                </div>

                <div id="mfaStep2" class="hidden space-y-8 animate-in fade-in zoom-in duration-500">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400 mx-auto mb-6 shadow-sm">
                            <i class="fa-solid fa-shield-check text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-white">Berhasil Diaktifkan!</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                            MFA sudah aktif. Harap simpan kode cadangan ini di tempat yang aman.
                        </p>
                    </div>
                    <div id="backupCodesList" class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm"></div>
                    <button onclick="window.location.reload()" class="w-full h-12 rounded-xl bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white font-semibold text-sm shadow-sm transition-all active:scale-[0.98]">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MFA DISABLE MODAL -->
<div id="mfaDisableModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-6">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="p-8">
                <div class="text-center space-y-5">
                    <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 rounded-xl flex items-center justify-center text-red-500 mx-auto mb-4 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Matikan MFA?</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed px-2 font-medium">
                        Tingkat keamanan akun Anda akan menurun drastis. Selesaikan konfirmasi dengan kata sandi Anda.
                    </p>
                </div>

                <div class="mt-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kata Sandi Anda</label>
                        <input type="password" id="mfaDisablePassword" placeholder="••••••••"
                            class="w-full h-12 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium focus:border-red-400 focus:ring-1 focus:ring-red-400 outline-none transition-all">
                        <p id="mfaDisableError" class="hidden text-[10px] text-red-500 ml-1 mt-1 font-bold"></p>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="closeDisableMfaModal()" class="flex-1 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            Batal
                        </button>
                        <button onclick="confirmDisableMfa()" id="mfaDisableConfirmBtn" class="flex-1 h-11 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-xs shadow-sm active:scale-[0.98] transition-all">
                            Ya, Matikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Copy-pasting logic from original profile.blade.php with minor tweaks
    const modal = document.getElementById('mfaSetupModal');
    const step1 = document.getElementById('mfaStep1');
    const step2 = document.getElementById('mfaStep2');
    const qrPlaceholder = document.getElementById('mfaQrPlaceholder');
    const secretText = document.getElementById('mfaSecretText');
    const confirmInput = document.getElementById('mfaConfirmCode');
    const confirmBtn = document.getElementById('mfaConfirmBtn');

    async function startMfaSetup() {
        modal.classList.remove('hidden');
        step1.classList.remove('hidden');
        step2.classList.add('hidden');
        
        try {
            const response = await fetch("{{ route('dashboard.profile.mfa.setup') }}");
            const data = await response.json();
            
            qrPlaceholder.innerHTML = data.qr_code;
            secretText.textContent = data.secret;
        } catch (error) {
            AppPopup.error({
                title: 'Gagal Setup',
                description: 'Gagal mengambil data setup MFA dari server.'
            });
        }
    }

    async function confirmMfaSetup() {
        const code = confirmInput.value;
        const errorEl = document.getElementById('mfaSetupError');

        if (code.length < 6) {
            errorEl.textContent = 'Masukkan 6 digit kode lengkap.';
            errorEl.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('mfaConfirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memvalidasi...';
        errorEl.classList.add('hidden');

        try {
            const response = await fetch("{{ route('dashboard.profile.mfa.confirm') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code })
            });

            const data = await response.json();

            if (response.ok) {
                step1.classList.add('hidden');
                step2.classList.remove('hidden');
                
                const list = document.getElementById('backupCodesList');
                list.innerHTML = data.backup_codes.map(c => `
                    <div class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400 py-1.5 px-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-100 dark:border-slate-700 text-center">
                        ${c}
                    </div>
                `).join('');
            } else {
                errorEl.textContent = data.message || 'Kode verifikasi salah.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Verifikasi & Aktifkan';
            }
        } catch (error) {
            errorEl.textContent = 'Terjadi kesalahan sistem.';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
        }
    }

    function closeMfaModal() {
        modal.classList.add('hidden');
    }

    function openDisableMfaModal() {
        document.getElementById('mfaDisableModal').classList.remove('hidden');
        document.getElementById('mfaDisablePassword').value = '';
        document.getElementById('mfaDisableError').classList.add('hidden');
    }

    function closeDisableMfaModal() {
        document.getElementById('mfaDisableModal').classList.add('hidden');
    }

    async function confirmDisableMfa() {
        const password = document.getElementById('mfaDisablePassword').value;
        const errorEl = document.getElementById('mfaDisableError');
        const btn = document.getElementById('mfaDisableConfirmBtn');

        if (!password) {
            errorEl.textContent = 'Kata sandi wajib diisi.';
            errorEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> ...';
        errorEl.classList.add('hidden');

        try {
            const response = await fetch("{{ route('dashboard.profile.mfa.disable') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ current_password: password })
            });

            const data = await response.json();

            if (response.ok) {
                window.location.reload();
            } else {
                errorEl.textContent = data.message || 'Gagal menonaktifkan MFA.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Ya, Matikan';
            }
        } catch (error) {
            errorEl.textContent = 'Terjadi kesalahan sistem.';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
        }
    }

    function confirmResetSandi() {
        AppPopup.confirm({
            title: 'Kirim Link Reset?',
            description: 'Link untuk mengatur ulang kata sandi akan dikirim ke email Anda. Pastikan Anda dapat mengakses email tersebut.',
            confirmText: 'Ya, Kirim Link',
            cancelText: 'Batal',
            onConfirm: () => {
                document.getElementById('reset-link-form').submit();
            }
        });
    }
</script>
@endpush
@endsection
