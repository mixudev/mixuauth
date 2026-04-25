@extends('layouts.app-dashboard')

@section('title', 'Notifikasi Keamanan')
@section('page-title', 'Notifikasi')
@section('page-sub', 'Pantau aktivitas keamanan dan peringatan sistem secara real-time.')

@section('content')

{{-- STATS SECTION --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Notifikasi</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($stats['total']) }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-100 dark:border-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Belum Dibaca</p>
                <h3 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">{{ number_format($stats['unread']) }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest mb-1">Peringatan</p>
                <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($stats['warning']) }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Telah Dibaca</p>
                <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($stats['total'] - $stats['unread']) }}</h3>
            </div>
            <div class="w-9 h-9 rounded bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari notifikasi..." class="w-full pl-8 pr-4 py-1.5 text-[11px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded focus:outline-none focus:border-indigo-500 transition-all">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-slate-800 dark:bg-slate-700 text-white text-[11px] font-bold rounded hover:bg-slate-900 transition-colors">Cari</button>
        </form>

        <div class="flex items-center gap-1.5">
            @foreach(['unread' => 'Belum Dibaca', 'read' => 'Dibaca'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['read' => $val]) }}" class="px-3 py-1.5 rounded text-[10px] font-bold transition-all {{ request('read') == $val ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:border-indigo-300' }}">
                {{ $label }}
            </a>
            @endforeach
            @if(request('read') || request('search') || request('type'))
            <a href="{{ route('dashboard.notifications.all') }}" class="p-1.5 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Reset Filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3 w-full lg:w-auto">
        <button id="btn-mark-all" onclick="markAllAsRead()" class="px-4 py-1.5 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 text-[11px] font-bold rounded bg-indigo-50/50 dark:bg-indigo-500/5 hover:bg-indigo-100 transition-all flex items-center justify-center gap-2" {{ $stats['unread'] == 0 ? 'disabled' : '' }}>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tandai Semua
        </button>
        <button onclick="openBulkDeleteModal()" class="px-4 py-1.5 border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 text-[11px] font-bold rounded bg-red-50/50 dark:bg-red-500/5 hover:bg-red-100 transition-all flex items-center justify-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/></svg>
            Hapus Masal
        </button>
    </div>
</div>

{{-- NOTIFICATIONS TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Detail Notifikasi</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Info Pendukung</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Waktu</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody id="notif-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
                @include('communication::notifications.partials.table_body')
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
<div id="pagination-container" class="mt-4">
    @if($notifications->hasPages())
        {{ $notifications->links() }}
    @endif
</div>

{{-- MODALS --}}
@include('communication::notifications.modals.bulk_delete')

<script>
    const CSRF = '{{ csrf_token() }}';
    const API_BASE = '{{ url("dashboard/notifications/api") }}';

    async function refreshTable() {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        
        try {
            const resp = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (resp.ok) {
                const data = await resp.json();
                document.getElementById('notif-table-body').innerHTML = data.html;
                document.getElementById('pagination-container').innerHTML = data.pagination;
            }
        } catch (err) {
            console.error('[refreshTable]', err);
        }
    }

    async function markAsRead(id) {
        const row = document.getElementById(`notif-row-${id}`);
        if (row) row.style.opacity = '0.5';

        try {
            const resp = await fetch(`${API_BASE}/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });

            if (resp.ok) {
                showToast('success', 'Notifikasi ditandai dibaca.');
                refreshTable();
            } else {
                if (row) row.style.opacity = '1';
            }
        } catch (err) {
            if (row) row.style.opacity = '1';
            showToast('error', 'Gagal memperbarui status.');
        }
    }

    async function markAllAsRead() {
        const btn = document.getElementById('btn-mark-all');
        btn.disabled = true;
        
        try {
            const resp = await fetch(`${API_BASE}/read-all`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });

            if (resp.ok) {
                showToast('success', 'Semua notifikasi ditandai dibaca.');
                location.reload(); 
            }
        } catch (err) {
            btn.disabled = false;
            showToast('error', 'Gagal memproses permintaan.');
        }
    }

    function deleteNotif(id) {
        AppPopup.confirm({
            title: 'Hapus Notifikasi?',
            description: 'Tindakan ini permanen.',
            confirmText: 'Ya, Hapus',
            onConfirm: () => {
                const row = document.getElementById(`notif-row-${id}`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        if (row.parentNode) row.remove();
                        if (document.querySelectorAll('#notif-table-body tr').length === 0) location.reload();
                    }, 300);
                }

                fetch(`${API_BASE}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                })
                .then(resp => {
                    if (resp.ok) {
                        showToast('success', 'Notifikasi dihapus.');
                        refreshTable();
                    } else {
                        showToast('error', 'Gagal menghapus di server.');
                        location.reload();
                    }
                })
                .catch(err => {
                    showToast('error', 'Terjadi kesalahan koneksi.');
                    location.reload();
                });
            }
        });
    }

    // Bulk Delete Logic
    window.openBulkDeleteModal = function() {
        if (window.AppModal) AppModal.open('bulkDeleteModal');
    }

    window.closeBulkDeleteModal = function() {
        if (window.AppModal) AppModal.close('bulkDeleteModal');
    }

    window.submitBulkDelete = function() {
        const startDate = document.getElementById('bulk-start-date').value;
        const endDate = document.getElementById('bulk-end-date').value;

        if (!startDate || !endDate) {
            showToast('error', 'Pilih rentang waktu yang valid.');
            return;
        }

        const btn = document.getElementById('btn-submit-bulk-delete');
        const spinner = document.getElementById('bulk-delete-spinner');
        
        btn.disabled = true;
        spinner.classList.remove('hidden');

        fetch(`${API_BASE}/bulk-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ start_date: startDate, end_date: endDate })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', res.message);
                closeBulkDeleteModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('error', res.message || 'Gagal menghapus masal.');
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        })
        .catch(() => {
            showToast('error', 'Terjadi kesalahan server.');
            btn.disabled = false;
            spinner.classList.add('hidden');
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
