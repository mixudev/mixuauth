@php
$cards = [
    [
        'label'     => 'Total Login',
        'value'     => $stats['total_logins'] ?? 0,
        'sub'       => 'percobaan masuk',
        'trend'     => $stats['login_trend'] ?? null,
        'color'     => 'indigo',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>'
    ],
    [
        'label'     => 'Login Sukses',
        'value'     => $stats['success_logins'] ?? 0,
        'sub'       => 'berhasil masuk',
        'trend'     => null, 'color' => 'emerald',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
     ],
    [
        'label'     => 'IP Diblokir',
        'value'     => $stats['blocked_ips'] ?? 0,
        'sub'       => 'blacklisted aktif',
        'trend'     => null, 'color' => 'red',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>'
     ],
    [
        'label'     => 'User Diblokir',
        'value'     => $stats['blocked_users'] ?? 0,
        'sub'       => 'akun terkunci',
        'trend'     => null, 'color' => 'orange',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
     ],
    [
        'label'     => 'OTP Aktif',
        'value'     => $stats['active_otps'] ?? 0,
        'sub'       => 'menunggu verifikasi',
        'trend'     => null, 'color' => 'sky',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
     ],
    [
        'label'     => 'Failed Jobs',
        'value'     => $stats['failed_jobs'] ?? 0,
        'sub'       => 'queue gagal',
        'trend'     => null,
        'color'     => ($stats['failed_jobs'] ?? 0) > 0 ? 'red' : 'slate',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
     ],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
    'red'     => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'ic' => 'text-red-600 dark:text-red-400',         'ring' => 'ring-red-100 dark:ring-red-500/20'],
    'orange'  => ['bg' => 'bg-orange-50 dark:bg-orange-500/10',   'ic' => 'text-orange-600 dark:text-orange-400',   'ring' => 'ring-orange-100 dark:ring-orange-500/20'],
    'sky'     => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',         'ic' => 'text-sky-600 dark:text-sky-400',         'ring' => 'ring-sky-100 dark:ring-sky-500/20'],
    'slate'   => ['bg' => 'bg-slate-100 dark:bg-slate-800',       'ic' => 'text-slate-500 dark:text-slate-400',     'ring' => 'ring-slate-200 dark:ring-slate-700'],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach($cards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-sm p-4 flex flex-col gap-3 hover:shadow-md dark:hover:shadow-black/20 transition-shadow">
        <div class="flex items-center justify-between">
            <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} ring-1 {{ $c['ring'] }} flex items-center justify-center">
                <svg style="width:18px;height:18px" class="{{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $card['icon'] !!}</svg>
            </div>
            @if($card['trend'] !== null)
            <span class="text-xs font-semibold {{ $card['trend'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                {{ $card['trend'] >= 0 ? '▲' : '▼' }} {{ abs($card['trend']) }}%
            </span>
            @endif
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">@shortnum($card['value'])</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $card['label'] }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>