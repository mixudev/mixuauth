<div id="roleModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeRoleModal()"></div>
        
        <div class="relative bg-white dark:bg-slate-900 rounded shadow-2xl w-full max-w-4xl overflow-hidden transform transition-all border border-slate-200 dark:border-slate-800">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div>
                    <h3 id="roleModalTitle" class="text-base font-bold text-slate-800 dark:text-slate-100">Tambah Role Baru</h3>
                    <p id="roleModalSub" class="text-[10px] text-slate-400 font-mono mt-0.5">Konfigurasi hak akses level grup.</p>
                </div>
                <button onclick="closeRoleModal()" class="p-2 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form --}}
            <div class="p-6">
                <input type="hidden" id="role-id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Role</label>
                        <input type="text" id="role-name" placeholder="Contoh: Manager Operasional" class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Deskripsi Singkat</label>
                        <input type="text" id="role-description" placeholder="Berikan penjelasan fungsi role ini..." class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Izin (Permissions)</label>
                        <div class="flex gap-4">
                            <button onclick="toggleAllPermissions(true)" class="text-[10px] font-bold text-indigo-600 hover:underline">Pilih Semua</button>
                            <button onclick="toggleAllPermissions(false)" class="text-[10px] font-bold text-slate-400 hover:underline">Hapus Semua</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($permissions as $group => $perms)
                        <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-4 bg-slate-50/30 dark:bg-slate-800/20">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                                <h4 class="text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">{{ ucfirst($group) }}</h4>
                                <input type="checkbox" class="group-toggle w-3.5 h-3.5 rounded text-indigo-600" data-group="{{ $group }}" onclick="toggleGroup(this)">
                            </div>
                            <div class="space-y-2">
                                @foreach($perms as $perm)
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-checkbox w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 dark:border-slate-700 dark:bg-slate-900" data-group="{{ $group }}">
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors">{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <button onclick="closeRoleModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">Batal</button>
                <button id="btn-save-role" onclick="saveRole()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <span id="role-btn-text">Simpan Role</span>
                    <svg id="role-spinner" class="animate-spin h-3 w-3 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
