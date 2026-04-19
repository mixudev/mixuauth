<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60  shadow-2xl overflow-hidden modal-panel">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-semibold text-slate-800 dark:text-white">Edit Pengguna</h3>
                <p id="editModalSub" class="text-[11px] text-slate-400 font-mono mt-0.5"></p>
            </div>
            <button onclick="closeModal('editModal')" class="w-8 h-8 flex items-center justify-center  text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <input type="hidden" id="editUserId"/>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="editName" type="text" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"/>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input id="editEmail" type="email" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all font-mono"/>
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password Baru <span class="text-slate-400">(kosongkan jika tidak diubah)</span></label>
                    <div class="relative">
                        <input id="editPassword" type="password" placeholder="Min. 8 karakter"
                            class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all pr-10 font-mono"/>
                        <button type="button" onclick="togglePassword('editPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status Akun</label>
                    <select id="editIsActive" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                        <option value="1">✓ Aktif</option>
                        <option value="0">✗ Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status Email</label>
                    <div id="editEmailVerifiedContainer" class="flex items-center mt-2.5">
                        <!-- Content injected via JS -->
                    </div>
                </div>
            </div>
            <div id="editError" class="hidden flex items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20  px-3 py-2.5">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p id="editErrorMsg" class="text-xs text-red-600 dark:text-red-400"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button onclick="sendResetPasswordFromEdit(this)" class="px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20  border border-amber-200 dark:border-amber-500/20 transition-all flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Reset Password
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="closeModal('editModal')" class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700  transition-all">Batal</button>
                <button onclick="submitEdit()" id="editSubmitBtn" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white  transition-all shadow-sm flex items-center gap-2">
                    <svg id="editSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
