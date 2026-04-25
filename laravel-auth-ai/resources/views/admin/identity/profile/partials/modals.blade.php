<!-- ════ HIDDEN: MFA Setup Modal ════ -->
<div id="mfaSetupModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-6">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Setup Authenticator</h3>
                    <button onclick="closeMfaModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div id="mfaStep1" class="space-y-6">
                    <div class="text-center space-y-5">
                        <div id="mfaQrPlaceholder" class="mx-auto w-48 h-48 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="animate-spin h-7 w-7 border-2 border-slate-400 border-t-transparent rounded-full"></div>
                        </div>
                        <div class="py-3 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mb-1">Manual Secret Key</p>
                            <code id="mfaSecretText" class="block text-sm font-mono font-bold text-slate-800 dark:text-white tracking-widest uppercase">--------</code>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Konfirmasi 6-Digit Kode</label>
                        <input type="text" id="mfaConfirmCode" placeholder="xxxxxx" maxlength="6"
                            class="w-full h-12 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center font-mono text-2xl tracking-[0.5em] focus:border-slate-400 focus:ring-1 focus:ring-slate-400 outline-none transition-all">
                        <p id="mfaSetupError" class="hidden text-[10px] text-red-500 ml-1 font-bold"></p>
                    </div>
                    <button onclick="confirmMfaSetup()" id="mfaConfirmBtn"
                        class="w-full h-11 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                        Verifikasi & Aktifkan
                    </button>
                </div>

                <div id="mfaStep2" class="hidden space-y-6">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-600 mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                <path d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Berhasil Diaktifkan!</h4>
                        <p class="text-xs text-slate-500 mt-2">Simpan kode cadangan ini di tempat yang aman. Kode cadangan juga telah dikirim ke email Anda.</p>
                    </div>
                    <div id="backupCodesList" class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-200 dark:border-slate-700"></div>
                    <button onclick="window.location.reload()" class="w-full h-11 rounded-xl bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white font-semibold text-sm transition-all">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for password reset --}}
<form id="reset-link-form" action="{{ route('dashboard.profile.password.reset_request') }}" method="POST" class="hidden">@csrf</form>
