@extends('admin.profile.layout', ['title' => 'Log Aktivitas'])

@section('profile-content')
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-500">
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-violet-500 text-sm"></i>
                Riwayat Login & Keamanan
            </h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Pantau aktivitas login dan deteksi aktivitas yang mencurigakan.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-full border border-slate-100 dark:border-slate-700">Total: {{ $logs->total() }}</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                    <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Waktu</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">IP & Lokasi</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Device / Browser</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Status</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-right">Risk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-colors group">
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">
                                {{ $log->occurred_at->translatedFormat('d M Y') }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                                {{ $log->occurred_at->format('H:i:s') }}
                            </span>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                                <div>
                                    <span class="text-[11px] font-mono font-bold text-slate-600 dark:text-slate-400">{{ $log->ip_address }}</span>
                                    <span class="text-[10px] text-slate-400 block font-medium">{{ $log->country_code ?? 'Unknown Location' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3 max-w-[200px]">
                                <i class="fa-solid fa-desktop text-slate-300 dark:text-slate-700 text-xs"></i>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate group-hover:whitespace-normal group-hover:break-words transition-all duration-300" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 40) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            @php
                                $statusClasses = [
                                    'success'      => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
                                    'failed'       => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border-red-100 dark:border-red-800/50',
                                    'blocked'      => 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border-slate-800',
                                    'otp_required' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800/50',
                                    'fallback'     => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100 dark:border-blue-800/50',
                                ];
                                $currentStatus = $log->status;
                                $class = $statusClasses[$currentStatus] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider border {{ $class }}">
                                {{ str_replace('_', ' ', $currentStatus) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            @if($log->risk_score !== null)
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $log->risk_score > 70 ? 'bg-red-500' : ($log->risk_score > 30 ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                    <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400">{{ $log->risk_score }}</span>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-300 font-mono">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500 shadow-sm">
                                    <i class="fa-solid fa-database text-2xl"></i>
                                </div>
                                <p class="text-xs text-slate-400 font-medium">Belum ada riwayat aktivitas login tercatat.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-8 py-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/20">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
