@extends('layouts.app-dashboard')

@section('title', 'Role Management')
@section('page-title', 'Roles')
@section('page-sub', 'Kelola role dan hak akses pengguna sistem')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Role</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola role dan hak akses pengguna sistem</p>
    </div>
    @if(auth()->user()->hasPermission('roles.create'))
    <button
        onclick="window.location.href='{{ route('dashboard.roles.create') }}'"
        class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors shadow-sm"
    >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Role
    </button>
    @endif
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total Role',  'val' => $stats['total'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>', 'color' => 'violet'],
    ['label' => 'System Role', 'val' => $stats['system'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'blue'],
    ['label' => 'Custom Role',  'val' => $stats['custom'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>', 'color' => 'emerald'],
];
$colorMap = [
    'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-500/10',   'ic' => 'text-violet-600 dark:text-violet-400',   'ring' => 'ring-violet-100 dark:ring-violet-500/20'],
    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-500/10',       'ic' => 'text-blue-600 dark:text-blue-400',       'ring' => 'ring-blue-100 dark:ring-blue-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
                placeholder="Cari role..."
                value="{{ $filters['search'] ?? '' }}"
                class="w-full px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>
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
     ROLES TABLE
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Role</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Deskripsi</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Permission</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">User</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @php
                                $colors = ['super-admin' => 'red', 'admin' => 'indigo', 'security-officer' => 'amber', 'user' => 'slate'];
                                $color = $colors[$role->slug] ?? 'slate';
                                $bgClass = match($color) {
                                    'red' => 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300',
                                    'indigo' => 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
                                    'amber' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                    default => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $bgClass }}">
                                {{ $role->name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                        {{ $role->description ?? '-' }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-violet-100 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-xs font-semibold">
                            {{ $role->permissions()->count() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                            {{ $role->users()->count() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if(auth()->user()->hasPermission('roles.view'))
                            <a href="{{ route('dashboard.roles.edit', $role->id) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            @endif
                            @if($role->slug !== 'super-admin' && $role->slug !== 'admin' && $role->slug !== 'user' && $role->slug !== 'security-officer' && auth()->user()->hasPermission('roles.delete'))
                            <button
                                onclick="confirmDelete('{{ route('dashboard.roles.destroy', $role->id) }}', '{{ $role->name }}')"
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
                        <p class="text-slate-400 text-sm">Tidak ada role ditemukan</p>
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
    {{ $roles->links() }}
</div>

{{-- Form Hapus (Hidden) --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url, roleName) {
    AppPopup.confirm({
        title: 'Hapus Role?',
        description: `Role "${roleName}" akan dihapus beserta semua relasi. Tindakan ini tidak dapat dibatalkan.`,
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
