{{-- TOOLBAR & FILTERS --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pilih User untuk Penugasan Role</h2>
        <div id="selection-counter" class="hidden px-2 py-0.5 rounded bg-indigo-600 text-[10px] text-white font-bold">
            0 Terpilih
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('dashboard.access-management.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="assignment">
            <div class="relative">
                <input 
                    type="text" 
                    name="user_search"
                    placeholder="Cari user..."
                    value="{{ $userFilters['user_search'] ?? '' }}"
                    class="pl-8 pr-4 py-1.5 text-[11px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded text-slate-700 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all"
                >
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold rounded hover:bg-slate-200 transition-colors">
                Filter
            </button>
        </form>

        <button 
            id="btn-open-assign-modal"
            onclick="openAssignModal()" 
            disabled
            class="px-4 py-1.5 bg-indigo-600 text-white text-[11px] font-bold rounded opacity-50 cursor-not-allowed transition-all flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Assign Role
        </button>
    </div>
</div>

{{-- USER TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left w-10">
                        <input type="checkbox" id="check-all-users" onchange="toggleAllUsers(this)" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Informasi User</th>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Role Saat Ini</th>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                    <td class="px-5 py-4">
                        <input type="checkbox" value="{{ $user->id }}" onchange="toggleUserSelection(this)" class="user-checkbox w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded border border-slate-100 dark:border-slate-800">
                            <div>
                                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200">{{ $user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($user->roles as $role)
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[9px] font-bold text-slate-600 dark:text-slate-400">
                                {{ $role->name }}
                            </span>
                            @empty
                            <span class="text-[10px] text-slate-400 italic">No role</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">Active</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-red-50 text-red-600 border border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center text-slate-400 text-xs italic">
                        Tidak ada user ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $users->links() }}
</div>
