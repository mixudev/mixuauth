@extends('layouts.app-dashboard')

@section('title', 'Edit Role: ' . $role->name)
@section('page-title', 'Edit Role')
@section('page-sub', 'Modifikasi identitas role dan hak akses granular sistem.')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('dashboard.access-management.index', ['tab' => 'roles']) }}" class="flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Manajemen
        </a>
    </div>

    <form action="{{ route('dashboard.access-management.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Left: Identity --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-6 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-6 pb-2 border-b border-slate-100 dark:border-slate-800">
                        Identitas Role
                    </h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Role</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="Contoh: Manager Operasional" class="w-full px-4 py-2 text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-slate-700 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all">
                            @error('name') <p class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Deskripsi</label>
                            <textarea name="description" rows="4" placeholder="Berikan penjelasan fungsi role ini..." class="w-full px-4 py-2 text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-slate-700 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all">{{ old('description', $role->description) }}</textarea>
                            @error('description') <p class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Role Slug</label>
                            <div class="px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-[11px] font-mono text-slate-500">
                                {{ $role->slug }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/10 rounded p-6">
                    <p class="text-[11px] text-indigo-600 dark:text-indigo-400 leading-relaxed font-medium">
                        <svg class="w-4 h-4 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Memperbarui izin role akan berdampak langsung pada seluruh user yang memiliki role ini.
                    </p>
                </div>
            </div>

            {{-- Right: Permissions --}}
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-8 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-widest">
                            Konfigurasi Izin (Permissions)
                        </h3>
                        <div class="flex gap-4">
                            <button type="button" onclick="toggleAllPermissions(true)" class="text-[10px] font-bold text-indigo-600 hover:underline">Pilih Semua</button>
                            <button type="button" onclick="toggleAllPermissions(false)" class="text-[10px] font-bold text-slate-400 hover:underline">Hapus Semua</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($permissions as $group => $perms)
                        <div class="border border-slate-100 dark:border-slate-800 rounded p-4 bg-slate-50/30 dark:bg-slate-800/20">
                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                                <h4 class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-tight">{{ ucfirst($group) }}</h4>
                                <input type="checkbox" class="group-toggle w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500" data-group="{{ $group }}" onclick="toggleGroup(this)">
                            </div>
                            <div class="grid grid-cols-1 gap-2.5">
                                @foreach($perms as $perm)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-checkbox w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 dark:border-slate-700 dark:bg-slate-900 focus:ring-indigo-500" data-group="{{ $group }}" {{ in_array($perm->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors">{{ $perm->name }}</span>
                                        <span class="text-[9px] text-slate-400">{{ $perm->description }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8">
                    <a href="{{ route('dashboard.access-management.index', ['tab' => 'roles']) }}" class="px-6 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">Batal</a>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                        <span>Perbarui Role</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleGroup(toggle) {
        const group = toggle.dataset.group;
        document.querySelectorAll(`.perm-checkbox[data-group="${group}"]`).forEach(cb => cb.checked = toggle.checked);
    }

    function toggleAllPermissions(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
        document.querySelectorAll('.group-toggle').forEach(cb => {
            cb.checked = checked;
            cb.indeterminate = false;
        });
    }

    function syncAllGroupToggles() {
        document.querySelectorAll('.group-toggle').forEach(toggle => {
            const group = toggle.dataset.group;
            const allInGroup = document.querySelectorAll(`.perm-checkbox[data-group="${group}"]`);
            const allChecked = Array.from(allInGroup).every(cb => cb.checked);
            const someChecked = Array.from(allInGroup).some(cb => cb.checked);
            toggle.checked = allChecked;
            toggle.indeterminate = !allChecked && someChecked;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.addEventListener('change', syncAllGroupToggles);
        });
        syncAllGroupToggles();
    });
</script>
@endsection
