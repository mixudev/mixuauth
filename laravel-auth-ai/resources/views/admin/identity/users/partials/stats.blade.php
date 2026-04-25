@php
$statCards = [
    ['label' => 'Total User',   'val' => $stats['total'],    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',         'color' => 'indigo',  'sub' => 'pengguna terdaftar'],
    ['label' => 'User Aktif',   'val' => $stats['active'],   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                    'color' => 'emerald', 'sub' => 'dapat login'],
    ['label' => 'Unverified',   'val' => $stats['unverified'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',                                                                                                       'color' => 'orange',  'sub' => 'email belum terverifikasi'],
    ['label' => 'Baru Hari Ini','val' => $stats['new_today'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',                                                                                                                        'color' => 'sky',     'sub' => 'registrasi baru'],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-900'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-900'],
    'red'     => ['bg' => 'bg-red-50 dark:bg-red-900/30',         'ic' => 'text-red-600 dark:text-red-400',         'ring' => 'ring-red-100 dark:ring-red-900'],
    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',     'ic' => 'text-amber-600 dark:text-amber-400',     'ring' => 'ring-amber-100 dark:ring-amber-900'],
    'orange'  => ['bg' => 'bg-orange-50 dark:bg-orange-900/30',   'ic' => 'text-orange-600 dark:text-orange-400',   'ring' => 'ring-orange-100 dark:ring-orange-900'],
    'sky'     => ['bg' => 'bg-sky-50 dark:bg-sky-900/30',         'ic' => 'text-sky-600 dark:text-sky-400',         'ring' => 'ring-sky-100 dark:ring-sky-900'],
];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach($statCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp

    <div class="group relative flex flex-col justify-between rounded-sm border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 overflow-hidden">

        {{-- Icon --}}
        <div class="relative w-8 h-8 rounded-lg {{ $c['bg'] }} ring-1 {{ $c['ring'] }} flex items-center justify-center mb-3">
            <svg class="w-[15px] h-[15px] {{ $c['ic'] }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                {!! $card['icon'] !!}
            </svg>
        </div>

        {{-- Content --}}
        <div class="relative">
            <p class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums leading-none">
                {{ number_format($card['val']) }}
            </p>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1.5 leading-none">
                {{ $card['label'] }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5 font-medium uppercase tracking-wider">
                {{ $card['sub'] }}
            </p>
        </div>
    </div>
    @endforeach
</div>
