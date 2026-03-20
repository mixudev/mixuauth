<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">

    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5 flex flex-col">
        <div class="flex items-center justify-between mb-5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Aktivitas Login</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tren harian berdasarkan status percobaan</p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-emerald-500 rounded-full inline-block"></span>Sukses</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-amber-500 rounded-full inline-block"></span>OTP</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-500 rounded-full inline-block"></span>Blocked</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-slate-400 rounded-full inline-block"></span>Gagal</span>
            </div>
        </div>
        <div class="relative flex-1 min-h-[240px] max-h-[320px]">
            <canvas id="loginActivityChart"></canvas>
        </div>
    </div>

    {{-- Card Keputusan AI: horizontal layout, donut kiri + legend kanan --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5 flex flex-col">

        {{-- Header --}}
        <div class="mb-4 flex-shrink-0">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Keputusan AI</h2>
            <p class="text-xs text-slate-400 mt-0.5">Distribusi hasil analisis risiko</p>
        </div>

        @php
        $decisions = [
            ['label' => 'ALLOW',    'key' => 'ALLOW',    'dot' => 'bg-emerald-500', 'txt' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'OTP',      'key' => 'OTP',      'dot' => 'bg-amber-400',   'txt' => 'text-amber-600 dark:text-amber-400'],
            ['label' => 'BLOCK',    'key' => 'BLOCK',    'dot' => 'bg-red-500',      'txt' => 'text-red-600 dark:text-red-400'],
            ['label' => 'FALLBACK', 'key' => 'FALLBACK', 'dot' => 'bg-slate-400',    'txt' => 'text-slate-500 dark:text-slate-400'],
            ['label' => 'PENDING',  'key' => 'PENDING',  'dot' => 'bg-sky-400',      'txt' => 'text-sky-600 dark:text-sky-400'],
        ];
        $decTotal = 0;
        foreach ($decisions as $_d) { $decTotal += $decisionBreakdown[$_d['key']] ?? 0; }

        // Hitung insight otomatis
        $allowVal   = $decisionBreakdown['ALLOW']  ?? 0;
        $blockVal   = $decisionBreakdown['BLOCK']  ?? 0;
        $otpVal     = $decisionBreakdown['OTP']    ?? 0;
        $allowPct   = $decTotal > 0 ? round(($allowVal / $decTotal) * 100, 1) : 0;
        $blockPct   = $decTotal > 0 ? round(($blockVal / $decTotal) * 100, 1) : 0;
        $otpPct     = $decTotal > 0 ? round(($otpVal   / $decTotal) * 100, 1) : 0;

        // Level ancaman berdasarkan % BLOCK
        if ($blockPct >= 20) {
            $threatLevel = ['label' => 'TINGGI',  'bg' => 'bg-red-100 dark:bg-red-500/15',     'txt' => 'text-red-600 dark:text-red-400',     'icon' => '⚠'];
        } elseif ($blockPct >= 8) {
            $threatLevel = ['label' => 'SEDANG',  'bg' => 'bg-amber-100 dark:bg-amber-500/15', 'txt' => 'text-amber-600 dark:text-amber-400', 'icon' => '●'];
        } else {
            $threatLevel = ['label' => 'RENDAH',  'bg' => 'bg-emerald-100 dark:bg-emerald-500/15', 'txt' => 'text-emerald-600 dark:text-emerald-400', 'icon' => '✓'];
        }
        @endphp

        {{-- Body: donut kiri + legend kanan --}}
        <div class="flex items-center gap-4 flex-shrink-0">

            {{-- Donut kecil, fixed --}}
            <div class="flex-shrink-0 relative" style="width:96px;height:96px;">
                <canvas id="decisionDonut" style="width:96px;height:96px;"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xs font-bold text-slate-800 dark:text-white tabular-nums leading-none">{{ number_format($decTotal) }}</span>
                    <span class="text-[8px] text-slate-400 mt-0.5">total</span>
                </div>
            </div>

            {{-- Legend kanan: 2 kolom grid, super compact --}}
            <div class="flex-1 min-w-0">
                <div class="grid grid-cols-2 gap-x-2 gap-y-2">
                    @foreach($decisions as $d)
                    @php
                    $val = $decisionBreakdown[$d['key']] ?? 0;
                    $pct = $decTotal > 0 ? round(($val / $decTotal) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $d['dot'] }}"></div>
                        <div class="min-w-0">
                            <div class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wide leading-none">{{ $d['label'] }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xs font-bold tabular-nums {{ $d['txt'] }}">{{ number_format($val) }}</span>
                                <span class="text-[9px] text-slate-400 tabular-nums">{{ $pct }}%</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[9px] text-slate-400 uppercase tracking-wide">Total</span>
                    <span class="text-xs font-bold tabular-nums text-slate-700 dark:text-slate-200">{{ number_format($decTotal) }}</span>
                </div>
            </div>

        </div>

        {{-- Divider --}}
        <div class="my-4 border-t border-slate-100 dark:border-slate-800 flex-shrink-0"></div>

        {{-- Insight cards — mengisi sisa ruang secara natural --}}
        <div class="flex-1 flex flex-col justify-between gap-3">

            {{-- Baris 1: Tingkat keberhasilan + Level ancaman --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg px-3 py-2.5">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wide mb-1">Keberhasilan</p>
                    <p class="text-lg font-bold tabular-nums text-emerald-600 dark:text-emerald-400 leading-none">{{ $allowPct }}%</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">login diizinkan</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg px-3 py-2.5">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wide mb-1">Level Ancaman</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold {{ $threatLevel['bg'] }} {{ $threatLevel['txt'] }}">
                            {{ $threatLevel['icon'] }} {{ $threatLevel['label'] }}
                        </span>
                    </div>
                    <p class="text-[9px] text-slate-400 mt-1">{{ $blockPct }}% diblokir</p>
                </div>
            </div>

        </div>

    </div>
</div>