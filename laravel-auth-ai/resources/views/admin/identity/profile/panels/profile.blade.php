@extends('identity::profile.layout')

@section('profile-content')
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-address-card text-slate-400 text-xs"></i>
            Informasi Dasar
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">Perbarui nama dan foto profil Anda.</p>
    </div>

    <div class="p-8">
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800/50 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-7">
            @csrf

            <!-- AVATAR UPLOAD -->
            <div class="flex flex-col md:flex-row items-center gap-6 p-6 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700">
                <div class="relative shrink-0">
                    <div class="w-24 h-24 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm">
                        <img id="avatarPreview" src="{{ Auth::user()->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                    </div>
                    <label for="avatar_file" class="absolute -bottom-2 -right-2 w-8 h-8 rounded-lg bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center cursor-pointer hover:bg-slate-50 border border-slate-200 dark:border-slate-700 shadow-sm transition-all">
                        <i class="fa-solid fa-camera text-[10px]"></i>
                        <input type="file" id="avatar_file" name="avatar_file" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </label>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Foto Profil</h4>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Format PNG, JPG, atau WEBP. Ukuran maks. 2MB, dimensi min. 100×100 px.</p>
                    @error('avatar_file')
                        <p class="text-[11px] text-red-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- NAME -->
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}"
                        class="w-full h-11 pl-9 pr-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none">
                </div>
                @error('name') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- EMAIL (read-only) -->
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-slate-400 text-[10px]"></i>
                    </div>
                    <input type="email" value="{{ Auth::user()->email }}" readonly
                        class="w-full h-11 pl-9 pr-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 text-sm text-slate-500 cursor-not-allowed outline-none">
                </div>
                <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-[9px]"></i>
                    Email tidak dapat diubah langsung.
                </p>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="h-10 px-6 rounded-lg bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-save text-[10px]"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
