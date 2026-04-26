@extends('layouts.app-dashboard')

@section('title', 'SSO Client Apps')
@section('page-title', 'SSO Client Apps')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-tight">SSO Client Applications</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola aplikasi pihak ketiga yang terhubung via SSO (OAuth2).</p>
        </div>
        <button onclick="AppModal.open('createClientModal')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium rounded-lg transition-all shadow-sm shadow-indigo-500/20">
            <i class="fa-solid fa-plus"></i> Tambah Klien
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-4 font-semibold">Nama Aplikasi</th>
                        <th class="px-6 py-4 font-semibold">Domain & Konfigurasi</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($clients as $client)
                        @php
                            // Ekstrak domain dari webhook_url untuk tampilan
                            $domain = 'Tidak Diketahui';
                            $protocol = 'https://';
                            if ($client->webhook_url) {
                                $parsed = parse_url($client->webhook_url);
                                if (isset($parsed['host'])) {
                                    $domain = $parsed['host'];
                                    if (isset($parsed['port'])) {
                                        $domain .= ':' . $parsed['port'];
                                    }
                                }
                                $protocol = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'https://';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-500/20 shrink-0">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $client->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5" title="Client ID">ID: {{ Str::limit($client->oauth_client_id, 12) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 dark:text-slate-300 font-medium flex items-center gap-2">
                                    <i class="fa-solid fa-globe text-slate-400 text-xs"></i> {{ $protocol }}{{ $domain }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex flex-col gap-0.5">
                                    <span title="Webhook"><i class="fa-solid fa-plug text-slate-400 w-3"></i> /api/sso/webhook</span>
                                    <span title="Callback"><i class="fa-solid fa-arrow-right-arrow-left text-slate-400 w-3"></i> /auth/callback</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($client->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                                {{-- Area requirement badge --}}
                                @php $areaCount = $client->accessAreas()->count(); @endphp
                                @if($areaCount > 0)
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                                            <i class="fa-solid fa-shield-halved text-[8px]"></i> {{ $areaCount }} area
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-50 text-slate-400 dark:bg-slate-800 dark:text-slate-500 border border-slate-100 dark:border-slate-700">
                                            <i class="fa-solid fa-globe text-[8px]"></i> Open
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Manage Access Areas Button --}}
                                    <a href="{{ route('sso.clients.edit-access-areas', $client) }}"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 transition-colors"
                                       title="Kelola Access Area">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </a>

                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick="openEditModal({{ $client->id }}, '{{ addslashes($client->name) }}', '{{ $protocol }}', '{{ $domain }}', '{{ addslashes($client->description) }}', {{ $client->is_active ? 'true' : 'false' }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200 transition-colors"
                                            title="Edit Klien">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    <!-- Test Webhook Button -->
                                    <form id="form-test-{{ $client->id }}" action="{{ route('sso.clients.test-webhook', $client) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="button" onclick="confirmTestWebhook('form-test-{{ $client->id }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 transition-colors"
                                                title="Test Webhook Connection">
                                            <i class="fa-solid fa-satellite-dish text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Generate Token Button -->
                                    <form id="form-regen-{{ $client->id }}" action="{{ route('sso.clients.generate-token', $client) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="button" onclick="confirmRegenerate('form-regen-{{ $client->id }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 transition-colors"
                                                title="Regenerate Token">
                                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form id="form-delete-{{ $client->id }}" action="{{ route('sso.clients.destroy', $client) }}" method="POST" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete('form-delete-{{ $client->id }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors"
                                                title="Hapus Klien">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mb-3">
                                    <i class="fa-solid fa-plug-circle-xmark text-xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada Klien SSO</h3>
                                <p class="text-xs text-slate-500 mt-1">Tambahkan aplikasi pihak ketiga untuk mengaktifkan integrasi SSO.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Create Client -->
<x-app-modal id="createClientModal" maxWidth="md" title="Tambah SSO Client" description="Daftarkan aplikasi baru untuk integrasi SSO." icon='<i class="fa-solid fa-plus text-xl"></i>' iconColor="indigo">
    <form id="createClientForm" action="{{ route('sso.clients.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="create_name">Nama Aplikasi</label>
                <input type="text" id="create_name" name="name" required placeholder="Contoh: Portal Akademik">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="create_domain" class="!mb-0">Domain Klien</label>
                    <div class="inline-flex p-1">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="protocol" value="https://" checked class="peer sr-only">
                            <span class="absolute inset-0 rounded-md bg-white dark:bg-slate-700 shadow-sm opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                            <span class="relative z-10 px-3 py-1 flex items-center gap-1.5 text-[11px] uppercase tracking-wider font-bold text-slate-500 peer-checked:text-emerald-600 dark:text-slate-400 dark:peer-checked:text-emerald-400 transition-colors">
                                <i class="fa-solid fa-lock text-[10px]"></i> HTTPS
                            </span>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="protocol" value="http://" class="peer sr-only">
                            <span class="absolute inset-0 rounded-md bg-white border dark:bg-slate-700 shadow-sm opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                            <span class="relative z-10 px-3 py-1 flex items-center gap-1.5 text-[11px] uppercase tracking-wider font-bold text-slate-500 peer-checked:text-slate-800 dark:text-slate-400 dark:peer-checked:text-slate-200 transition-colors">
                                <i class="fa-solid fa-lock-open text-[10px] text-slate-400"></i> HTTP
                            </span>
                        </label>
                    </div>
                </div>
                <input type="text" id="create_domain" name="domain" required placeholder="app.campus.test" class="w-full">
                <div class="mt-2 p-3 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-lg text-xs text-indigo-700 dark:text-indigo-300">
                    Sistem otomatis akan mengarahkan <br>
                    Callback ke: <span class="font-mono font-bold">/auth/callback</span><br>
                    Webhook ke: <span class="font-mono font-bold">/api/sso/webhook</span>
                </div>
            </div>
            <div>
                <label for="create_description">Deskripsi (Opsional)</label>
                <textarea id="create_description" name="description" rows="2" placeholder="Informasi tambahan..."></textarea>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('createClientModal')" class="modal-btn-cancel">Batal</button>
            <button type="submit" form="createClientForm" class="modal-btn-primary">Buat Klien</button>
        </x-slot>
    </form>
</x-app-modal>

<!-- Modal: Edit Client -->
<x-app-modal id="editClientModal" maxWidth="md" title="Edit SSO Client" description="Perbarui informasi klien SSO." icon='<i class="fa-solid fa-pen text-xl"></i>' iconColor="emerald">
    <form id="editClientForm" method="POST">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label for="edit_name">Nama Aplikasi</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="edit_domain" class="!mb-0">Domain Klien</label>
                    <div class="inline-flex p-1 ">
                        <label class="relative cursor-pointer">
                            <input type="radio" id="edit_proto_https" name="protocol" value="https://" class="peer sr-only">
                            <span class="absolute inset-0 rounded-md bg-white dark:bg-slate-700 shadow-sm opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                            <span class="relative z-10 px-3 py-1 flex items-center gap-1.5 text-[11px] uppercase tracking-wider font-bold text-slate-500 peer-checked:text-emerald-600 dark:text-slate-400 dark:peer-checked:text-emerald-400 transition-colors">
                                <i class="fa-solid fa-lock text-[10px]"></i> HTTPS
                            </span>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" id="edit_proto_http" name="protocol" value="http://" class="peer sr-only">
                            <span class="absolute inset-0 rounded-md bg-white dark:bg-slate-700 shadow-sm opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                            <span class="relative z-10 px-3 py-1 flex items-center gap-1.5 text-[11px] uppercase tracking-wider font-bold text-slate-500 peer-checked:text-slate-800 dark:text-slate-400 dark:peer-checked:text-slate-200 transition-colors">
                                <i class="fa-solid fa-lock-open text-[10px] text-slate-400"></i> HTTP
                            </span>
                        </label>
                    </div>
                </div>
                <input type="text" id="edit_domain" name="domain" required class="w-full">
            </div>
            <div>
                <label for="edit_description">Deskripsi (Opsional)</label>
                <textarea id="edit_description" name="description" rows="2"></textarea>
            </div>
            <label class="flex items-center gap-2 cursor-pointer mt-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Status Klien Aktif</span>
            </label>
        </div>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('editClientModal')" class="modal-btn-cancel">Batal</button>
            <button type="submit" form="editClientForm" class="modal-btn-primary !bg-emerald-600 !border-emerald-700 hover:!bg-emerald-700">Simpan Perubahan</button>
        </x-slot>
    </form>
</x-app-modal>

<!-- Modal: Secure Credentials (VIEW ONCE) -->
@if(session('credentials_modal'))
<x-app-modal id="credentialsModal" maxWidth="md" title="Kredensial SSO Terbuat!" description="Harap simpan kredensial ini SEKARANG. Demi keamanan, data ini tidak akan pernah ditampilkan lagi setelah Anda menutup modal ini." icon='<i class="fa-solid fa-shield-halved text-xl"></i>' iconColor="red">
    <div class="space-y-5">
        <!-- Client ID -->
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Client ID</label>
            <div class="relative">
                <input type="text" id="sec_client_id" readonly value="{{ session('client_id') }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 pl-3 pr-10 text-sm font-mono text-slate-700 dark:text-slate-300 focus:outline-none">
                <button type="button" onclick="copyToClipboard('sec_client_id', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors" title="Copy">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
        </div>

        <!-- Client Secret -->
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">OAuth Client Secret</label>
            <div class="relative">
                <input type="text" id="sec_client_secret" readonly value="{{ session('client_secret') }}" class="w-full bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-500/30 rounded-lg py-2 pl-3 pr-10 text-sm font-mono text-red-600 dark:text-red-400 focus:outline-none">
                <button type="button" onclick="copyToClipboard('sec_client_secret', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-600 transition-colors" title="Copy">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
        </div>

        <!-- Webhook Secret -->
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Webhook Secret (Global Logout)</label>
            <div class="relative">
                <input type="text" id="sec_webhook_secret" readonly value="{{ session('webhook_secret') }}" class="w-full bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-500/30 rounded-lg py-2 pl-3 pr-10 text-sm font-mono text-amber-700 dark:text-amber-400 focus:outline-none">
                <button type="button" onclick="copyToClipboard('sec_webhook_secret', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-600 transition-colors" title="Copy">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-500/10 p-3 rounded-lg border border-red-100 dark:border-red-500/20 text-xs text-red-600 dark:text-red-400 flex gap-2 items-start">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <p><strong>Peringatan!</strong> Setelah Anda menekan "Saya Telah Menyimpan Ini", nilai rahasia di atas akan hilang selamanya. Jika hilang, Anda harus melakukan *Regenerate Token* ulang.</p>
        </div>
    </div>
    
    <x-slot name="footer">
        <button type="button" onclick="AppModal.close('credentialsModal')" class="modal-btn-primary !bg-red-600 !border-red-700 hover:!bg-red-700 w-full justify-center">Saya Telah Menyimpan Ini</button>
    </x-slot>
</x-app-modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            AppModal.open('credentialsModal');
        }, 100);
    });
</script>
@endif

<script>
    function openEditModal(id, name, protocol, domain, description, isActive) {
        document.getElementById('editClientForm').action = `/dashboard/sso/clients/${id}`;
        document.getElementById('edit_name').value = name;
        
        if(protocol === 'https://') {
            document.getElementById('edit_proto_https').checked = true;
        } else {
            document.getElementById('edit_proto_http').checked = true;
        }

        document.getElementById('edit_domain').value = domain;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_is_active').checked = isActive;
        
        AppModal.open('editClientModal');
    }

    function copyToClipboard(inputId, btn) {
        const copyText = document.getElementById(inputId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); // Untuk mobile
        navigator.clipboard.writeText(copyText.value).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check text-emerald-500"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 1500);
        });
    }

    function confirmTestWebhook(formId) {
        AppPopup.confirm({
            title: 'Test Koneksi Webhook?',
            description: 'Sistem akan mengirim HTTP Request (Ping) ke Webhook URL klien ini untuk memastikan koneksi berjalan lancar.',
            confirmText: 'Ya, Uji Koneksi',
            onConfirm: () => document.getElementById(formId).submit()
        });
    }

    function confirmRegenerate(formId) {
        AppPopup.confirm({
            title: 'Regenerate Token?',
            description: 'PERINGATAN! Ini akan mereset Client Secret dan Webhook Secret. Integrasi yang sedang berjalan akan putus hingga rahasia baru dipasang di aplikasi klien. Lanjutkan?',
            confirmText: 'Ya, Regenerate',
            onConfirm: () => document.getElementById(formId).submit()
        });
    }

    function confirmDelete(formId) {
        AppPopup.confirm({
            title: 'Hapus SSO Client?',
            description: 'Aplikasi yang terhubung via client ini tidak akan bisa login lagi via SSO. Tindakan ini permanen.',
            confirmText: 'Ya, Hapus Klien',
            onConfirm: () => document.getElementById(formId).submit()
        });
    }
</script>

@endsection
