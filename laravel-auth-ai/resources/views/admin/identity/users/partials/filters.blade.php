<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-sm p-4 mb-4">
    <form method="GET" action="{{ route('dashboard.users.index') }}" id="filterForm">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari nama, email, atau IP..."
                    class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"
                    oninput="debounceSubmit()"
                />
            </div>

            {{-- Status filter pills --}}
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                @foreach(['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif', 'blocked' => 'Diblokir', 'deleted' => 'Dihapus'] as $val => $label)
                <button
                    type="button"
                    onclick="setStatus('{{ $val }}')"
                    class="status-btn px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 {{ ($filters['status'] ?? 'all') === $val ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                    data-status="{{ $val }}"
                >{{ $label }}</button>
                @endforeach
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ $filters['status'] ?? 'all' }}"/>

            {{-- Sort --}}
            <select
                name="sort"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                <option value="-created_at"   {{ ($filters['sort'] ?? '-created_at') === '-created_at'   ? 'selected' : '' }}>Terdaftar Terbaru</option>
                <option value="created_at"    {{ ($filters['sort'] ?? '') === 'created_at'    ? 'selected' : '' }}>Terdaftar Lama</option>
                <option value="name"          {{ ($filters['sort'] ?? '') === 'name'          ? 'selected' : '' }}>Nama A → Z</option>
                <option value="-name"         {{ ($filters['sort'] ?? '') === '-name'         ? 'selected' : '' }}>Nama Z → A</option>
                <option value="-last_login_at"{{ ($filters['sort'] ?? '') === '-last_login_at'? 'selected' : '' }}>Login Terbaru</option>
                <option value="block_count"   {{ ($filters['sort'] ?? '') === 'block_count'   ? 'selected' : '' }}>Blokir Terbanyak</option>
            </select>

            {{-- Per page --}}
            <select
                name="per_page"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                @foreach([10, 25, 50, 100] as $pp)
                <option value="{{ $pp }}" {{ (int)($filters['per_page'] ?? 15) === $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
                @endforeach
            </select>

            {{-- Reset filter --}}
            @if(!empty($filters['search']) || !empty($filters['status']) && $filters['status'] !== 'all')
            <a href="{{ route('dashboard.users.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Reset</a>
            @endif
        </div>
    </form>
</div>
