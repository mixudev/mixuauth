@extends('layouts.app-dashboard')

@section('title', 'OTP Logs - MixuAuth')
@section('page-title', 'OTP History')
@section('page-sub', 'Pemantauan pengiriman dan verifikasi One-Time Password sistem.')

@section('content')
<div class="space-y-6">
    <!-- Filter Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('admin.security.otps.index') }}" method="GET" class="relative group max-w-sm w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all outline-none text-slate-600 dark:text-slate-300 shadow-sm"
                placeholder="Cari user atau email...">
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20">
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">User Identity</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Security Code</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Expiration</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($otps as $otp)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-900/10 flex items-center justify-center text-amber-600 border border-amber-100 dark:border-amber-500/20">
                                    <i class="fa-solid fa-key text-[10px]"></i>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $otp->user->email ?? 'Guest' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-mono font-bold tracking-widest text-slate-800 dark:text-white">
                                {{ substr($otp->code, 0, 3) }} •••
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Type: Auth / MFA</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 dark:text-slate-400">@humanstime($otp->expires_at)</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Created: {{ $otp->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($otp->is_verified)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 text-[9px] font-bold uppercase tracking-wide">VERIFIED</span>
                            @elseif($otp->isExpired())
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700 text-[9px] font-bold uppercase tracking-wide">EXPIRED</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 text-[9px] font-bold uppercase tracking-wide animate-pulse">PENDING</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-key text-3xl text-slate-200 dark:text-slate-800 mb-4"></i>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada pengiriman OTP tercatat</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($otps->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            {{ $otps->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
