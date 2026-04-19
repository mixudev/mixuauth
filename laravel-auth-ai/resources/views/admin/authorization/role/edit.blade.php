@extends('layouts.app-dashboard')

@section('title', 'Edit Role - ' . $role->display_name)
@section('page-title', 'Edit Role')
@section('page-sub', 'Edit role "' . $role->display_name . '" dan permissions-nya')

@section('content')

<div class="max-w-2xl">
    {{-- ─────────────────────────────────────────────────────────────────────────
         FORM CARD
    ───────────────────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        
        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Edit Role</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Ubah informasi role dan permissions</p>

        @if(in_array($role->name, ['super-admin', 'admin', 'user']))
        <div class="mb-6 p-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 17.657l1.414-1.414m2.828 0l1.414 1.414M9.172 9.172L7.757 7.757m2.828 0L11.414 9.172M14 11h.01M14 15h.01"/>
                </svg>
                <div>
                    <p class="font-semibold text-amber-900 dark:text-amber-100">Role Bawaan Sistem</p>
                    <p class="text-sm text-amber-800 dark:text-amber-200">Hanya permission yang bisa diubah untuk role ini.</p>
                </div>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('dashboard.roles.update', $role->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Role Name (Read-only for system roles) --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Nama Role <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $role->name) }}"
                    class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') ring-2 ring-red-500 @enderror"
                    required
                />
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Deskripsi
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Jelaskan tujuan dan fungsi role ini..."
                    class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') ring-2 ring-red-500 @enderror"
                >{{ old('description', $role->description) }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Permissions Section --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    Permissions
                </label>
                
                <div class="space-y-4">
                    @foreach($permissions as $group => $perms)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <input
                                type="checkbox"
                                id="group-{{ $group }}"
                                class="group-toggle w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                data-group="{{ $group }}"
                            />
                            <label for="group-{{ $group }}" class="ml-2 font-semibold text-slate-700 dark:text-slate-300 cursor-pointer uppercase text-xs tracking-wide">
                                {{ $group }}
                            </label>
                            <span class="ml-auto text-xs text-slate-500 dark:text-slate-400">
                                {{ count($perms) }} permission
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-4">
                            @foreach($perms as $permission)
                            <label class="flex items-start gap-2.5 p-2 rounded hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer group-{{ $group }}">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                    class="mt-1 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $permission->name }}</div>
                                    @if($permission->description)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $permission->description }}</div>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-4">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                >
                    Simpan Perubahan
                </button>
                <a
                    href="{{ route('dashboard.roles.index') }}"
                    class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Update group toggle state based on selected permissions
function updateGroupToggles() {
    document.querySelectorAll('.group-toggle').forEach(toggle => {
        const group = toggle.dataset.group;
        const allCheckboxes = document.querySelectorAll(`.group-${group} input[type="checkbox"]`);
        const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
        toggle.checked = checkedCount === allCheckboxes.length;
        toggle.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
    });
}

// Toggle all permissions in a group
document.querySelectorAll('.group-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const group = this.dataset.group;
        const checked = this.checked;
        document.querySelectorAll(`.group-${group} checkbox[type="checkbox"]`).forEach(checkbox => {
            checkbox.checked = checked;
        });
    });
});

// Track individual permission changes
document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateGroupToggles);
});

// Initialize on page load
updateGroupToggles();
</script>

@endsection
