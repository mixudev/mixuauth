@extends('layouts.app-dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

{{-- Base URL tanpa query string, digunakan oleh JS --}}
@php $profileBaseUrl = route('dashboard.profile.show'); @endphp

@section('content')
<div class="max-w-6xl mx-auto" id="profile-root">

    <!-- ════ TOP HEADER ════ -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
        <!-- Banner -->
        <div class="h-32 bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-800/60 border-b border-slate-100 dark:border-slate-800/60 relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.06]" style="background-image:radial-gradient(circle,#64748b 1px,transparent 1px);background-size:20px 20px"></div>
        </div>

        <div class="px-6 md:px-8">
            <!-- Avatar & Name -->
            <div class="flex flex-col md:flex-row items-center md:items-end -mt-12 mb-4 gap-4">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-5 text-center md:text-left">
                    <div class="w-24 h-24 rounded-lg bg-white dark:bg-slate-900 p-1 border border-slate-200 dark:border-slate-700 shadow-sm relative z-10">
                        <div class="w-full h-full rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden">
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar_url }}" class="w-full h-full object-cover" id="header-avatar-img">
                            @else
                                <span class="text-3xl font-bold text-slate-500 dark:text-slate-400">{{ substr(Auth::user()->name,0,1) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="pb-1">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white" id="header-name">{{ Auth::user()->name }}</h2>
                        <p class="text-[13px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Horizontal Navigation (panel basis via ?panel=xxx) -->
            <nav class="flex overflow-x-auto border-t border-slate-100 dark:border-slate-800/60 hide-scrollbar" id="profile-nav">
                @php
                    $currentPanel = request()->input('panel','profile');
                    $navItems = [
                        ['panel' => 'profile',      'icon' => 'fa-user-gear',         'label' => 'Profil'],
                        ['panel' => 'security',     'icon' => 'fa-shield-halved',     'label' => 'Keamanan'],
                        ['panel' => 'preferences',  'icon' => 'fa-sliders',           'label' => 'Preferensi'],
                        ['panel' => 'devices',      'icon' => 'fa-laptop',            'label' => 'Perangkat'],
                        ['panel' => 'activity',     'icon' => 'fa-clock-rotate-left', 'label' => 'Log Aktivitas'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    <a href="{{ $profileBaseUrl }}?panel={{ $item['panel'] }}"
                       data-panel="{{ $item['panel'] }}"
                       class="profile-nav-link whitespace-nowrap px-3 py-4 border-b-2 text-[13px] font-semibold transition-colors mr-2 hover:text-slate-900 dark:hover:text-white
                              {{ $currentPanel === $item['panel'] ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600' }}">
                        <i class="fa-solid {{ $item['icon'] }} mr-1.5 text-xs"></i>{{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- ════ TWO-COLUMN BODY ════ -->
    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Sidebar -->
        <aside class="w-full lg:w-64 shrink-0">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-8">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-address-card text-slate-400 dark:text-slate-500"></i> Data Akun
                    </h3>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Peran Akses</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">Administrator</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ Auth::user()->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Zona Waktu</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ Auth::user()->timezone ?? 'UTC' }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider">MFA</p>
                            @if(Auth::user()->mfa_enabled)
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-slate-100 text-slate-500 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">Tidak Aktif</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider">Email</p>
                            @if(Auth::user()->email_verified_at)
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">Terverifikasi</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-500 border border-amber-200 dark:border-amber-800/50">Belum</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Panel -->
        <main class="flex-1 min-w-0 " id="profile-panel">
            @yield('profile-content')
        </main>
    </div>

    <!-- ════ HIDDEN: MFA Setup Modal (always in DOM so JS always finds it) ════ -->
    <div id="mfaSetupModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative min-h-screen flex items-center justify-center p-6">
            <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Setup Authenticator</h3>
                        <button onclick="closeMfaModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Step 1: Scan QR -->
                    <div id="mfaStep1" class="space-y-6">
                        <div class="text-center space-y-5">
                            <div id="mfaQrPlaceholder" class="mx-auto w-48 h-48 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <div class="animate-spin h-7 w-7 border-2 border-slate-400 border-t-transparent rounded-full"></div>
                            </div>
                            <div class="py-3 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mb-1">Manual Secret Key</p>
                                <code id="mfaSecretText" class="block text-sm font-mono font-bold text-slate-800 dark:text-white tracking-widest uppercase">--------</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Konfirmasi 6-Digit Kode</label>
                            <input type="text" id="mfaConfirmCode" placeholder="xxxxxx" maxlength="6"
                                class="w-full h-12 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center font-mono text-2xl tracking-[0.5em] focus:border-slate-400 focus:ring-1 focus:ring-slate-400 outline-none transition-all">
                            <p id="mfaSetupError" class="hidden text-[10px] text-red-500 ml-1 font-bold"></p>
                        </div>
                        <button onclick="confirmMfaSetup()" id="mfaConfirmBtn"
                            class="w-full h-11 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                            Verifikasi & Aktifkan
                        </button>
                    </div>

                    <!-- Step 2: Backup Codes -->
                    <div id="mfaStep2" class="hidden space-y-6">
                        <div class="text-center">
                            <div class="w-14 h-14 bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-600 mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                    <path d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Berhasil Diaktifkan!</h4>
                            <p class="text-xs text-slate-500 mt-2">Simpan kode cadangan ini di tempat yang aman.</p>
                        </div>
                        <div id="backupCodesList" class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-200 dark:border-slate-700"></div>
                        <button onclick="window.location.reload()" class="w-full h-11 rounded-xl bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white font-semibold text-sm transition-all">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════ HIDDEN: MFA Disable Modal ════ -->
    <div id="mfaDisableModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative min-h-screen flex items-center justify-center p-6">
            <div class="relative bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800">
                <div class="p-8">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-red-50 border border-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center text-red-500 mx-auto mb-4">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Matikan MFA?</h3>
                        <p class="text-xs text-slate-500 mt-2">Konfirmasi dengan kata sandi Anda untuk melanjutkan.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kata Sandi</label>
                            <input type="password" id="mfaDisablePassword" placeholder="••••••••"
                                class="w-full h-11 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:border-red-400 focus:ring-1 focus:ring-red-400 outline-none transition-all">
                            <p id="mfaDisableError" class="hidden text-[10px] text-red-500 font-bold"></p>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="closeDisableMfaModal()" class="flex-1 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-xs font-semibold transition-all">Batal</button>
                            <button onclick="confirmDisableMfa()" id="mfaDisableConfirmBtn" class="flex-1 h-10 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-sm transition-all">Ya, Matikan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for password reset --}}
<form id="reset-link-form" action="{{ route('dashboard.profile.password.reset_request') }}" method="POST" class="hidden">@csrf</form>

@endsection

@push('scripts')
<script>
(function () {
'use strict';

// ─── Config ─────────────────────────────────────────────────────────────────
const BASE_URL   = '{{ $profileBaseUrl }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// ─── Panel Navigation (AJAX) ─────────────────────────────────────────────────
const panel  = document.getElementById('profile-panel');
const navLinks = document.querySelectorAll('.profile-nav-link');

function setActiveNav(activePanel) {
    navLinks.forEach(link => {
        const isActive = link.dataset.panel === activePanel;
        link.classList.toggle('border-violet-500',            isActive);
        link.classList.toggle('text-violet-600',              isActive);
        link.classList.toggle('dark:text-violet-400',         isActive);
        link.classList.toggle('border-transparent',           !isActive);
        link.classList.toggle('text-slate-500',               !isActive);
        link.classList.toggle('dark:text-slate-400',          !isActive);
        link.classList.toggle('hover:border-slate-300',       !isActive);
        link.classList.toggle('dark:hover:border-slate-600',  !isActive);
    });
}

async function loadPanel(panelName, pushState = true) {
    // Loading state
    panel.style.opacity = '0.45';
    panel.style.pointerEvents = 'none';

    try {
        const url = `${BASE_URL}?panel=${panelName}`;
        const res = await fetch(url, {
            headers: { 'X-Profile-Panel': '1', 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const html = await res.text();

        panel.innerHTML = html;

        // Re-execute inline <script> tags inside the loaded HTML
        panel.querySelectorAll('script').forEach(old => {
            const s = document.createElement('script');
            [...old.attributes].forEach(a => s.setAttribute(a.name, a.value));
            s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });

        if (pushState) {
            history.pushState({ panel: panelName }, '', url);
        }

        setActiveNav(panelName);

        // Re-initialize panel-specific interactions
        initPanel(panelName);

    } catch (err) {
        console.error('[Profile] Panel load failed:', err);
        window.location.href = `${BASE_URL}?panel=${panelName}`;
    } finally {
        panel.style.opacity = '1';
        panel.style.pointerEvents = '';
        panel.animate(
            [{ opacity: 0, transform: 'translateY(5px)' }, { opacity: 1, transform: 'translateY(0)' }],
            { duration: 200, easing: 'ease-out', fill: 'forwards' }
        );
    }
}

navLinks.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const target = link.dataset.panel;
        const current = new URLSearchParams(window.location.search).get('panel') || 'profile';
        if (target === current) return;
        loadPanel(target);
    });
});

window.addEventListener('popstate', e => {
    const p = e.state?.panel ?? 'profile';
    loadPanel(p, false);
});

history.replaceState(
    { panel: new URLSearchParams(window.location.search).get('panel') || 'profile' },
    '',
    window.location.href
);

// ─── Panel Init (called after each AJAX swap) ────────────────────────────────
function initPanel(panelName) {
    if (panelName === 'preferences') initOtpCards();
}

// Run init on first full page load too
initPanel(new URLSearchParams(window.location.search).get('panel') || 'profile');

// ─── OTP Card Selection ──────────────────────────────────────────────────────
window.initOtpCards = function () {
    const radios = document.querySelectorAll('.otp-radio');
    if (!radios.length) return;

    function applyOtpState() {
        radios.forEach(radio => {
            const val   = radio.value;
            const box   = document.querySelector(`[data-otp-box="${val}"]`);
            const icon  = document.querySelector(`[data-otp-icon="${val}"]`);
            const lbl   = document.querySelector(`[data-otp-label="${val}"]`);
            const on    = radio.checked;
            if (!box) return;

            box.className = box.className
                .replace(/border-violet-500|bg-violet-50\/50|dark:bg-violet-900\/10|border-slate-200|dark:border-slate-700|bg-white|dark:bg-slate-800|hover:border-slate-300|dark:hover:border-slate-600/g, '')
                .trim();

            if (on) {
                box.classList.add('border-violet-500','bg-violet-50/50','dark:bg-violet-900/10');
            } else {
                box.classList.add('border-slate-200','dark:border-slate-700','bg-white','dark:bg-slate-800');
            }

            if (icon) {
                icon.classList.toggle('text-violet-500', on);
                icon.classList.toggle('text-slate-300',  !on);
                icon.classList.toggle('dark:text-slate-600', !on);
            }
            if (lbl) {
                lbl.classList.toggle('text-violet-700',        on);
                lbl.classList.toggle('dark:text-violet-400',   on);
                lbl.classList.toggle('text-slate-700',         !on);
                lbl.classList.toggle('dark:text-slate-300',    !on);
            }
        });
    }

    radios.forEach(r => r.addEventListener('change', applyOtpState));
    applyOtpState();
};

// ─── Avatar Preview ──────────────────────────────────────────────────────────
window.previewImage = function (input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const el = document.getElementById('avatarPreview');
        if (el) el.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
};

// ─── Lupa Sandi ──────────────────────────────────────────────────────────────
window.confirmResetSandi = function () {
    AppPopup.confirm({
        title: 'Kirim Link Reset?',
        description: 'Link untuk mengatur ulang kata sandi akan dikirim ke email Anda.',
        confirmText: 'Ya, Kirim Link',
        cancelText: 'Batal',
        onConfirm: () => document.getElementById('reset-link-form').submit()
    });
};

// ─── MFA Setup ───────────────────────────────────────────────────────────────
window.startMfaSetup = async function () {
    const modal = document.getElementById('mfaSetupModal');
    const step1 = document.getElementById('mfaStep1');
    const step2 = document.getElementById('mfaStep2');
    const qrEl  = document.getElementById('mfaQrPlaceholder');
    const secEl = document.getElementById('mfaSecretText');
    const inp   = document.getElementById('mfaConfirmCode');
    const btn   = document.getElementById('mfaConfirmBtn');
    const err   = document.getElementById('mfaSetupError');

    // Reset state
    qrEl.innerHTML = '<div class="animate-spin h-7 w-7 border-2 border-slate-400 border-t-transparent rounded-full"></div>';
    secEl.textContent = '--------';
    inp.value = '';
    btn.disabled = false;
    btn.textContent = 'Verifikasi & Aktifkan';
    err.classList.add('hidden');
    step1.classList.remove('hidden');
    step2.classList.add('hidden');
    modal.classList.remove('hidden');

    try {
        const res  = await fetch('{{ route("dashboard.profile.mfa.setup") }}');
        const data = await res.json();
        qrEl.innerHTML = data.qr_code;
        secEl.textContent = data.secret;
    } catch (e) {
        if (window.AppPopup?.error) {
            AppPopup.error({ title: 'Gagal', description: 'Gagal mengambil data setup MFA.' });
        }
    }
};

window.closeMfaModal = function () {
    document.getElementById('mfaSetupModal').classList.add('hidden');
};

window.confirmMfaSetup = async function () {
    const code  = document.getElementById('mfaConfirmCode').value;
    const btn   = document.getElementById('mfaConfirmBtn');
    const err   = document.getElementById('mfaSetupError');
    const step1 = document.getElementById('mfaStep1');
    const step2 = document.getElementById('mfaStep2');

    if (code.length < 6) {
        err.textContent = 'Masukkan 6 digit kode lengkap.';
        err.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memvalidasi...';
    err.classList.add('hidden');

    try {
        const res  = await fetch('{{ route("dashboard.profile.mfa.confirm") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ code })
        });
        const data = await res.json();

        if (res.ok) {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            const list = document.getElementById('backupCodesList');
            list.innerHTML = data.backup_codes.map(c =>
                `<div class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400 py-1.5 px-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 text-center">${c}</div>`
            ).join('');
        } else {
            err.textContent = data.message || 'Kode salah.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Verifikasi & Aktifkan';
        }
    } catch (e) {
        err.textContent = 'Terjadi kesalahan sistem.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Verifikasi & Aktifkan';
    }
};

// ─── MFA Disable ─────────────────────────────────────────────────────────────
window.openDisableMfaModal = function () {
    document.getElementById('mfaDisablePassword').value = '';
    document.getElementById('mfaDisableError').classList.add('hidden');
    document.getElementById('mfaDisableModal').classList.remove('hidden');
};

window.closeDisableMfaModal = function () {
    document.getElementById('mfaDisableModal').classList.add('hidden');
};

window.confirmDisableMfa = async function () {
    const pw  = document.getElementById('mfaDisablePassword').value;
    const err = document.getElementById('mfaDisableError');
    const btn = document.getElementById('mfaDisableConfirmBtn');

    if (!pw) { err.textContent = 'Kata sandi wajib diisi.'; err.classList.remove('hidden'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>...';
    err.classList.add('hidden');

    try {
        const res  = await fetch('{{ route("dashboard.profile.mfa.disable") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ current_password: pw })
        });
        const data = await res.json();

        if (res.ok) {
            window.location.reload();
        } else {
            err.textContent = data.message || 'Gagal menonaktifkan MFA.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Ya, Matikan';
        }
    } catch (e) {
        err.textContent = 'Terjadi kesalahan sistem.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Ya, Matikan';
    }
};

// ─── Device Revoke ───────────────────────────────────────────────────────────
window.revokeDevice = function (deviceId, btn) {
    if (!window.AppPopup) return;
    AppPopup.confirm({
        title: 'Cabut Perangkat?',
        description: 'Perangkat ini tidak akan lagi dipercaya dan akan memerlukan verifikasi OTP kembali.',
        confirmText: 'Ya, Cabut',
        cancelText: 'Batal',
        onConfirm: async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';

            try {
                const res = await fetch(`{{ url('dashboard/profile/devices') }}/${deviceId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (res.ok) {
                    const row = btn.closest('[data-device-id]');
                    if (row) {
                        row.style.transition = 'opacity 0.3s, transform 0.3s';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(16px)';
                        setTimeout(() => row.remove(), 300);
                    }
                } else {
                    const data = await res.json().catch(() => ({}));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
                    alert(data.message || 'Gagal mencabut perangkat.');
                }
            } catch (e) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
            }
        }
    });
};

})();
</script>
@endpush
