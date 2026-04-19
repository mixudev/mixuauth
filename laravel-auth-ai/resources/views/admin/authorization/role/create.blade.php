@extends('layouts.app-dashboard')

@section('title', 'Create Role')
@section('page-title', 'Tambah Role')
@section('page-sub', 'Buat role baru dengan set permission yang diinginkan')

@section('content')

<div class="max-w-2xl">
    {{-- ─────────────────────────────────────────────────────────────────────────
         FORM CARD
    ───────────────────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        
        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Tambah Role Baru</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Buat role dengan set permission yang sesuai</p>

        <form method="POST" action="{{ route('dashboard.roles.store') }}" class="space-y-6">
            @csrf

            {{-- Role Name --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Nama Role <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="mis: Content Manager"
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
                >{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Permissions Section --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    Permissions <span class="text-slate-400">(opsional)</span>
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
                    Buat Role
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
// Toggle group permissions
document.querySelectorAll('.group-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const group = this.dataset.group;
        const checked = this.checked;
        document.querySelectorAll(`.group-${group} input[type="checkbox"]`).forEach(checkbox => {
            checkbox.checked = checked;
        });
    });
});
</script>

@endsection
