<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">

    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-sm p-5 flex flex-col">
        <div class="flex items-center justify-between mb-5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Tren Risk Score</h2>
                <p class="text-xs text-slate-400 mt-0.5">Rata-rata &amp; puncak skor risiko harian</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-violet-500 rounded-full inline-block"></span>Avg</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-400 rounded-full inline-block"></span>Max</span>
            </div>
        </div>
        <div class="relative flex-1 min-h-[160px]">
            <canvas id="riskScoreChart"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Top Threat IPs</h2>
                <p class="text-xs text-slate-400 mt-0.5">IP dengan aktivitas blocked terbanyak</p>
            </div>
            <a href="{{ route('admin.security.blacklist.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
        </div>
        <div class="space-y-2">
            @forelse($topThreatIps ?? [] as $i => $ip)
            @if($i >= 6) @break @endif
            <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></div>
                    <span class="text-xs font-mono text-slate-700 dark:text-slate-300 truncate">{{ $ip->ip_address }}</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($ip->max_risk >= 80)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400 rounded">HIGH</span>
                    @elseif($ip->max_risk >= 50)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 rounded">MED</span>
                    @else
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded">LOW</span>
                    @endif
                    <span class="text-xs font-semibold tabular-nums text-slate-600 dark:text-slate-300 w-8 text-right">{{ number_format($ip->attempts) }}×</span>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="w-8 h-8 text-slate-200 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-xs text-slate-400 dark:text-slate-600">Tidak ada ancaman terdeteksi</p>
            </div>
            @endforelse
        </div>
    </div>
</div>