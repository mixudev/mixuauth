@extends('layouts.app-dashboard')

@section('title', 'Create Permission')
@section('page-title', 'Tambah Permission')
@section('page-sub', 'Buat permission baru untuk digunakan dalam sistem')

@section('content')

<div class="max-w-2xl">
    {{-- ─────────────────────────────────────────────────────────────────────────
         FORM CARD
    ───────────────────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        
        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Tambah Permission Baru</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Buat permission yang bisa diassign ke role</p>

        <form method="POST" action="{{ route('dashboard.permissions.store') }}" class="space-y-6">
            @csrf

            {{-- Permission Name --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Nama Permission <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="mis: users.view, users.edit, users.delete"
                    class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') ring-2 ring-red-500 @enderror font-mono text-sm"
                    required
                />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Format: <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">module.action</code></p>
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Group --}}
            <div>
                <label for="group" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Grup <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select name="group" id="group" class="flex-1 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('group') ring-2 ring-red-500 @enderror">
                        <option value="">Pilih Grup atau Buat Baru</option>
                        @foreach($groups as $grp)
                        <option value="{{ $grp }}" {{ old('group') === $grp ? 'selected' : '' }}>{{ ucfirst($grp) }}</option>
                        @endforeach
                    </select>
                </div>
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
                >{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-4">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                >
                    Buat Permission
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
