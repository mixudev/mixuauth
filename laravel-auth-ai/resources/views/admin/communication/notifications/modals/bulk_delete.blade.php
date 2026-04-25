<x-app-modal 
    id="bulkDeleteModal" 
    maxWidth="md" 
    title="Hapus Masal Notifikasi" 
    description="Pilih rentang waktu untuk menghapus data notifikasi secara permanen dari sistem." 
    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z'/></svg>" 
    iconColor="red"
>
    <div class="space-y-5">
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mulai Dari</label>
            <input type="datetime-local" id="bulk-start-date" class="w-full px-4 py-2 text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-slate-700 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all">
        </div>

        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Sampai Dengan</label>
            <input type="datetime-local" id="bulk-end-date" class="w-full px-4 py-2 text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-slate-700 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all">
        </div>

        <div class="p-4 bg-red-50/50 dark:bg-red-500/5 border border-red-100 dark:border-red-500/10 rounded">
            <p class="text-[10px] text-red-600 dark:text-red-400 leading-relaxed italic font-medium">
                <svg class="w-3.5 h-3.5 inline mr-1 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Peringatan: Tindakan ini permanen. Semua notifikasi dalam rentang waktu tersebut akan dihapus.
            </p>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3 w-full">
            <button onclick="AppModal.close('bulkDeleteModal')" class="modal-btn-cancel">Batal</button>
            <button id="btn-submit-bulk-delete" onclick="submitBulkDelete()" class="modal-btn-primary !bg-red-600 !hover:bg-red-700 !border-red-600">
                <span id="bulk-delete-text">Hapus Sekarang</span>
                <svg id="bulk-delete-spinner" class="animate-spin h-3 w-3 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </x-slot>
</x-app-modal>
