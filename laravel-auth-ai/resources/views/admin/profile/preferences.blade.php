@extends('admin.profile.layout', ['title' => 'Preferensi'])

@section('profile-content')
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-300">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-sliders text-slate-400 text-xs"></i>
            Preferensi Akun
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">Atur zona waktu dan perilaku verifikasi dua langkah.</p>
    </div>

    <div class="p-8">
        <form action="{{ route('dashboard.profile.preferences.update') }}" method="POST" class="space-y-8">
            @csrf

            <!-- TIMEZONE -->
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Zona Waktu (Timezone)</label>
                <div class="relative group">
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
                    Zona waktu digunakan untuk menampilkan waktu pada log aktivitas dan notifikasi.
                </p>
            </div>

            <!-- OTP PREFERENCE -->
            <div class="space-y-3">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Verifikasi Dua Langkah (OTP)</label>
                <p class="text-xs text-slate-400 -mt-1">Tentukan kapan sistem harus meminta kode OTP saat login.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="otp-grid">
                    @php
                        $otpOptions = [
                            ['value' => 'always',   'icon' => 'fa-shield-check',  'label' => 'Selalu',     'desc' => 'OTP diminta di setiap login tanpa pengecualian.'],
                            ['value' => 'system',   'icon' => 'fa-robot',         'label' => 'Otomatis',   'desc' => 'Sistem memutuskan berdasarkan faktor risiko.'],
                            ['value' => 'disabled', 'icon' => 'fa-shield-xmark',  'label' => 'Nonaktif',   'desc' => 'OTP tidak pernah diminta (tidak direkomendasikan).'],
                        ];
                        $currentOtp = old('otp_preference', Auth::user()->otp_preference);
                    @endphp

                    @foreach($otpOptions as $opt)
                        @php $isChecked = $currentOtp === $opt['value']; @endphp
                        <label class="cursor-pointer" data-otp-card="{{ $opt['value'] }}">
                            <input type="radio" name="otp_preference" value="{{ $opt['value'] }}"
                                class="otp-radio hidden" {{ $isChecked ? 'checked' : '' }}>
                            <div class="w-full p-4 rounded-lg border transition-all select-none
                                {{ $isChecked
                                    ? 'border-violet-500 bg-violet-50/50 dark:bg-violet-900/10'
                                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                data-otp-box="{{ $opt['value'] }}">
                                <i class="fa-solid {{ $opt['icon'] }} text-lg mb-2 block transition-colors {{ $isChecked ? 'text-violet-500' : 'text-slate-300 dark:text-slate-600' }}" data-otp-icon="{{ $opt['value'] }}"></i>
                                <p class="text-xs font-bold transition-colors {{ $isChecked ? 'text-violet-700 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300' }}" data-otp-label="{{ $opt['value'] }}">{{ $opt['label'] }}</p>
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

@push('scripts')
<script>
(function () {
    const radios = document.querySelectorAll('.otp-radio');
    const ACTIVE_BOX   = ['border-violet-500', 'bg-violet-50/50', 'dark:bg-violet-900/10'];
    const IDLE_BOX     = ['border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800', 'hover:border-slate-300', 'dark:hover:border-slate-600'];
    const ACTIVE_ICON  = ['text-violet-500'];
    const IDLE_ICON    = ['text-slate-300', 'dark:text-slate-600'];
    const ACTIVE_LABEL = ['text-violet-700', 'dark:text-violet-400'];
    const IDLE_LABEL   = ['text-slate-700', 'dark:text-slate-300'];

    function applyState() {
        radios.forEach(radio => {
            const val = radio.value;
            const box   = document.querySelector(`[data-otp-box="${val}"]`);
            const icon  = document.querySelector(`[data-otp-icon="${val}"]`);
            const label = document.querySelector(`[data-otp-label="${val}"]`);
            const active = radio.checked;

            if(!box || !icon || !label) return;

            ACTIVE_BOX.forEach(c => box.classList.toggle(c, active));
            IDLE_BOX.forEach(c => box.classList.toggle(c, !active));
            ACTIVE_ICON.forEach(c => icon.classList.toggle(c, active));
            IDLE_ICON.forEach(c => icon.classList.toggle(c, !active));
            ACTIVE_LABEL.forEach(c => label.classList.toggle(c, active));
            IDLE_LABEL.forEach(c => label.classList.toggle(c, !active));
        });
    }

    radios.forEach(radio => radio.addEventListener('change', applyState));
    applyState(); // Initialize on load
})();
</script>
@endpush
@endsection
