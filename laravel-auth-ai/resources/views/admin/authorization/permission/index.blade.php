@extends('layouts.app-dashboard')

@section('title', 'Permission Management')
@section('page-title', 'Permissions')
@section('page-sub', 'Kelola izin akses dan hak pengguna di sistem')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Permission</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola izin akses dan hak pengguna di sistem</p>
    </div>
    @if(auth()->user()->hasPermission('permissions.create'))
    <button
        onclick="window.location.href='{{ route('dashboard.permissions.create') }}'"
        class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors shadow-sm"
    >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Permission
    </button>
    @endif
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total Permission', 'val' => $stats['total'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'green'],
    ['label' => 'Grup Permission', 'val' => $stats['groups'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>', 'color' => 'cyan'],
];
$colorMap = [
    'green' => ['bg' => 'bg-green-50 dark:bg-green-500/10',   'ic' => 'text-green-600 dark:text-green-400',   'ring' => 'ring-green-100 dark:ring-green-500/20'],
    'cyan'  => ['bg' => 'bg-cyan-50 dark:bg-cyan-500/10',     'ic' => 'text-cyan-600 dark:text-cyan-400',     'ring' => 'ring-cyan-100 dark:ring-cyan-500/20'],
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    @foreach($statCards as $card)
    <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 {{ $colorMap[$card['color']]['bg'] }} space-y-2">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $card['val'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-white/50 dark:bg-white/5 flex items-center justify-center {{ $colorMap[$card['color']]['ic'] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    {!! $card['icon'] !!}
                </svg>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     FILTER & SEARCH
───────────────────────────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-col sm:flex-row gap-3">
    <form method="GET" class="flex gap-3 flex-1">
        <div class="flex-1">
            <input
                type="text"
                name="search"
                placeholder="Cari permission..."
                value="{{ $filters['search'] ?? '' }}"
                class="w-full px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>
        <select name="group" class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Grup</option>
            @foreach($groups as $grp)
            <option value="{{ $grp }}" {{ ($filters['group'] ?? '') === $grp ? 'selected' : '' }}>{{ ucfirst($grp) }}</option>
            @endforeach
        </select>
        <select name="sort" class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="name" {{ ($filters['sort'] ?? 'name') === 'name' ? 'selected' : '' }}>A-Z</option>
            <option value="recent" {{ ($filters['sort'] ?? 'name') === 'recent' ? 'selected' : '' }}>Terbaru</option>
        </select>
        <button type="submit" class="px-3.5 py-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-semibold text-sm rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
            Filter
        </button>
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     PERMISSIONS TABLE
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Permission</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Deskripsi</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Grup</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Role</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $permission->name }}</div>
                    </td>
                    <td class="px-5 py-3 text-slate-600 dark:text-slate-400 text-sm">
                        {{ $permission->description ?? '-' }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 text-xs font-semibold">
                            {{ $permission->group }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 text-xs font-semibold">
                            {{ $permission->roles()->count() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if(auth()->user()->hasPermission('permissions.view'))
                            <a href="{{ route('dashboard.permissions.edit', $permission->id) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('permissions.delete'))
                            <button
                                onclick="confirmDelete('{{ route('dashboard.permissions.destroy', $permission->id) }}', '{{ $permission->name }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/>
                                </svg>
                                Hapus
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center">
                        <p class="text-slate-400 text-sm">Tidak ada permission ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     PAGINATION
───────────────────────────────────────────────────────────────────────── --}}
<div class="mt-6">
    {{ $permissions->links() }}
</div>

{{-- Form Hapus (Hidden) --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url, permName) {
    AppPopup.confirm({
        title: 'Hapus Permission?',
        description: `Permission "${permName}" akan dihapus. Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        onConfirm: () => {
            const form = document.getElementById('delete-form');
            form.action = url;
            form.submit();
        },
    });
}
</script>

@endsection
