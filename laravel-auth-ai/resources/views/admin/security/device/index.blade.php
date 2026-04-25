@extends('layouts.app-dashboard')

@section('title', 'Trusted Devices - MixuAuth')
@section('page-title', 'Trusted Devices')
@section('page-sub', 'Kelola daftar perangkat yang telah diverifikasi dan diizinkan mengakses akun pengguna.')

@section('content')

{{-- STATS SECTION --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm">
        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Devices</p>
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($stats['total']) }}</h3>
    </div>
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
        <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Active Trust</p>
        <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($stats['active']) }}</h3>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
        <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mb-1">Revoked</p>
        <h3 class="text-xl font-bold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($stats['revoked']) }}</h3>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
        <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest mb-1">Expired</p>
        <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($stats['expired']) }}</h3>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <form action="{{ route('admin.security.devices.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user, IP, atau fingerprint..." class="w-full pl-8 pr-4 py-1.5 text-[11px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded focus:outline-none focus:border-violet-500 transition-all">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-slate-800 dark:bg-slate-700 text-white text-[11px] font-bold rounded hover:bg-slate-900 transition-colors">Search</button>
        </form>
    </div>
</div>

{{-- DEVICE TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">User & Device</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Location & IP</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Status</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Last Active</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($devices as $device)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ $device->is_revoked ? 'opacity-60 grayscale' : '' }}">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                @if(str_contains(strtolower($device->device_type), 'mobile'))
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @elseif(str_contains(strtolower($device->device_type), 'tablet'))
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-100 truncate">{{ $device->user->name ?? 'Unknown User' }}</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase truncate">{{ $device->browser_name }} on {{ $device->os_name }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $device->ip_address }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">{{ $device->country_code ?? 'XX' }} Origin</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $isExpired = $device->trusted_until && $device->trusted_until < now();
                            if ($device->is_revoked) {
                                $style = 'bg-red-50 text-red-600 border-red-100';
                                $label = 'REVOKED';
                            } elseif ($isExpired) {
                                $style = 'bg-amber-50 text-amber-600 border-amber-100';
                                $label = 'EXPIRED';
                            } else {
                                $style = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                $label = 'ACTIVE';
                            }
                        @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider {{ $style }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">{{ $device->last_seen_at ? $device->last_seen_at->format('d/m/Y') : '-' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="viewDeviceDetails({{ $device->id }})" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-violet-600 transition-colors" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            
                            <form action="{{ route('admin.security.devices.revoke', $device) }}" method="POST" class="inline" id="revoke-form-{{ $device->id }}">
                                @csrf
                                <button type="button" onclick="confirmRevoke({{ $device->id }}, {{ $device->is_revoked ? 'true' : 'false' }})" class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-600 transition-colors" title="{{ $device->is_revoked ? 'Pulihkan' : 'Cabut Akses' }}">
                                    @if($device->is_revoked)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-20 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">Tidak ada perangkat ditemukan</p>
                            <p class="text-[11px] text-slate-400 mt-1">Belum ada perangkat terpercaya yang tercatat di sistem.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $devices->links() }}
</div>

{{-- MODALS --}}
@include('admin.security.device.modals.details')

<script>
    const API_BASE = '{{ url("admin/security/devices") }}';

    async function viewDeviceDetails(id) {
        try {
            const resp = await fetch(`${API_BASE}/${id}/details`);
            const res = await resp.json();
            
            if (res.success) {
                const data = res.data;
                
                document.getElementById('dev-user').textContent = data.user_name;
                document.getElementById('dev-email').textContent = data.user_email;
                document.getElementById('dev-browser').textContent = data.browser;
                document.getElementById('dev-os').textContent = data.os;
                document.getElementById('dev-type').textContent = data.device_type;
                document.getElementById('dev-ip').textContent = data.ip;
                document.getElementById('dev-country').textContent = data.country || 'Unknown';
                document.getElementById('dev-created').textContent = data.created_at;
                document.getElementById('dev-last-seen').textContent = data.last_seen_at;
                document.getElementById('dev-expires').textContent = data.trusted_until;
                document.getElementById('dev-fingerprint').textContent = data.fingerprint;

                // Status Badge
                let style = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                let label = 'ACTIVE';
                if (data.is_revoked) {
                    style = 'bg-red-50 text-red-600 border-red-100';
                    label = 'REVOKED';
                } else if (data.is_expired) {
                    style = 'bg-amber-50 text-amber-600 border-amber-100';
                    label = 'EXPIRED';
                }

                document.getElementById('dev-status-badge').innerHTML = `
                    <span class="px-3 py-1 rounded text-[10px] font-bold border uppercase tracking-wider ${style}">
                        ${label}
                    </span>
                `;

                AppModal.open('deviceDetailsModal');
            }
        } catch (err) {
            console.error('[viewDeviceDetails]', err);
            showToast('error', 'Gagal memuat detail perangkat.');
        }
    }

    function confirmRevoke(id, isRevoked) {
        const action = isRevoked ? 'memulihkan' : 'mencabut';
        AppPopup.confirm({
            title: 'Konfirmasi Akses',
            description: `Apakah Anda yakin ingin <b>${action}</b> kepercayaan untuk perangkat ini?`,
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batalkan',
            onConfirm: () => document.getElementById(`revoke-form-${id}`).submit()
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (window.AppPopup) {
                AppPopup.success({ description: 'Fingerprint hash berhasil disalin ke clipboard.' });
            } else {
                alert('Copied to clipboard');
            }
        });
    }

    function showToast(type, message) {
        if (window.AppPopup) {
            if (type === 'success') AppPopup.success({ description: message });
            else AppPopup.error({ description: message });
        } else {
            alert(message);
        }
    }
</script>
@endsection
