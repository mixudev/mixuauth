<div id="assignModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeAssignModal()"></div>
        
        <div class="relative bg-white dark:bg-slate-900 rounded shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all border border-slate-200 dark:border-slate-800">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Penetapan Role Masal</h3>
                    <p class="text-[10px] text-slate-400 font-mono mt-0.5"><span id="assign-user-count">0</span> user terpilih untuk diperbarui.</p>
                </div>
                <button onclick="closeAssignModal()" class="p-2 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <div class="space-y-6">
                    {{-- Role Selection --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">1. Pilih Role yang Akan Diterapkan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($roles as $role)
                            <label class="relative flex items-center gap-3 p-2.5 rounded border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-all group">
                                <input type="checkbox" name="assign_roles[]" value="{{ $role->id }}" class="assign-role-checkbox-modal w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800">
                                <div class="flex-1">
                                    <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $role->name }}</span>
                                    <span class="block text-[9px] text-slate-400 font-mono">{{ $role->slug }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Method Selection --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">2. Pilih Metode Pembaruan</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer group">
                                <input type="radio" name="assign_action_modal" value="sync" checked class="hidden peer">
                                <div class="text-center p-3 rounded border border-slate-100 dark:border-slate-800 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 transition-all">
                                    <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600">Sync</span>
                                    <span class="block text-[9px] text-slate-400 mt-1">Timpa semua role lama</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="assign_action_modal" value="attach" class="hidden peer">
                                <div class="text-center p-3 rounded border border-slate-100 dark:border-slate-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-500/10 transition-all">
                                    <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-200 group-hover:text-emerald-600">Attach</span>
                                    <span class="block text-[9px] text-slate-400 mt-1">Tambahkan role baru</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="assign_action_modal" value="detach" class="hidden peer">
                                <div class="text-center p-3 rounded border border-slate-100 dark:border-slate-800 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-500/10 transition-all">
                                    <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-200 group-hover:text-red-600">Detach</span>
                                    <span class="block text-[9px] text-slate-400 mt-1">Hapus role terpilih</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <button onclick="closeAssignModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">Batal</button>
                <button id="btn-submit-assign" onclick="submitAssignment()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <span>Terapkan Role</span>
                    <svg id="assign-spinner" class="animate-spin h-3 w-3 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
