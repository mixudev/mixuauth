<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Card 1: Login Sukses Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Login Sukses Hari Ini</h3>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 pl-3.5">Distribusi per jam (00–23)</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums leading-none">
                    {{ number_format(array_sum($todaySuccessHourly ?? [])) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total hari ini</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todaySuccessChart"></canvas>
        </div>
    </div>

    {{-- Card 2: Login OTP Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Login OTP Hari Ini</h3>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 pl-3.5">Distribusi per jam (00–23)</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-amber-500 dark:text-amber-400 tabular-nums leading-none">
                    {{ number_format(array_sum($todayOtpHourly ?? [])) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total hari ini</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todayOtpChart"></canvas>
        </div>
    </div>

    {{-- Card 3: Login Gagal & Block Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Gagal</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Block</span>
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Gagal &amp; Block Hari Ini</p>
            </div>
            <div class="text-right">
                @php
                $totalThreat = array_sum($todayFailedHourly ?? []) + array_sum($todayBlockedHourly ?? []);
                @endphp
                <p class="text-xl font-bold text-red-600 dark:text-red-400 tabular-nums leading-none">
                    {{ number_format($totalThreat) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total ancaman</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todayThreatChart"></canvas>
        </div>
    </div>

</div>
