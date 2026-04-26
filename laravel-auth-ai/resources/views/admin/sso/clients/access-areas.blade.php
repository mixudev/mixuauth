@extends('layouts.app-dashboard')

@section('title', 'Kelola Access Area — ' . $client->name)
@section('page-title', 'Kelola Access Area Klien')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('sso.clients.index') }}"
                   class="text-slate-400 hover:text-indigo-500 transition-colors text-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                    Kelola Access Area
                </h2>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 ml-7">
                Tentukan access area yang <strong>wajib dimiliki user</strong> agar bisa login ke
                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $client->name }}</span>.
            </p>
        </div>

        {{-- Status Badge --}}
        <div class="flex items-center gap-2">
            @if($client->is_active)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                </span>
            @endif
        </div>
    </div>

    {{-- Info Box: Open vs Restricted --}}
    @if(count($assignedIds) === 0)
    <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300">
        <i class="fa-solid fa-circle-info mt-0.5 shrink-0"></i>
        <div>
            <p class="font-semibold mb-0.5">Klien Ini Bersifat Open (Tidak Ada Restriksi Area)</p>
            <p class="text-blue-600/80 dark:text-blue-300/70 text-xs">
                Saat ini semua user aktif dapat login ke aplikasi ini tanpa syarat access area.
                Pilih access area di bawah untuk membatasi akses.
            </p>
        </div>
    </div>
    @else
    <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 rounded-xl p-4 text-sm text-amber-700 dark:text-amber-300">
        <i class="fa-solid fa-shield-halved mt-0.5 shrink-0"></i>
        <div>
            <p class="font-semibold mb-0.5">Klien Ini Memiliki Restriksi Access Area</p>
            <p class="text-amber-600/80 dark:text-amber-300/70 text-xs">
                User harus memiliki <strong>SEMUA</strong> area yang dipilih di bawah agar bisa login ke aplikasi ini.
            </p>
        </div>
    </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('sso.clients.sync-access-areas', $client) }}" method="POST" id="syncAreaForm">
        @csrf

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">

            {{-- Toolbar --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-800/40">
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Pilih Required Access Areas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Centang area yang WAJIB dimiliki user untuk mengakses <strong>{{ $client->name }}</strong>.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="btnSelectAll"
                            class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Pilih Semua
                    </button>
                    <button type="button" id="btnClearAll"
                            class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Hapus Semua
                    </button>
                </div>
            </div>

            {{-- Area List --}}
            @if($allAreas->count() > 0)
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @foreach($allAreas as $area)
                    @php $isChecked = in_array($area->id, $assignedIds); @endphp
                    <label for="area_{{ $area->id }}"
                           class="flex items-center gap-4 px-6 py-4 cursor-pointer group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">

                        <input type="checkbox"
                               id="area_{{ $area->id }}"
                               name="access_area_ids[]"
                               value="{{ $area->id }}"
                               {{ $isChecked ? 'checked' : '' }}
                               class="area-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 transition-all cursor-pointer">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-slate-800 dark:text-slate-200 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                    {{ $area->name }}
                                </span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    {{ $area->slug }}
                                </span>
                                @if($isChecked)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                                        <i class="fa-solid fa-check text-[8px]"></i> Dipilih
                                    </span>
                                @endif
                            </div>
                            @if($area->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $area->description }}</p>
                            @endif
                        </div>

                        {{-- User count hint --}}
                        <span class="text-xs text-slate-400 shrink-0">
                            {{ $area->users_count ?? 0 }} user
                        </span>
                    </label>
                @endforeach
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mb-3">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Belum Ada Access Area</h3>
                <p class="text-xs text-slate-400 mt-1">
                    Buat access area terlebih dahulu di menu
                    <a href="{{ route('sso.access-areas.index') }}" class="text-indigo-500 hover:underline">Access Areas</a>.
                </p>
            </div>
            @endif

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-400">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    Tidak memilih area = <strong>Open Client</strong> (semua user aktif boleh login).
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('sso.clients.index') }}"
                       class="px-4 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="button" onclick="confirmSync()"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium rounded-lg transition-all shadow-sm shadow-indigo-500/20">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Summary Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
        <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
            <i class="fa-solid fa-diagram-project mr-1.5"></i>Ringkasan Konfigurasi
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="selectedCount">{{ count($assignedIds) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Area Dipilih</div>
            </div>
            <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <div class="text-2xl font-bold text-slate-700 dark:text-slate-200">{{ $allAreas->count() }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Total Area Tersedia</div>
            </div>
            <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg" id="accessTypeCard">
                <div class="text-sm font-bold {{ count($assignedIds) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}" id="accessTypeLabel">
                    {{ count($assignedIds) > 0 ? 'Restricted' : 'Open' }}
                </div>
                <div class="text-xs text-slate-500 mt-0.5">Tipe Akses</div>
            </div>
        </div>
    </div>

</div>

<script>
    const checkboxes = document.querySelectorAll('.area-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const accessTypeLabel = document.getElementById('accessTypeLabel');

    function updateCount() {
        const count = document.querySelectorAll('.area-checkbox:checked').length;
        selectedCount.textContent = count;
        if (count > 0) {
            accessTypeLabel.textContent = 'Restricted';
            accessTypeLabel.className = 'text-sm font-bold text-amber-600 dark:text-amber-400';
        } else {
            accessTypeLabel.textContent = 'Open';
            accessTypeLabel.className = 'text-sm font-bold text-emerald-600 dark:text-emerald-400';
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    document.getElementById('btnSelectAll').addEventListener('click', () => {
        checkboxes.forEach(cb => cb.checked = true);
        updateCount();
    });

    document.getElementById('btnClearAll').addEventListener('click', () => {
        checkboxes.forEach(cb => cb.checked = false);
        updateCount();
    });

    function confirmSync() {
        const count = document.querySelectorAll('.area-checkbox:checked').length;
        const msg = count > 0
            ? `Set ${count} access area sebagai syarat login ke {{ addslashes($client->name) }}?`
            : `Hapus semua restriksi? Klien akan menjadi OPEN (semua user aktif bisa login).`;

        AppPopup.confirm({
            title: 'Simpan Konfigurasi Access Area?',
            description: msg,
            confirmText: 'Ya, Simpan',
            onConfirm: () => document.getElementById('syncAreaForm').submit()
        });
    }
</script>
@endsection
