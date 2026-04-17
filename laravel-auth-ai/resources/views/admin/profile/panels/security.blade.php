@extends('admin.profile.layout')

@section('profile-content')
<div class="space-y-5">

    @if(session('success'))
        <div class="px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800/50 flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
            <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif

    <!-- UBAH KATA SANDI -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-key text-slate-400 text-xs"></i>
                Ubah Kata Sandi
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Gunakan kata sandi yang kuat dan unik.</p>
        </div>
        <div class="p-8">
            <form action="{{ route('dashboard.profile.password') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kata Sandi Saat Ini</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400 text-[10px]"></i>
                        </div>
                        <input type="password" name="current_password" placeholder="••••••••"
                            class="w-full h-11 pl-9 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                    </div>
                    @error('current_password') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fa-solid fa-shield-halved text-slate-400 text-[10px]"></i>
                            </div>
                            <input type="password" name="password" placeholder="••••••••"
                                class="w-full h-11 pl-9 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                        </div>
                        @error('password') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Konfirmasi Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fa-solid fa-check-double text-slate-400 text-[10px]"></i>
                            </div>
                            <input type="password" name="password_confirmation" placeholder="••••••••"
                                class="w-full h-11 pl-9 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="confirmResetSandi()"
                        class="h-10 px-5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold transition-all flex items-center gap-2">
                        <i class="fa-solid fa-envelope-open-text text-[10px]"></i>
                        Lupa Sandi
                    </button>
                    <button type="submit"
                        class="h-10 px-6 rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-save text-[10px]"></i>
                        Ganti Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MFA / TOTP -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-shield-virus text-slate-400 text-xs"></i>
                    Autentikasi Dua Faktor (TOTP)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Amankan akun dengan lapisan verifikasi tambahan.</p>
            </div>
            @if(Auth::user()->mfa_enabled && Auth::user()->mfa_type === 'totp')
                <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider border border-emerald-200 dark:border-emerald-800/50">Aktif</span>
            @endif
        </div>
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="w-14 h-14 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0 shadow-sm">
                    <i class="fa-solid fa-mobile-screen-button text-xl"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-sm font-semibold text-slate-800 dark:text-white">Aplikasi Authenticator</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                        Gunakan <strong>Google Authenticator</strong> atau <strong>Authy</strong> untuk membuat kode 6 digit yang diperbarui setiap 30 detik.
                    </p>
                </div>
                <div class="shrink-0">
                    @if(Auth::user()->mfa_enabled && Auth::user()->mfa_type === 'totp')
                        <button type="button" onclick="openDisableMfaModal()"
                            class="h-10 px-5 rounded-lg border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-semibold hover:bg-red-50 dark:hover:bg-red-900/10 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                            Matikan MFA
                        </button>
                    @else
                        <button type="button" onclick="startMfaSetup()"
                            class="h-10 px-5 rounded-lg bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            Siapkan Aplikasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
