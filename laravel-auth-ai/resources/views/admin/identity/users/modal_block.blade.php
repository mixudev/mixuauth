<div id="blockModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60  shadow-2xl overflow-hidden modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Blokir Pengguna</h3>
            <p id="blockModalSub" class="text-[11px] text-slate-400 font-mono mt-0.5"></p>
        </div>
        <input type="hidden" id="blockUserId"/>
        <input type="hidden" id="blockUserName"/>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Alasan Blokir <span class="text-red-500">*</span></label>
                <select id="blockReason" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                    <option value="">-- Pilih alasan --</option>
                    <option value="Suspicious activity">Aktivitas mencurigakan</option>
                    <option value="Multiple failed logins">Terlalu banyak login gagal</option>
                    <option value="Violation of terms">Pelanggaran ketentuan layanan</option>
                    <option value="Security threat">Ancaman keamanan</option>
                    <option value="Fraudulent activity">Aktivitas penipuan</option>
                    <option value="Manual review">Review manual admin</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Blokir Sampai <span class="text-slate-400">(opsional)</span></label>
                <input id="blockUntil" type="datetime-local"
                    class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all font-mono"/>
                <p class="text-[10px] text-slate-400 mt-1">Kosongkan untuk blokir permanen</p>
            </div>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="closeModal('blockModal')" class="flex-1 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700  transition-all">Batal</button>
            <button onclick="submitBlock()" class="flex-1 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700  transition-all shadow-sm">Konfirmasi Blokir</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: BULK BLOCK
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="bulkBlockModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60  shadow-2xl overflow-hidden modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Blokir Semua yang Dipilih</h3>
            <p id="bulkBlockCount" class="text-[11px] text-slate-400 mt-0.5"></p>
        </div>
        <div class="px-6 py-4">
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Alasan Blokir <span class="text-red-500">*</span></label>
            <select id="bulkBlockReason" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700  text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                <option value="">-- Pilih alasan --</option>
                <option value="Suspicious activity">Aktivitas mencurigakan</option>
                <option value="Security threat">Ancaman keamanan</option>
                <option value="Manual review">Review manual admin</option>
            </select>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="closeModal('bulkBlockModal')" class="flex-1 py-2.5 text-xs font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700  transition-all">Batal</button>
            <button onclick="submitBulkBlock()" class="flex-1 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700  transition-all">Blokir Semua</button>
        </div>
    </div>
</div>