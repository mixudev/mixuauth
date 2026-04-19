@extends('identity::profile.layout')

@section('profile-content')
@php
    $currentOtp = old('otp_preference', Auth::user()->otp_preference);
    $otpOptions = [
        ['value' => 'always',   'icon' => 'fa-shield-check', 'label' => 'Selalu',   'desc' => 'OTP diminta di setiap login tanpa pengecualian.'],
        ['value' => 'system',   'icon' => 'fa-robot',        'label' => 'Otomatis', 'desc' => 'Sistem memutuskan berdasarkan faktor risiko.'],
        ['value' => 'disabled', 'icon' => 'fa-shield-xmark', 'label' => 'Nonaktif', 'desc' => 'OTP tidak pernah diminta (tidak direkomendasikan).'],
    ];
@endphp

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-sliders text-slate-400 text-xs"></i>
            Preferensi Akun
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">Atur zona waktu dan perilaku verifikasi OTP.</p>
    </div>

    <div class="p-8">
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800/50 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('dashboard.profile.preferences.update') }}" method="POST" class="space-y-8">
            @csrf

            <!-- TIMEZONE -->
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Zona Waktu (Timezone)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-earth-asia text-slate-400 text-[10px]"></i>
                    </div>
                    <select name="timezone"
                        class="w-full h-11 pl-9 pr-10 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition-all outline-none appearance-none">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', Auth::user()->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-slate-400 text-[9px]"></i>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-[9px]"></i>
                    Digunakan untuk menampilkan waktu pada log dan notifikasi.
                </p>
            </div>

            <!-- OTP PREFERENCE -->
            <div class="space-y-3">
                <div>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Verifikasi Dua Langkah (OTP)</label>
                    <p class="text-xs text-slate-400 mt-1">Tentukan kapan sistem meminta kode OTP saat login.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($otpOptions as $opt)
                        @php $isChecked = $currentOtp === $opt['value']; @endphp
                        <label class="cursor-pointer" data-otp-card="{{ $opt['value'] }}">
                            <input type="radio" name="otp_preference" value="{{ $opt['value'] }}"
                                class="otp-radio hidden" {{ $isChecked ? 'checked' : '' }}>
                            <div class="w-full p-4 rounded-lg border transition-all select-none
                                        {{ $isChecked ? 'border-violet-500 bg-violet-50/50 dark:bg-violet-900/10' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}"
                                 data-otp-box="{{ $opt['value'] }}">
                                <i class="fa-solid {{ $opt['icon'] }} text-lg mb-2 block transition-colors
                                          {{ $isChecked ? 'text-violet-500' : 'text-slate-300 dark:text-slate-600' }}"
                                   data-otp-icon="{{ $opt['value'] }}"></i>
                                <p class="text-xs font-bold transition-colors
                                          {{ $isChecked ? 'text-violet-700 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300' }}"
                                   data-otp-label="{{ $opt['value'] }}">{{ $opt['label'] }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">{{ $opt['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('otp_preference') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="h-10 px-6 rounded-lg bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-save text-[10px]"></i>
                    Simpan Preferensi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
