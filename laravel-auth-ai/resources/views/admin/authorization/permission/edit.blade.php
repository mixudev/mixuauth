@extends('layouts.app-dashboard')

@section('title', 'Edit Permission - ' . $permission->name)
@section('page-title', 'Edit Permission')
@section('page-sub', 'Edit permission "' . $permission->name . '"')

@section('content')

<div class="max-w-2xl">
    {{-- ─────────────────────────────────────────────────────────────────────────
         FORM CARD
    ───────────────────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        
        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Edit Permission</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Ubah informasi permission</p>

        <form method="POST" action="{{ route('dashboard.permissions.update', $permission->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Permission Name (Read-only) --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Nama Permission
                </label>
                <input
                    type="text"
                    id="name"
                    value="{{ $permission->name }}"
                    disabled
                    class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-mono text-sm cursor-not-allowed opacity-60"
                />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nama permission tidak dapat diubah</p>
            </div>

            {{-- Group --}}
            <div>
                <label for="group" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Grup <span class="text-red-500">*</span>
                </label>
                <select name="group" id="group" class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('group') ring-2 ring-red-500 @enderror">
                    <option value="">Pilih Grup</option>
                    @foreach($groups as $grp)
                    <option value="{{ $grp }}" {{ old('group', $permission->group) === $grp ? 'selected' : '' }}>{{ ucfirst($grp) }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Grup digunakan untuk mengorganisir permission</p>
                @error('group')
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
                    placeholder="Jelaskan apa yang dilakukan permission ini..."
                    class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') ring-2 ring-red-500 @enderror"
                >{{ old('description', $permission->description) }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Related Information --}}
            @php
                $roleCount = $permission->roles()->count();
            @endphp
            @if($roleCount > 0)
            <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-blue-900 dark:text-blue-100">Digunakan oleh {{ $roleCount }} Role</p>
                        <p class="text-sm text-blue-800 dark:text-blue-200">Permission ini diassign ke {{ $roleCount }} role. Perubahan akan memengaruhi semua role tersebut.</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-3 pt-4">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                >
                    Simpan Perubahan
                </button>
                <a
                    href="{{ route('dashboard.permissions.index') }}"
                    class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
