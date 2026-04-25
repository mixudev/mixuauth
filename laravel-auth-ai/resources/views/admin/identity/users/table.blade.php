<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs min-w-[760px]">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 cursor-pointer focus:ring-indigo-500/30"/>
                    </th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Pengguna</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden md:table-cell">Email</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden lg:table-cell">IP Terakhir</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Status</th>
                    
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden lg:table-cell">Blokir</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $isBlocked  = $user->is_blocked;
                    $isActive   = $user->is_active && !$isBlocked;
                    $blockCount = $user->user_blocks_count ?? 0;
                    
                    // Format roles for JS
                    $userRoles = $user->roles->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug])->toArray();
                    
                    $detailData = array_merge(
                        $user->only(["id","name","email","is_active","last_login_ip","last_login_at","email_verified_at","created_at","mfa_enabled","mfa_type"]),
                        ['roles' => $userRoles]
                    );
                    
                    $editData = array_merge(
                        $user->only(["id","name","email","is_active","email_verified_at"]),
                        ['roles' => $userRoles]
                    );
                @endphp
                <tr
                    class="border-b border-slate-50 dark:border-slate-800/60 last:border-0 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group"
                    data-user-id="{{ $user->id }}"
                >
                    {{-- Checkbox --}}
                    <td class="px-4 py-3">
                        <input type="checkbox"
                            class="row-checkbox w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 cursor-pointer focus:ring-indigo-500/30"
                            value="{{ $user->id }}"
                            onchange="updateSelection()"
                        />
                    </td>

                    {{-- User --}}
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 {{ $isBlocked ? 'bg-red-400' : ($isActive ? 'bg-gradient-to-br from-indigo-400 to-purple-500' : 'bg-slate-400') }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-100 leading-none">{{ $user->name }}</p>
                                <p class="font-mono text-[9px] text-slate-400 mt-0.5">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td class="px-3 py-3 hidden md:table-cell">
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-slate-500 dark:text-slate-400 text-[11px]">{{ $user->email }}</span>
                            @if($user->email_verified_at)
                            <span title="Email terverifikasi">
                                <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </span>
                            @else
                            <span title="Email belum terverifikasi">
                                <svg class="w-3 h-3 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- IP --}}
                    <td class="px-3 py-3 hidden lg:table-cell">
                        <span class="font-mono text-slate-400 dark:text-slate-500 text-[11px]">{{ $user->last_login_ip ?? '—' }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="px-3 py-3">
                        @if($isBlocked)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span> BLOCKED
                        </span>
                        @elseif($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0 animate-pulse"></span> ACTIVE
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0"></span> INACTIVE
                        </span>
                        @endif
                    </td>



                    {{-- Block count --}}
                    <td class="px-3 py-3 hidden lg:table-cell">
                        @if($blockCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[24px] h-5 px-1.5 rounded text-[10px] font-bold tabular-nums {{ $blockCount >= 3 ? 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400' }}">
                            {{ $blockCount }}×
                        </span>
                        @else
                        <span class="text-slate-300 dark:text-slate-700">—</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">

                            {{-- Detail --}}
                            <button
                                onclick='openDetailModal(@json($detailData), {{ (int)$isBlocked }}, {{ $blockCount }}, @json($user->activeBlock?->reason))'
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-all"
                                title="Detail"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>

                            {{-- Edit --}}
                            <button
                                onclick='openEditModal(@json($editData))'
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/15 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
                                title="Edit"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            {{-- Block / Unblock --}}
                            @if($isBlocked)
                            <button
                                onclick="confirmUnblock({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/15 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all"
                                title="Unblokir"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                            @else
                            <button
                                onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-100 dark:hover:bg-red-500/15 hover:text-red-600 dark:hover:text-red-400 transition-all"
                                title="Blokir"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                            @endif

                            {{-- Reset Password --}}
                            <button
                                onclick="sendResetPassword({{ $user->id }}, '{{ addslashes($user->email) }}', this)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-amber-100 dark:hover:bg-amber-500/15 hover:text-amber-600 dark:hover:text-amber-400 transition-all"
                                title="Reset Password"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </button>

                            {{-- Kirim Verifikasi --}}
                            @if(!$user->email_verified_at)
                            <button
                                onclick="sendVerificationEmailAction({{ $user->id }}, '{{ addslashes($user->email) }}', this)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/15 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
                                title="Kirim Link Verifikasi"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </button>
                            @endif

                            {{-- Delete --}}
                            <button
                                onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-100 dark:hover:bg-red-500/15 hover:text-red-600 dark:hover:text-red-400 transition-all"
                                title="Hapus"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-sm text-slate-400 dark:text-slate-600">Tidak ada pengguna ditemukan</p>
                            <a href="{{ route('dashboard.users.index') }}" class="text-xs text-indigo-500 hover:underline">Reset filter</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 dark:border-slate-800">
        <p class="text-[11px] font-mono text-slate-400">
            Menampilkan <span class="text-slate-600 dark:text-slate-300">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
            dari <span class="text-slate-600 dark:text-slate-300">{{ $users->total() }}</span> pengguna
        </p>
        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($users->onFirstPage())
            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
            @else
            <a href="{{ $users->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            @endif

            {{-- Pages --}}
            @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}"
               class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition-all {{ $page == $users->currentPage() ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $page }}
            </a>
            @endforeach

            {{-- Next --}}
            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>