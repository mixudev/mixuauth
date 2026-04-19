@extends('layouts.app-dashboard')

@section('title', 'Trusted Devices - MixuAuth')
@section('page-title', 'Trusted Devices')
@section('page-sub', 'Kelola daftar perangkat yang telah diverifikasi dan diizinkan mengakses akun pengguna.')

@section('content')
<div class="space-y-6">
    <!-- Filter Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('admin.security.devices.index') }}" method="GET" class="relative group max-w-sm w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-violet-500 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all outline-none text-slate-600 dark:text-slate-300 shadow-sm"
                placeholder="Cari user, IP, atau fingerprint...">
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20">
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">User & Device</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Fingerprint</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">IP & Status</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($devices as $device)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $device->is_revoked ? 'opacity-60 grayscale' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-violet-50 dark:bg-violet-900/10 flex items-center justify-center text-violet-600 border border-violet-100 dark:border-violet-500/20">
                                    <i class="fa-solid fa-laptop text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $device->user->name ?? 'Deleted User' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $device->device_name }} / {{ $device->browser }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-slate-500 dark:text-slate-400 font-mono">
                                {{ substr($device->fingerprint_hash, 0, 16) }}...
                            </code>
                            <div class="text-[10px] text-slate-400 mt-1">Verified: {{ $device->verified_at ? $device->verified_at->format('d M Y') : 'Unknown' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-mono text-slate-600 dark:text-slate-300">{{ $device->ip_address }}</div>
                            <div class="mt-1 flex items-center gap-2">
                                @if($device->is_revoked)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/20 text-[9px] font-bold text-red-600 dark:text-red-400 uppercase">Revoked</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/20 text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Active</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.security.devices.revoke', $device) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="px-3 py-1.5 rounded-lg border {{ $device->is_revoked ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' : 'border-red-200 text-red-600 hover:bg-red-50' }} text-[10px] font-bold transition-all uppercase">
                                    {{ $device->is_revoked ? 'Restore' : 'Revoke' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-mobile-screen text-3xl text-slate-200 dark:text-slate-800 mb-4"></i>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum Ada Perangkat Terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($devices->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            {{ $devices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
