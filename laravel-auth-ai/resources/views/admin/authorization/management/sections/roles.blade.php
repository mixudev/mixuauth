{{-- STAT CARDS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Role</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ $stats['total_roles'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Izin</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ $stats['total_permissions'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pengguna</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ $stats['total_users'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
        Manajemen Role
        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[9px] text-slate-500 font-mono">{{ $roles->count() }}</span>
    </h2>
    <a href="{{ route('dashboard.access-management.roles.create') }}" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Role
    </a>
</div>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Identitas Role</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Deskripsi</th>
                    <th class="px-5 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Izin & User</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($roles as $role)
                @php $isSystem = in_array($role->slug, ['super-admin', 'admin', 'user', 'security-officer']); @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 font-bold text-xs">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-[11px] text-slate-800 dark:text-slate-200">{{ $role->name }}</span>
                                    @if($isSystem)
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-amber-50 text-amber-600 border border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">System</span>
                                    @endif
                                </div>
                                <div class="text-[9px] font-mono text-slate-400">{{ $role->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 max-w-xs">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">
                            {{ $role->description ?? 'Tidak ada deskripsi.' }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $role->permissions_count }}</span>
                                <span class="text-[9px] uppercase font-semibold text-slate-400">Izin</span>
                            </div>
                            <div class="w-px h-3 bg-slate-100 dark:bg-slate-800"></div>
                            <div class="text-center">
                                <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $role->users_count }}</span>
                                <span class="text-[9px] uppercase font-semibold text-slate-400">User</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a 
                                href="{{ route('dashboard.access-management.roles.edit', $role->id) }}"
                                class="p-1.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if(!$isSystem)
                            <button 
                                onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')"
                                class="p-1.5 rounded bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
