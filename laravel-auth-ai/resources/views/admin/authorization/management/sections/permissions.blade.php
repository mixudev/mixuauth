<div class="flex items-center justify-between mb-4">
    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
        Katalog Izin Granular
        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[9px] text-slate-500 font-mono">{{ $stats['total_permissions'] }}</span>
    </h2>
    <div class="text-[9px] text-slate-400 italic">
        * Daftar ini bersifat fixed (read-only) untuk keamanan sistem.
    </div>
</div>

<div class="space-y-4">
    @foreach($permissions as $group => $perms)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
        <div class="bg-slate-50/50 dark:bg-slate-800/50 px-5 py-2.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest flex items-center gap-2">
                <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Modul: {{ ucfirst($group) }}
            </h3>
            <span class="text-[9px] font-bold text-slate-400 font-mono">{{ $perms->count() }} Izin</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/20 dark:bg-slate-800/20 text-[9px] text-slate-500 uppercase font-bold border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-2 text-left w-1/3">Permission Key</th>
                        <th class="px-5 py-2 text-left">Deskripsi Fungsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($perms as $perm)
                    <tr class="hover:bg-slate-50/10 dark:hover:bg-slate-800/10 transition-colors">
                        <td class="px-5 py-2.5">
                            <span class="font-mono text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-500/5 px-1.5 py-0.5 rounded border border-indigo-100/50 dark:border-indigo-500/10">
                                {{ $perm->name }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $perm->description ?? 'Deskripsi tidak tersedia.' }}
                            </p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
