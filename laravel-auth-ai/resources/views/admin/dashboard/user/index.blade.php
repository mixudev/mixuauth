@extends('layouts.app-dashboard')

@section('title', 'User Management')
@section('page-title', 'Users')
@section('page-sub', 'Kelola, monitor, dan kontrol akses semua pengguna sistem')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Pengguna</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola, monitor, dan kontrol akses semua pengguna sistem</p>
    </div>
    <button
        onclick="openCreateModal()"
        class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold  transition-colors shadow-sm"
    >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Pengguna
    </button>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total User',   'val' => $stats['total'],    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',         'color' => 'indigo',  'sub' => 'pengguna terdaftar'],
    ['label' => 'User Aktif',   'val' => $stats['active'],   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                    'color' => 'emerald', 'sub' => 'dapat login'],
    ['label' => 'Diblokir',     'val' => $stats['blocked'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',                                                                                                               'color' => 'red',     'sub' => 'akun terkunci'],
    ['label' => 'Nonaktif',     'val' => $stats['inactive'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                'color' => 'amber',   'sub' => 'akun dinonaktifkan'],
    ['label' => 'Unverified',   'val' => $stats['unverified'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',                                                                                                       'color' => 'orange',  'sub' => 'email belum terverifikasi'],
    ['label' => 'Baru Hari Ini','val' => $stats['new_today'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',                                                                                                                        'color' => 'sky',     'sub' => 'registrasi baru'],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
    'red'     => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'ic' => 'text-red-600 dark:text-red-400',         'ring' => 'ring-red-100 dark:ring-red-500/20'],
    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-500/10',     'ic' => 'text-amber-600 dark:text-amber-400',     'ring' => 'ring-amber-100 dark:ring-amber-500/20'],
    'orange'  => ['bg' => 'bg-orange-50 dark:bg-orange-500/10',   'ic' => 'text-orange-600 dark:text-orange-400',   'ring' => 'ring-orange-100 dark:ring-orange-500/20'],
    'sky'     => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',         'ic' => 'text-sky-600 dark:text-sky-400',         'ring' => 'ring-sky-100 dark:ring-sky-500/20'],
];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach($statCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md dark:hover:shadow-black/20 transition-shadow">
        <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} ring-1 {{ $c['ring'] }} flex items-center justify-center">
            <svg style="width:18px;height:18px" class="{{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $card['icon'] !!}</svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($card['val']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $card['label'] }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     FILTER & SEARCH BAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 mb-4">
    <form method="GET" action="{{ route('dashboard.users.index') }}" id="filterForm">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari nama, email, atau IP..."
                    class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"
                    oninput="debounceSubmit()"
                />
            </div>

            {{-- Status filter pills --}}
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                @foreach(['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif', 'blocked' => 'Diblokir', 'deleted' => 'Dihapus'] as $val => $label)
                <button
                    type="button"
                    onclick="setStatus('{{ $val }}')"
                    class="status-btn px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 {{ ($filters['status'] ?? 'all') === $val ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                    data-status="{{ $val }}"
                >{{ $label }}</button>
                @endforeach
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ $filters['status'] ?? 'all' }}"/>

            {{-- Sort --}}
            <select
                name="sort"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                <option value="-created_at"   {{ ($filters['sort'] ?? '-created_at') === '-created_at'   ? 'selected' : '' }}>Terdaftar Terbaru</option>
                <option value="created_at"    {{ ($filters['sort'] ?? '') === 'created_at'    ? 'selected' : '' }}>Terdaftar Lama</option>
                <option value="name"          {{ ($filters['sort'] ?? '') === 'name'          ? 'selected' : '' }}>Nama A → Z</option>
                <option value="-name"         {{ ($filters['sort'] ?? '') === '-name'         ? 'selected' : '' }}>Nama Z → A</option>
                <option value="-last_login_at"{{ ($filters['sort'] ?? '') === '-last_login_at'? 'selected' : '' }}>Login Terbaru</option>
                <option value="block_count"   {{ ($filters['sort'] ?? '') === 'block_count'   ? 'selected' : '' }}>Blokir Terbanyak</option>
            </select>

            {{-- Per page --}}
            <select
                name="per_page"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                @foreach([10, 25, 50, 100] as $pp)
                <option value="{{ $pp }}" {{ (int)($filters['per_page'] ?? 15) === $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
                @endforeach
            </select>

            {{-- Reset filter --}}
            @if(!empty($filters['search']) || !empty($filters['status']) && $filters['status'] !== 'all')
            <a href="{{ route('dashboard.users.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     BULK ACTION BAR (muncul saat ada selection)
───────────────────────────────────────────────────────────────────────── --}}
<div id="bulkBar" class="hidden items-center gap-3 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl px-4 py-3 mb-4">
    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span id="bulkCount" class="text-xs font-semibold text-indigo-700 dark:text-indigo-300"></span>
    <div class="flex items-center gap-2 ml-2">
        <button onclick="openBulkBlockModal()" class="px-3 py-1.5 text-xs font-semibold bg-red-100 dark:bg-red-500/15 hover:bg-red-200 dark:hover:bg-red-500/25 text-red-700 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-500/30 transition-all">
            Blokir Semua
        </button>
        <button onclick="bulkAction('unblock')" class="px-3 py-1.5 text-xs font-semibold bg-emerald-100 dark:bg-emerald-500/15 hover:bg-emerald-200 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-500/30 transition-all">
            Unblokir Semua
        </button>
        <button onclick="bulkAction('delete')" class="px-3 py-1.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
            Hapus Semua
        </button>
    </div>
    <button onclick="clearSelection()" class="ml-auto p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     TABLE
───────────────────────────────────────────────────────────────────────── --}}
@include('admin.dashboard.user.table')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: CREATE USER
═══════════════════════════════════════════════════════════════════════════ --}}
@include('admin.dashboard.user.modal_create')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: EDIT USER
═══════════════════════════════════════════════════════════════════════════ --}}
@include('admin.dashboard.user.modal_edit')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: DETAIL USER
═══════════════════════════════════════════════════════════════════════════ --}}
@include('admin.dashboard.user.modal_detail')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: BLOCK USER
═══════════════════════════════════════════════════════════════════════════ --}}
@include('admin.dashboard.user.modal_block')





<style>
    @keyframes modalIn { from { opacity:0; transform:translateY(16px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    .modal-panel { animation: modalIn .22s cubic-bezier(.16,1,.3,1); }
</style>

<script>
(function () {
    var CSRF = '{{ csrf_token() }}';

    // ── Routes ────────────────────────────────────────────────────────────────
    var ROUTES = {
        store    : '{{ route("dashboard.users.store") }}',
        update   : function(id) { return '{{ url("dashboard/users") }}/' + id; },
        destroy  : function(id) { return '{{ url("dashboard/users") }}/' + id; },
        block    : function(id) { return '{{ url("dashboard/users") }}/' + id + '/block'; },
        unblock  : function(id) { return '{{ url("dashboard/users") }}/' + id + '/unblock'; },
        resetPwd : function(id) { return '{{ url("dashboard/users") }}/' + id + '/reset-password'; },
        bulk     : '{{ route("dashboard.users.bulk") }}',
    };

    // ── Selection ─────────────────────────────────────────────────────────────
    var selectedIds = [];

    window.updateSelection = function () {
        selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(function(c){ return parseInt(c.value); });
        var bar   = document.getElementById('bulkBar');
        var count = document.getElementById('bulkCount');
        var all   = document.getElementById('selectAll');
        var total = document.querySelectorAll('.row-checkbox').length;
        if (selectedIds.length > 0) {
            bar.classList.replace('hidden', 'flex');
            count.textContent = selectedIds.length + ' pengguna dipilih';
        } else {
            bar.classList.replace('flex', 'hidden');
        }
        all.indeterminate = selectedIds.length > 0 && selectedIds.length < total;
        all.checked = selectedIds.length > 0 && selectedIds.length === total;
    };

    window.toggleSelectAll = function (cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = cb.checked; });
        updateSelection();
    };

    window.clearSelection = function () {
        document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = false; });
        document.getElementById('selectAll').checked = false;
        selectedIds = [];
        document.getElementById('bulkBar').classList.replace('flex', 'hidden');
    };

    // ── Modal helpers ─────────────────────────────────────────────────────────
    window.closeModal = function (id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    };
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function showError(elId, msg) {
        var el = document.getElementById(elId);
        document.getElementById(elId + 'Msg').textContent = msg;
        el.classList.remove('hidden'); el.classList.add('flex');
    }
    function hideError(elId) {
        var el = document.getElementById(elId);
        el.classList.add('hidden'); el.classList.remove('flex');
    }
    function setLoading(btnId, spinnerId, loading) {
        document.getElementById(btnId).disabled = loading;
        document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }

    // ── Toast (Replacement with AppPopup) ────────────────────────────────────
    window.showToast = function (type, msg) {
        if (type === 'success') {
            AppPopup.success({ description: msg });
        } else if (type === 'error') {
            AppPopup.error({ description: msg });
        } else if (type === 'warning') {
            AppPopup.warning({ description: msg });
        } else {
            AppPopup.info({ description: msg });
        }
    };

    // ── API helper ────────────────────────────────────────────────────────────
    function api(method, url, data) {
        var opts = {
            method  : method,
            headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        };
        if (data) opts.body = JSON.stringify(data);
        return fetch(url, opts).then(function(r){ return r.json(); });
    }

    // ── Filter form ───────────────────────────────────────────────────────────
    var debounceTimer;
    window.debounceSubmit = function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function(){ document.getElementById('filterForm').submit(); }, 600);
    };
    window.setStatus = function (val) {
        document.getElementById('statusInput').value = val;
        document.querySelectorAll('.status-btn').forEach(function(btn){
            var active = btn.dataset.status === val;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('dark:bg-slate-700', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-800', active);
            btn.classList.toggle('dark:text-white', active);
            btn.classList.toggle('text-slate-500', !active);
            btn.classList.toggle('dark:text-slate-400', !active);
        });
        document.getElementById('filterForm').submit();
    };

    // ── Password toggle ───────────────────────────────────────────────────────
    window.togglePassword = function (inputId, btn) {
        var inp = document.getElementById(inputId);
        inp.type = inp.type === 'password' ? 'text' : 'password';
    };

    // ── CREATE ────────────────────────────────────────────────────────────────
    window.openCreateModal = function () {
        ['createName','createEmail','createPassword'].forEach(function(id){ document.getElementById(id).value = ''; });
        document.getElementById('createIsActive').value = '1';
        document.getElementById('createEmailVerified').value = '1';
        hideError('createError');
        updateCreatePreview();
        openModal('createModal');
    };

    window.updateCreatePreview = function () {
        var name  = document.getElementById('createName').value || 'Nama Pengguna';
        var email = document.getElementById('createEmail').value || 'email@domain.com';
        document.getElementById('createPreviewName').textContent  = name;
        document.getElementById('createPreviewEmail').textContent = email;
        document.getElementById('createAvatar').textContent = name.charAt(0).toUpperCase();
    };

    window.submitCreate = function () {
        var name  = document.getElementById('createName').value.trim();
        var email = document.getElementById('createEmail').value.trim();
        var pass  = document.getElementById('createPassword').value;
        if (!name || !email || !pass) { showError('createError', 'Nama, email, dan password wajib diisi.'); return; }
        hideError('createError');
        setLoading('createSubmitBtn', 'createSpinner', true);
        api('POST', ROUTES.store, {
            name: name, email: email, password: pass,
            is_active: document.getElementById('createIsActive').value === '1',
            email_verified: document.getElementById('createEmailVerified').value === '1',
        }).then(function(res){
            setLoading('createSubmitBtn', 'createSpinner', false);
            if (res.success) { closeModal('createModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
            else { showError('createError', res.message || 'Gagal membuat pengguna.'); }
        }).catch(function(){ setLoading('createSubmitBtn', 'createSpinner', false); showError('createError', 'Terjadi kesalahan server.'); });
    };

    // ── EDIT ──────────────────────────────────────────────────────────────────
    window.openEditModal = function (user) {
        document.getElementById('editUserId').value  = user.id;
        document.getElementById('editName').value    = user.name;
        document.getElementById('editEmail').value   = user.email;
        document.getElementById('editPassword').value = '';
        document.getElementById('editIsActive').value = user.is_active ? '1' : '0';
        document.getElementById('editEmailVerified').value = user.email_verified_at ? '1' : '0';
        document.getElementById('editModalSub').textContent = '#' + String(user.id).padStart(4,'0') + ' · ' + user.email;
        hideError('editError');
        openModal('editModal');
    };

    window.submitEdit = function () {
        var id    = document.getElementById('editUserId').value;
        var name  = document.getElementById('editName').value.trim();
        var email = document.getElementById('editEmail').value.trim();
        if (!name || !email) { showError('editError', 'Nama dan email wajib diisi.'); return; }
        hideError('editError');
        setLoading('editSubmitBtn', 'editSpinner', true);
        api('PUT', ROUTES.update(id), {
            name: name, email: email,
            password: document.getElementById('editPassword').value || null,
            is_active: document.getElementById('editIsActive').value === '1',
            email_verified: document.getElementById('editEmailVerified').value === '1',
        }).then(function(res){
            setLoading('editSubmitBtn', 'editSpinner', false);
            if (res.success) { closeModal('editModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
            else { showError('editError', res.message || 'Gagal menyimpan perubahan.'); }
        }).catch(function(){ setLoading('editSubmitBtn', 'editSpinner', false); showError('editError', 'Terjadi kesalahan server.'); });
    };

    window.sendResetPasswordFromEdit = function () {
        var id = document.getElementById('editUserId').value;
        if (!id) return;
        api('POST', ROUTES.resetPwd(id)).then(function(res){ showToast(res.success ? 'success' : 'error', res.message); });
    };

    // ── DETAIL ────────────────────────────────────────────────────────────────
    window.openDetailModal = function (user, isBlocked, blockCount, blockReason) {
        document.getElementById('detailAvatar').textContent = user.name.charAt(0).toUpperCase();
        document.getElementById('detailName').textContent   = user.name;
        document.getElementById('detailEmail').textContent  = user.email;

        var badge = document.getElementById('detailStatusBadge');
        if (isBlocked) {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">⊘ BLOCKED</span>';
        } else if (user.is_active) {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">● ACTIVE</span>';
        } else {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">○ INACTIVE</span>';
        }

        var fields = [
            ['User ID',       '#' + String(user.id).padStart(4,'0')],
            ['Email Verify',  user.email_verified_at ? '✓ Verified' : '✗ Unverified'],
            ['Terdaftar',     user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '—'],
            ['Login Terakhir',user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('id-ID') : 'Belum pernah'],
            ['IP Terakhir',   user.last_login_ip || '—'],
            ['Total Blokir',  blockCount > 0 ? blockCount + '×' : 'Tidak pernah'],
        ];

        var grid = document.getElementById('detailGrid');
        grid.innerHTML = fields.map(function(f){
            return '<div class="bg-slate-50 dark:bg-slate-800/60  px-3 py-2.5"><p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">' + f[0] + '</p><p class="text-xs font-semibold text-slate-700 dark:text-slate-200 font-mono">' + (f[1] || '—') + '</p></div>';
        }).join('');

        var blockInfo = document.getElementById('detailBlockInfo');
        if (blockCount > 0) {
            blockInfo.classList.remove('hidden');
            document.getElementById('detailBlockText').textContent = 'Pengguna ini telah diblokir sebanyak ' + blockCount + ' kali.';
            document.getElementById('detailBlockReason').textContent = blockReason ? 'Alasan: ' + blockReason : '';
        } else {
            blockInfo.classList.add('hidden');
        }

        document.getElementById('detailEditBtn').onclick = function(){ closeModal('detailModal'); openEditModal(user); };

        var blockBtn = document.getElementById('detailBlockBtn');
        if (isBlocked) {
            blockBtn.textContent = 'Unblokir User';
            blockBtn.className = 'flex-1 py-2 text-xs font-bold  border transition-all bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20';
            blockBtn.onclick = function(){ closeModal('detailModal'); confirmUnblock(user.id, user.name); };
        } else {
            blockBtn.textContent = 'Blokir User';
            blockBtn.className = 'flex-1 py-2 text-xs font-bold  border transition-all bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20';
            blockBtn.onclick = function(){ closeModal('detailModal'); openBlockModal(user.id, user.name); };
        }

        openModal('detailModal');
    };

    // ── BLOCK ─────────────────────────────────────────────────────────────────
    window.openBlockModal = function (userId, userName) {
        document.getElementById('blockUserId').value = userId;
        document.getElementById('blockUserName').value = userName;
        document.getElementById('blockModalSub').textContent = userName;
        document.getElementById('blockReason').value = '';
        document.getElementById('blockUntil').value = '';
        openModal('blockModal');
    };

    window.submitBlock = function () {
        var id     = document.getElementById('blockUserId').value;
        var reason = document.getElementById('blockReason').value;
        var until  = document.getElementById('blockUntil').value;
        if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
        api('POST', ROUTES.block(id), { reason: reason, blocked_until: until || null }).then(function(res){
            closeModal('blockModal');
            showToast(res.success ? 'info' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    window.confirmUnblock = function (userId, userName) {
        AppPopup.warning({
            title: 'Unblokir Pengguna?',
            description: 'Apakah Anda yakin ingin membuka blokir untuk ' + userName + '?',
            confirmText: 'Ya, Unblokir',
            cancelText: 'Batal',
            onConfirm: function() {
                api('POST', ROUTES.unblock(userId)).then(function(res){
                    showToast(res.success ? 'success' : 'error', res.message);
                    if (res.success) setTimeout(function(){ location.reload(); }, 800);
                });
            }
        });
    };

    // ── DELETE ────────────────────────────────────────────────────────────────
    window.confirmDelete = function (userId, userName) {
        AppPopup.confirm({
            title: 'Hapus Pengguna?',
            description: 'Akun ' + userName + ' akan dihapus. Tindakan ini tidak dapat dibatalkan.',
            confirmText: 'Ya, Hapus',
            onConfirm: function() {
                api('DELETE', ROUTES.destroy(userId)).then(function(res){
                    showToast(res.success ? 'success' : 'error', res.message);
                    if (res.success) setTimeout(function(){ location.reload(); }, 800);
                });
            }
        });
    };

    // ── RESET PASSWORD ────────────────────────────────────────────────────────
    window.sendResetPassword = function (userId, email) {
        AppPopup.info({
            title: 'Reset Password?',
            description: 'Kirim link reset password ke ' + email + '?',
            confirmText: 'Kirim Link',
            cancelText: 'Batal',
            onConfirm: function() {
                api('POST', ROUTES.resetPwd(userId)).then(function(res){
                    showToast(res.success ? 'success' : 'error', res.message);
                });
            }
        });
    };

    // ── BULK ──────────────────────────────────────────────────────────────────
    window.openBulkBlockModal = function () {
        if (selectedIds.length === 0) return;
        document.getElementById('bulkBlockCount').textContent = selectedIds.length + ' pengguna akan diblokir';
        document.getElementById('bulkBlockReason').value = '';
        openModal('bulkBlockModal');
    };

    window.submitBulkBlock = function () {
        var reason = document.getElementById('bulkBlockReason').value;
        if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
        api('POST', ROUTES.bulk, { action: 'block', user_ids: selectedIds, reason: reason }).then(function(res){
            closeModal('bulkBlockModal');
            showToast(res.success ? 'info' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    window.bulkAction = function (action) {
        if (selectedIds.length === 0) return;
        var labels = { unblock: 'unblokir', delete: 'hapus' };
        var actionLabel = labels[action] || action;
        var type = action === 'delete' ? 'confirm' : 'warning';
        
        AppPopup.show({
            type: type,
            title: 'Aksi Massal: ' + actionLabel.toUpperCase() + '?',
            description: 'Apakah Anda yakin ingin menjalankan aksi ' + actionLabel + ' pada ' + selectedIds.length + ' pengguna yang dipilih?',
            confirmText: 'Ya, Jalankan',
            cancelText: 'Batal',
            onConfirm: function() {
                api('POST', ROUTES.bulk, { action: action, user_ids: selectedIds }).then(function(res){
                    showToast(res.success ? 'success' : 'error', res.message);
                    if (res.success) setTimeout(function(){ location.reload(); }, 800);
                });
            }
        });
    };

    // ── Close modal on backdrop click ─────────────────────────────────────────
    ['createModal','editModal','detailModal','blockModal'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e){ if (e.target === el) closeModal(id); });
    });
}());
</script>

@endsection