<x-app-modal id="editModal" maxWidth="lg" title="Edit Pengguna" description="Perbarui informasi profil dan status akses akun pengguna ini." icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/></svg>" iconColor="indigo">
    <div class="space-y-4">
        <div id="editModalSub" class="text-[10px] font-mono text-gray-400 dark:text-slate-500 bg-gray-50 dark:bg-white/5 px-2 py-1 rounded inline-block mb-2"></div>
        <input type="hidden" id="editUserId"/>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label>Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="editName" type="text" placeholder="John Doe"/>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label>Email <span class="text-red-500">*</span></label>
                <input id="editEmail" type="email" placeholder="john@example.com" class="font-mono"/>
            </div>
            <div class="col-span-2">
                <label>Password Baru <span class="text-gray-400 capitalize">(kosongkan jika tidak diubah)</span></label>
                <div class="relative">
                    <input id="editPassword" type="password" placeholder="Min. 8 karakter" class="pr-10 font-mono"/>
                    <button type="button" onclick="togglePassword('editPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
            <div class="col-span-2">
                <label>Role / Hak Akses <span class="text-red-500">*</span></label>
                @include('identity::users.partials.role_select', ['id' => 'editRoles', 'roles' => $roles])
                <p class="text-[10px] text-gray-400 mt-1">Perubahan role akan segera berdampak pada hak akses pengguna.</p>
            </div>
            <div>
                <label>Status Akun</label>
                <select id="editIsActive" class="appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div>
                <label>Status Email</label>
                <div id="editEmailVerifiedContainer" class="flex items-center">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
        <div id="editError" class="hidden items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-3 py-2.5 rounded-sm transition-all">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p id="editErrorMsg" class="text-xs text-red-600 dark:text-red-400 font-medium"></p>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex items-center justify-between w-full">
            <button onclick="sendResetPasswordFromEdit(this)" class="px-4 py-2 text-[11px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 transition-all rounded-[10px] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Reset Password
            </button>
            <div class="flex items-center gap-3">
                <button onclick="AppModal.close('editModal')" class="modal-btn-cancel">Batal</button>
                <button onclick="submitEdit()" id="editSubmitBtn" class="modal-btn-primary">
                    <svg id="editSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </x-slot>
</x-app-modal>
