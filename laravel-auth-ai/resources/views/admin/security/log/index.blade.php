@extends('layouts.app-dashboard')

@section('title', 'Authentication Logs - MixuAuth')
@section('page-title', 'Security Audit')
@section('page-sub', 'Riwayat percobaan login dan aktivitas autentikasi sistem.')

@section('content')
<div class="space-y-6">
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Total Attempts</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($logs->total()) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <p class="text-[10px] font-mono uppercase tracking-widest text-emerald-500">Success Rate</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                @php
                    $successCount = $logs->where('status', 'success')->count();
                    $rate = $logs->count() > 0 ? ($successCount / $logs->count()) * 100 : 0;
                @endphp
                {{ number_format($rate, 1) }}%
            </p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <p class="text-[10px] font-mono uppercase tracking-widest text-red-500">Blocked Attempts</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($logs->where('status', 'blocked')->count()) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <p class="text-[10px] font-mono uppercase tracking-widest text-amber-500">MFA Challenges</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($logs->where('status', 'otp_required')->count()) }}</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                </div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">Audit Trail</h3>
            </div>
            
            <form action="{{ route('admin.security.logs.index') }}" method="GET" class="relative group max-w-sm w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="block w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-600 dark:text-slate-300"
                    placeholder="Cari email, IP, atau status...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20">
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Timestamp</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Identity</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">IP & Location</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">AI Risk</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 tabular-nums">
                                {{ $log->occurred_at->format('d M Y') }}
                                <div class="text-[10px] opacity-60 mt-0.5">{{ $log->occurred_at->format('H:i:s') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <i class="fa-solid fa-user text-[10px]"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[180px]">
                                        {{ $log->email_attempted }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                        {{ $log->user ? 'Verified Account' : 'Unknown / Attempt' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 dark:text-slate-300 font-mono">{{ $log->ip_address }}</div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-medium text-slate-500">
                                    {{ $log->country_code ?? 'XX' }}
                                </span>
                                <span class="text-[10px] text-slate-400 truncate max-w-[150px]" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 25) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->risk_score !== null)
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $log->risk_score >= 80 ? 'bg-red-500' : ($log->risk_score >= 50 ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                    <span class="text-xs font-bold tabular-nums {{ $log->risk_score >= 80 ? 'text-red-500' : ($log->risk_score >= 50 ? 'text-amber-500' : 'text-emerald-500') }}">
                                        {{ $log->risk_score }}
                                    </span>
                                </div>
                                <div class="text-[9px] text-slate-400 mt-0.5 uppercase tracking-tighter">{{ $log->decision }}</div>
                            @else
                                <span class="text-slate-300 dark:text-slate-700 font-mono italic">no data</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusStyles = [
                                    'success' => 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                    'failed' => 'bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/20',
                                    'blocked' => 'bg-slate-900 text-white border-slate-900',
                                    'otp_required' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                    'fallback' => 'bg-sky-100 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-200 dark:border-sky-500/20',
                                ];
                                $currentStyle = $statusStyles[$log->status] ?? 'bg-slate-100 text-slate-500 border-slate-200';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold border uppercase tracking-wide {{ $currentStyle }}">
                                {{ str_replace('_', ' ', $log->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-database text-slate-300"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada log data ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
