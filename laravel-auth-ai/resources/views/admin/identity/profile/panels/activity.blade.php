@extends('identity::profile.layout')

@section('profile-content')
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-slate-400 text-xs"></i>
                Riwayat Login & Keamanan
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Pantau aktivitas login dan deteksi hal yang mencurigakan.</p>
        </div>
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700 self-start sm:self-auto">
            Total: {{ $logs->total() }}
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Waktu</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">IP & Lokasi</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 hidden md:table-cell">Perangkat</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Status</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-right hidden sm:table-cell">Risk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                @forelse($logs as $log)
                    @php
                        $statusMap = [
                            'success'      => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50',
                            'failed'       => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50',
                            'blocked'      => 'bg-slate-900 text-white border-slate-800 dark:bg-white dark:text-slate-900',
                            'otp_required' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                            'fallback'     => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50',
                        ];
                        $cls = $statusMap[$log->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                    @endphp
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 block">{{ $log->occurred_at->translatedFormat('d M Y') }}</span>
                            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">{{ $log->occurred_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                <div>
                                    <span class="text-[11px] font-mono font-semibold text-slate-600 dark:text-slate-400">{{ $log->ip_address }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $log->country_code ?? 'Unknown' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium max-w-[180px] truncate block group-hover:whitespace-normal transition-all"
                                  title="{{ $log->user_agent }}">
                                {{ Str::limit($log->user_agent, 35) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border {{ $cls }}">
                                {{ str_replace('_', ' ', $log->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right hidden sm:table-cell">
                            @if($log->risk_score !== null)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $log->risk_score > 70 ? 'bg-red-500' : ($log->risk_score > 30 ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                    <span class="text-[10px] font-mono font-semibold text-slate-600 dark:text-slate-400">{{ $log->risk_score }}</span>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-300 font-mono">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500 shadow-sm">
                                    <i class="fa-solid fa-database text-xl"></i>
                                </div>
                                <p class="text-xs text-slate-400 font-medium">Belum ada riwayat login yang tercatat.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-8 py-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/20">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
