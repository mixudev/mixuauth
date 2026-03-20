<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60  shadow-2xl overflow-hidden modal-panel">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Detail Pengguna</h3>
            <button onclick="closeModal('detailModal')" class="w-8 h-8 flex items-center justify-center  text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center gap-4">
                <div id="detailAvatar" class="w-14 h-14  flex items-center justify-center text-2xl font-bold text-white flex-shrink-0 bg-gradient-to-br from-indigo-400 to-purple-500"></div>
                <div>
                    <p id="detailName" class="font-bold text-slate-800 dark:text-white text-base leading-none"></p>
                    <p id="detailEmail" class="text-xs text-slate-400 font-mono mt-1"></p>
                    <div id="detailStatusBadge" class="mt-2"></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3" id="detailGrid"></div>
            <div id="detailBlockInfo" class="hidden bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20  px-4 py-3">
                <p class="text-[10px] text-red-600 dark:text-red-400 font-semibold uppercase tracking-wider mb-1">Riwayat Blokir</p>
                <p id="detailBlockText" class="text-xs text-slate-600 dark:text-slate-300"></p>
                <p id="detailBlockReason" class="text-[10px] text-slate-400 font-mono mt-1"></p>
            </div>
        </div>
        <div class="px-6 pb-5 flex gap-2 border-t border-slate-100 dark:border-slate-800 pt-4">
            <button id="detailEditBtn" onclick="" class="flex-1 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white  transition-all">Edit User</button>
            <button id="detailBlockBtn" onclick="" class="flex-1 py-2 text-xs font-bold  border transition-all"></button>
        </div>
    </div>
</div>