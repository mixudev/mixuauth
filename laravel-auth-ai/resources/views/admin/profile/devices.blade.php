@extends('admin.profile.layout', ['title' => 'Perangkat Aktif'])

@section('profile-content')
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-300">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-laptop text-slate-400 text-xs"></i>
                Perangkat Terpercaya
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Kelola perangkat yang telah diverifikasi untuk login ke akun Anda.</p>
        </div>
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700">
            {{ $devices->count() }} Perangkat
        </span>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
        @forelse($devices as $device)
            @php
                $isActive      = ! $device->is_revoked && $device->trusted_until?->isFuture();
                $isCurrent     = $currentFingerprint && $device->fingerprint_hash === $currentFingerprint;
                $isExpired     = ! $device->is_revoked && $device->trusted_until?->isPast();
                $isRevoked     = $device->is_revoked;

                // Parse device label to get icon
                $label = strtolower($device->device_label ?? '');
                $deviceIcon = 'fa-laptop';
                if (str_contains($label, 'mobile') || str_contains($label, 'android') || str_contains($label, 'iphone')) {
                    $deviceIcon = 'fa-mobile-screen-button';
                } elseif (str_contains($label, 'tablet') || str_contains($label, 'ipad')) {
                    $deviceIcon = 'fa-tablet-screen-button';
                }
            @endphp

            <div class="px-8 py-5 flex flex-col sm:flex-row sm:items-center gap-4 group hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-colors" data-device-id="{{ $device->id }}">
                <!-- Icon -->
                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid {{ $deviceIcon }} text-slate-500 dark:text-slate-400 text-lg"></i>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                            {{ $device->device_label ?? 'Perangkat Tidak Dikenal' }}
                        </span>
                        @if($isCurrent)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-violet-50 text-violet-600 dark:bg-violet-900/20 dark:text-violet-400 border border-violet-200 dark:border-violet-800/50 uppercase tracking-wide">Perangkat Ini</span>
                        @endif
                        @if($isActive && ! $isCurrent)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 uppercase tracking-wide">Aktif</span>
                        @elseif($isExpired)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-500 border border-amber-200 dark:border-amber-800/50 uppercase tracking-wide">Kedaluwarsa</span>
                        @elseif($isRevoked)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800/50 uppercase tracking-wide">Dicabut</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-5 gap-y-1">
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-[9px] text-slate-300"></i>
                            {{ $device->country_code ?? 'Unknown' }} · {{ $device->ip_address }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock text-[9px] text-slate-300"></i>
                            Terakhir aktif: {{ $device->last_seen_at?->diffForHumans() ?? '—' }}
                        </span>
                        @if($device->trusted_until && ! $isRevoked)
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar text-[9px] text-slate-300"></i>
                                Berlaku sampai: {{ $device->trusted_until->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Revoke button -->
                @if(! $isRevoked)
                    <div class="shrink-0">
                        <button type="button"
                            onclick="revokeDevice({{ $device->id }}, this)"
                            class="h-9 px-4 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-red-300 dark:hover:border-red-800 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 text-[11px] font-semibold transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-ban text-[10px]"></i>
                            Cabut
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="px-8 py-16 text-center">
                <div class="w-14 h-14 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500 mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-laptop text-xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada perangkat terpercaya tercatat.</p>
                <p class="text-xs text-slate-400 mt-1">Perangkat akan muncul setelah Anda login dan melewati verifikasi OTP.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
async function revokeDevice(deviceId, btn) {
    AppPopup.confirm({
        title: 'Cabut Perangkat?',
        description: 'Perangkat ini tidak akan lagi dipercaya. Login berikutnya dari perangkat ini akan memerlukan verifikasi OTP.',
        confirmText: 'Ya, Cabut',
        cancelText: 'Batal',
        onConfirm: async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';

            try {
                const res = await fetch(`{{ url('dashboard/profile/devices') }}/${deviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (res.ok) {
                    // Remove device row from DOM
                    const row = btn.closest('[data-device-id]');
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(10px)';
                    row.style.transition = 'all 0.3s ease';
                    setTimeout(() => row.remove(), 300);

                    AppPopup.success?.({ title: 'Berhasil', description: 'Perangkat berhasil dicabut.' });
                } else {
                    const data = await res.json();
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
                    alert(data.message || 'Gagal mencabut perangkat.');
                }
            } catch(e) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
                console.error(e);
            }
        }
    });
}
</script>
@endpush
@endsection
