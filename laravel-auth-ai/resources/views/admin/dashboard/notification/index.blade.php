@extends('layouts.app-dashboard')

@section('title', 'Security Notifications')
@section('page-title', 'Notifications')
@section('page-sub', 'Pantau semua aktivitas keamanan dan peringatan sistem')

@section('content')

{{-- ============================================================
     PAGE HEADER
     ============================================================ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2.5 mb-1">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 tracking-tight">Security Notifications</h1>
            @if($stats['unread'] > 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                    {{ $stats['unread'] }} baru
                </span>
            @endif
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 ml-10.5">Daftar lengkap peringatan & aktivitas mencurigakan sistem</p>
    </div>
    <div class="flex items-center gap-2 ml-10.5 sm:ml-0">
        <button
            id="btn-mark-all"
            onclick="markAllAsRead()"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/70 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed"
            {{ $stats['unread'] == 0 ? 'disabled' : '' }}
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tandai Semua Dibaca
        </button>
    </div>
</div>

{{-- ============================================================
     STATS CARDS
     ============================================================ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total --}}
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-5 overflow-hidden group hover:border-slate-300 dark:hover:border-slate-600 transition-colors">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-transparent dark:from-slate-800/30 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.1em]">Total</p>
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($stats['total']) }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">semua notifikasi</p>
        </div>
    </div>

    {{-- Unread --}}
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-5 overflow-hidden group hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-colors">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-indigo-500 rounded-t-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-[0.1em]">Belum Dibaca</p>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums">{{ number_format($stats['unread']) }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">perlu ditinjau</p>
        </div>
    </div>

    {{-- Warning --}}
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-5 overflow-hidden group hover:border-amber-300 dark:hover:border-amber-500/40 transition-colors">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-amber-500 rounded-t-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-amber-500 uppercase tracking-[0.1em]">Warning</p>
                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($stats['warning']) }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">peringatan aktif</p>
        </div>
    </div>

    {{-- Success / Resolved (Optional replacement for some Error slot or extra slot) --}}
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-5 overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-500/40 transition-colors">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-t-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-[0.1em]">Success / Info</p>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($stats['total'] - $stats['unread']) }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">sudah diselesaikan</p>
        </div>
    </div>
</div>

{{-- ============================================================
     FILTER BAR
     ============================================================ --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-4 mb-5">
    <form method="GET" action="{{ route('dashboard.notifications.all') }}" class="flex flex-wrap items-center gap-3">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[220px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari judul, pesan, atau IP address…"
                class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition"
            >
        </div>

        {{-- Type Filter --}}
        <div class="flex items-center gap-2">
            @php
                $types = [
                    '' => ['label' => 'Semua', 'color' => 'slate'],
                    'info' => ['label' => 'Info', 'color' => 'blue'],
                    'warning' => ['label' => 'Warning', 'color' => 'amber'],
                    'error' => ['label' => 'Error', 'color' => 'red'],
                    'success' => ['label' => 'Success', 'color' => 'emerald'],
                ];
                $colorMap = [
                    'slate'    => 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-slate-400 dark:hover:border-slate-500',
                    'blue'     => 'border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 hover:border-blue-400',
                    'amber'    => 'border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 hover:border-amber-400',
                    'red'      => 'border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:border-red-400',
                    'emerald'  => 'border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 hover:border-emerald-400',
                ];
                $activeMap = [
                    'slate'    => 'bg-slate-800 dark:bg-slate-100 border-slate-800 dark:border-slate-100 text-white dark:text-slate-800',
                    'blue'     => 'bg-blue-600 border-blue-600 text-white',
                    'amber'    => 'bg-amber-500 border-amber-500 text-white',
                    'red'      => 'bg-red-500 border-red-500 text-white',
                    'emerald'  => 'bg-emerald-600 border-emerald-600 text-white',
                ];
            @endphp

            @foreach($types as $val => $meta)
                @php $isActive = request('type', '') === $val; @endphp
                <button
                    type="submit"
                    name="type"
                    value="{{ $val }}"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all duration-150 {{ $isActive ? $activeMap[$meta['color']] : 'bg-transparent ' . $colorMap[$meta['color']] }}"
                >
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Read Status Filter --}}
        <select
            name="read"
            onchange="this.form.submit()"
            class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition"
        >
            <option value="">Semua Status</option>
            <option value="unread" {{ request('read') == 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
            <option value="read" {{ request('read') == 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
        </select>

        {{-- Search Submit --}}
        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors">
            Cari
        </button>

        {{-- Reset --}}
        @if(request()->hasAny(['search', 'type', 'read']))
            <a
                href="{{ route('dashboard.notifications.all') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
        @endif

    </form>
</div>

{{-- ============================================================
     NOTIFICATIONS TABLE
     ============================================================ --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl overflow-hidden">

    {{-- Table Header --}}
    <div class="hidden md:grid grid-cols-12 gap-4 px-5 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
        <div class="col-span-1">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Tipe</span>
        </div>
        <div class="col-span-6">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Notifikasi</span>
        </div>
        <div class="col-span-2">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">IP Address</span>
        </div>
        <div class="col-span-2">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Waktu</span>
        </div>
        <div class="col-span-1 text-right">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Aksi</span>
        </div>
    </div>

    {{-- Notification Rows --}}
    <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
        @forelse($notifications as $notif)

            @php
                $typeConfig = match($notif->type) {
                    'error'   => ['icon_bg' => 'bg-red-100 dark:bg-red-500/10', 'icon_color' => 'text-red-600 dark:text-red-400', 'badge' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/30', 'label' => 'Error', 'dot' => 'bg-red-500'],
                    'warning' => ['icon_bg' => 'bg-amber-100 dark:bg-amber-500/10', 'icon_color' => 'text-amber-600 dark:text-amber-400', 'badge' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30', 'label' => 'Warning', 'dot' => 'bg-amber-500'],
                    'success' => ['icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/10', 'icon_color' => 'text-emerald-600 dark:text-emerald-400', 'badge' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30', 'label' => 'Success', 'dot' => 'bg-emerald-500'],
                    default   => ['icon_bg' => 'bg-blue-100 dark:bg-blue-500/10', 'icon_color' => 'text-blue-600 dark:text-blue-400', 'badge' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/30', 'label' => 'Info', 'dot' => 'bg-blue-500'],
                };
            @endphp

            <div
                id="notif-row-{{ $notif->id }}"
                class="group relative flex md:grid md:grid-cols-12 md:gap-4 items-start md:items-center px-5 py-4 transition-colors duration-150 {{ !$notif->is_read ? 'bg-indigo-50/40 dark:bg-indigo-500/[0.04]' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/40' }}"
            >
                {{-- Unread indicator stripe --}}
                @if(!$notif->is_read)
                    <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-indigo-500 rounded-r"></div>
                @endif

                {{-- Type Badge (col 1) --}}
                <div class="col-span-1 hidden md:flex items-center">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold border {{ $typeConfig['badge'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $typeConfig['dot'] }}"></span>
                        {{ $typeConfig['label'] }}
                    </span>
                </div>

                {{-- Mobile: icon + content stacked --}}
                <div class="flex gap-3 flex-1 min-w-0 md:contents">
                    {{-- Icon (mobile only) --}}
                    <div class="md:hidden shrink-0 mt-0.5">
                        <div class="w-9 h-9 rounded-xl {{ $typeConfig['icon_bg'] }} {{ $typeConfig['icon_color'] }} flex items-center justify-center">
                            @if($notif->type == 'error')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($notif->type == 'warning')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @elseif($notif->type == 'success')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                    </div>

                    {{-- Content (col 6) --}}
                    <div class="col-span-6 flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            @if($notif->event)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[9px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 font-mono">
                                    {{ $notif->event }}
                                </span>
                            @endif
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $notif->title }}</h3>
                            @if(!$notif->is_read)
                                <span class="notif-unread-dot shrink-0 w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 italic mb-1">{{ $notif->message }}</p>
                        {{-- Mobile meta --}}
                        <div class="md:hidden flex items-center gap-3 mt-1.5 text-[10px] text-slate-400">
                            @if($notif->ip_address)
                                <span class="font-mono">{{ $notif->ip_address }}</span>
                                <span>·</span>
                            @endif
                            <span>@humanstime($notif->created_at)</span>
                        </div>
                    </div>

                    {{-- IP Address (col 2) --}}
                    <div class="col-span-2 hidden md:flex items-center">
                        @if($notif->ip_address)
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                                </svg>
                                {{ $notif->ip_address }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </div>

                    {{-- Time (col 2) --}}
                    <div class="col-span-2 hidden md:flex items-center">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">@humanstime($notif->created_at)</p>
                            <p class="text-[10px] text-slate-300 dark:text-slate-600 mt-0.5">{{ $notif->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Actions (col 1) --}}
                <div class="col-span-1 flex items-center justify-end gap-1 shrink-0 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-150 mt-0 ml-2 md:ml-0">
                    @if(!$notif->is_read)
                        <button
                            onclick="markAsRead({{ $notif->id }})"
                            title="Tandai dibaca"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    @endif
                    <button
                        onclick="deleteNotif({{ $notif->id }})"
                        title="Hapus notifikasi"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

            </div>

        @empty
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-24 px-4 text-center">
                <div class="w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 flex items-center justify-center mb-5">
                    <svg class="w-9 h-9 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                    @if(request()->hasAny(['search', 'type', 'read']))
                        Tidak ada hasil yang cocok
                    @else
                        Tidak ada notifikasi
                    @endif
                </h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 max-w-xs">
                    @if(request()->hasAny(['search', 'type', 'read']))
                        Coba ubah filter pencarian atau <a href="{{ route('dashboard.notifications.all') }}" class="text-indigo-500 hover:underline">reset filter</a>.
                    @else
                        Sistem keamanan berjalan optimal. Tidak ada aktivitas mencurigakan terdeteksi.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <p class="text-xs text-slate-400 dark:text-slate-500">
                Menampilkan
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $notifications->firstItem() }}</span>
                –
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $notifications->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($notifications->total()) }}</span>
                notifikasi
            </p>
            <div class="text-sm">
                {{ $notifications->links() }}
            </div>
        </div>
    @endif

</div>

{{-- ============================================================
     JAVASCRIPT
     ============================================================ --}}
<script>
    const CSRF = '{{ csrf_token() }}';

    /**
     * Mark a single notification as read via API.
     */
    async function markAsRead(id) {
        try {
            const resp = await fetch(`/dashboard/api/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const res = await resp.json();

            if (res.success) {
                const row = document.getElementById(`notif-row-${id}`);
                if (!row) return;

                // Remove unread background
                row.classList.remove('bg-indigo-50/40', 'dark:bg-indigo-500/[0.04]');
                row.classList.add('hover:bg-slate-50/70', 'dark:hover:bg-slate-800/40');

                // Remove unread stripe
                row.querySelector('.absolute.left-0')?.remove();

                // Remove unread dot
                row.querySelector('.notif-unread-dot')?.remove();

                // Remove mark-as-read button
                row.querySelector('button[title="Tandai dibaca"]')?.remove();

                AppPopup?.success({ description: 'Notifikasi ditandai telah dibaca.' });
            }
        } catch (err) {
            console.error('[markAsRead]', err);
            AppPopup?.error({ description: 'Gagal memperbarui notifikasi.' });
        }
    }

    /**
     * Mark ALL notifications as read.
     */
    async function markAllAsRead() {
        const btn = document.getElementById('btn-mark-all');
        if (btn) btn.disabled = true;

        try {
            const resp = await fetch('/dashboard/api/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const res = await resp.json();

            if (res.success) {
                AppPopup?.success({ description: 'Semua notifikasi ditandai telah dibaca.' });
                setTimeout(() => location.reload(), 800);
            }
        } catch (err) {
            console.error('[markAllAsRead]', err);
            if (btn) btn.disabled = false;
            AppPopup?.error({ description: 'Gagal memperbarui notifikasi.' });
        }
    }

    /**
     * Delete a notification with confirmation.
     */
    async function deleteNotif(id) {
        AppPopup?.confirm({
            title: 'Hapus Notifikasi?',
            description: 'Notifikasi ini akan dihapus secara permanen dan tidak dapat dikembalikan.',
            confirmText: 'Hapus',
            cancelText: 'Batal',
            onConfirm: async () => {
                try {
                    const resp = await fetch(`/dashboard/api/notifications/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                    });

                    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                    const res = await resp.json();

                    if (res.success) {
                        const row = document.getElementById(`notif-row-${id}`);
                        if (row) {
                            row.style.transition = 'opacity 250ms ease, transform 250ms ease, max-height 300ms ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(12px)';
                            setTimeout(() => {
                                row.style.overflow = 'hidden';
                                row.style.maxHeight = row.offsetHeight + 'px';
                                requestAnimationFrame(() => { row.style.maxHeight = '0'; });
                                setTimeout(() => row.remove(), 300);
                            }, 200);
                        }
                        AppPopup?.success({ description: 'Notifikasi berhasil dihapus.' });
                    }
                } catch (err) {
                    console.error('[deleteNotif]', err);
                    AppPopup?.error({ description: 'Gagal menghapus notifikasi.' });
                }
            }
        });
    }
</script>

@endsection