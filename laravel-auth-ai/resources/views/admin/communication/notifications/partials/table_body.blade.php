@forelse($notifications as $notif)
@php
    $isUnread = !$notif->read_at;
    $typeConfig = match($notif->type) {
        'error'   => ['icon_bg' => 'bg-red-50 dark:bg-red-500/10', 'icon_color' => 'text-red-600 dark:text-red-400', 'border' => 'border-red-100 dark:border-red-500/20', 'label' => 'Critical'],
        'warning' => ['icon_bg' => 'bg-amber-50 dark:bg-amber-500/10', 'icon_color' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-500/20', 'label' => 'Warning'],
        'success' => ['icon_bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'icon_color' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-500/20', 'label' => 'Success'],
        default   => ['icon_bg' => 'bg-indigo-50 dark:bg-indigo-500/10', 'icon_color' => 'text-indigo-600 dark:text-indigo-400', 'border' => 'border-indigo-100 dark:border-indigo-500/20', 'label' => 'Info'],
    };
@endphp
<tr id="notif-row-{{ $notif->id }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group relative {{ $isUnread ? 'bg-indigo-50/30 dark:bg-indigo-500/[0.04]' : '' }}">
    <td class="px-5 py-4 relative">
        {{-- UNREAD BORDER INDICATOR --}}
        @if($isUnread)
        <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500"></div>
        @endif
        
        <div class="flex gap-4">
            <div class="w-10 h-10 rounded shrink-0 flex items-center justify-center {{ $typeConfig['icon_bg'] }} {{ $typeConfig['icon_color'] }} border {{ $typeConfig['border'] }}">
                @if($notif->type == 'error')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($notif->type == 'warning')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-[11px] text-slate-800 dark:text-slate-200">{{ $notif->title }}</span>
                    @if($isUnread)
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 italic">
                    {{ $notif->message }}
                </p>
            </div>
        </div>
    </td>
    <td class="px-5 py-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Event:</span>
                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    {{ $notif->event ?? 'N/A' }}
                </span>
            </div>
            @if($notif->ip_address)
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">IP:</span>
                <span class="text-[10px] font-mono text-slate-500">{{ $notif->ip_address }}</span>
            </div>
            @endif
        </div>
    </td>
    <td class="px-5 py-4">
        <div class="flex flex-col">
            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">@humanstime($notif->created_at)</span>
            <span class="text-[9px] text-slate-400 font-mono">{{ $notif->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
    </td>
    <td class="px-5 py-4 text-right">
        <div class="flex items-center justify-end gap-2">
            @if($isUnread)
            <button onclick="markAsRead({{ $notif->id }})" class="p-1.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors" title="Tandai Dibaca">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
            @endif
            <button onclick="deleteNotif({{ $notif->id }})" class="p-1.5 rounded bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 transition-colors" title="Hapus">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/></svg>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="4" class="px-5 py-20 text-center">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 rounded bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak Ada Notifikasi</h3>
            <p class="text-[11px] text-slate-400 max-w-xs mt-1">Belum ada aktivitas keamanan yang tercatat saat ini.</p>
        </div>
    </td>
</tr>
@endforelse
