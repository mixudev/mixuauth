<div id="bulkBar" class="hidden items-center gap-3 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-sm px-4 py-3 mb-4">
    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span id="bulkCount" class="text-xs font-semibold text-indigo-700 dark:text-indigo-300"></span>
    <div class="flex items-center gap-2 ml-2">
        <button onclick="openBulkBlockModal()" class="px-3 py-1.5 text-xs font-semibold bg-red-100 dark:bg-red-500/15 hover:bg-red-200 dark:hover:bg-red-500/25 text-red-700 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-500/30 transition-all">
            Blokir Semua
        </button>
        <button onclick="bulkAction('unblock')" class="px-3 py-1.5 text-xs font-semibold bg-emerald-100 dark:bg-emerald-500/15 hover:bg-emerald-200 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-500/30 transition-all">
            Unblokir Semua
        </button>
        <button onclick="bulkAction('delete')" class="px-3 py-1.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
            Hapus Semua
        </button>
    </div>
    <button onclick="clearSelection()" class="ml-auto p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
