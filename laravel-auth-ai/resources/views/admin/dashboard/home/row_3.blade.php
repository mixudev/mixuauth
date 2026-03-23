<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">

    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Log Login Terbaru</h2>
                <p class="text-xs text-slate-400 mt-0.5">10 percobaan login terakhir</p>
            </div>
            <a href="{{ route('security.logs') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-5 py-2.5 font-medium text-slate-400 dark:text-slate-600">Email / User</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden sm:table-cell">IP</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden md:table-cell">Risk</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600">Status</th>
                        <th class="text-right px-5 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden lg:table-cell">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs ?? [] as $log)
                    <tr class="border-b border-slate-50 dark:border-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0">
                                    {{ strtoupper(substr($log->email_attempted, 0, 1)) }}
                                </div>
                                <span class="font-mono text-slate-600 dark:text-slate-300 truncate max-w-[140px]">{{ $log->email_attempted }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 hidden sm:table-cell">
                            <span class="font-mono text-slate-500 dark:text-slate-400">{{ $log->ip_address }}</span>
                            @if($log->country_code)<span class="ml-1 text-slate-400 dark:text-slate-600">{{ $log->country_code }}</span>@endif
                        </td>
                        <td class="px-3 py-2.5 hidden md:table-cell">
                            @if($log->risk_score !== null)
                            <span class="tabular-nums font-medium {{ $log->risk_score >= 80 ? 'text-red-600 dark:text-red-400' : ($log->risk_score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">{{ $log->risk_score }}</span>
                            @else<span class="text-slate-300 dark:text-slate-700">—</span>@endif
                        </td>
                        <td class="px-3 py-2.5">
                            @php
                            $sm = ['success'=>'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400','otp_required'=>'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400','blocked'=>'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400','failed'=>'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400','fallback'=>'bg-sky-100 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $sm[$log->status] ?? $sm['failed'] }}">
                                {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right hidden lg:table-cell">
                            <span class="text-slate-400 dark:text-slate-600 tabular-nums">@humanstime($log->occurred_at)</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 dark:text-slate-600 text-xs">Belum ada data log</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Status OTP</h2>
            <div class="space-y-2.5">
                @foreach([['label'=>'Aktif & Belum Verified','val'=>$otpSummary['active']??0,'dot'=>'bg-amber-400'],['label'=>'Sudah Diverifikasi','val'=>$otpSummary['verified']??0,'dot'=>'bg-emerald-500'],['label'=>'Kedaluwarsa','val'=>$otpSummary['expired']??0,'dot'=>'bg-slate-300 dark:bg-slate-600']] as $o)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $o['dot'] }}"></div>
                        <span class="text-xs text-slate-600 dark:text-slate-300">{{ $o['label'] }}</span>
                    </div>
                    <span class="text-xs font-semibold tabular-nums text-slate-800 dark:text-white">{{ number_format($o['val']) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Perangkat Terpercaya</h2>
            <div class="grid grid-cols-3 gap-3 text-center">
                @foreach([['label'=>'Aktif','val'=>$deviceSummary['total']??0,'color'=>'text-emerald-600 dark:text-emerald-400'],['label'=>'Expired','val'=>$deviceSummary['expired']??0,'color'=>'text-amber-600 dark:text-amber-400'],['label'=>'Revoked','val'=>$deviceSummary['revoked']??0,'color'=>'text-red-600 dark:text-red-400']] as $d)
                <div>
                    <p class="text-xl font-bold tabular-nums {{ $d['color'] }}">{{ number_format($d['val']) }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $d['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifikasi</h2>
                <a href="{{ route('security.notifications') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Semua</a>
            </div>
            <div class="space-y-2">
                @forelse($recentNotifs ?? [] as $notif)
                @php
                $nm=['error'=>['bg'=>'bg-red-100 dark:bg-red-500/15','t'=>'text-red-500'],'warning'=>['bg'=>'bg-amber-100 dark:bg-amber-500/15','t'=>'text-amber-500'],'success'=>['bg'=>'bg-emerald-100 dark:bg-emerald-500/15','t'=>'text-emerald-500'],'info'=>['bg'=>'bg-sky-100 dark:bg-sky-500/15','t'=>'text-sky-500']];
                $ni=$nm[$notif->type]??['bg'=>'bg-slate-100 dark:bg-slate-800','t'=>'text-slate-400'];
                @endphp
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 flex-shrink-0 rounded {{ $ni['bg'] }} flex items-center justify-center mt-0.5">
                        <svg class="w-3 h-3 {{ $ni['t'] }}" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $notif->title }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-600 truncate">@humanstime($notif->created_at)</p>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 dark:text-slate-600 text-center py-3">Tidak ada notifikasi baru</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
