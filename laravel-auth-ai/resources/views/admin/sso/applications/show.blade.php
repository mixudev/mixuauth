@extends('layouts.app-dashboard')

@section('title', 'Application Detail: ' . $client->name)
@section('page-title', 'Application Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('sso.applications.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            Back to Ecosystem
        </a>
        <div class="flex items-center gap-2">
            @php
                $parsed = parse_url($client->webhook_url);
                $clientUrl = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            @endphp
            <a href="{{ $clientUrl }}" target="_blank" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-bold shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-external-link text-[10px]"></i>
                Visit Application
            </a>
        </div>
    </div>

    <!-- Header Detail Box -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 flex flex-col md:flex-row items-start gap-8">
            <!-- App Icon/Branding -->
            <div class="w-24 h-24 rounded-md bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-4xl font-bold border border-indigo-100 dark:border-indigo-500/20 shadow-inner shrink-0">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>

            <!-- Detailed Stats & Info -->
            <div class="flex-1 space-y-6 w-full">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">{{ $client->name }}</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
                            {{ $client->description ?: 'Tidak ada deskripsi tambahan untuk aplikasi ini.' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                            Status: Active
                        </span>
                        <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            OAuth2 / OIDC
                        </span>
                    </div>
                </div>

                <!-- Technical Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-6 border-t border-slate-50 dark:border-slate-800/50">
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Client ID</span>
                        <div class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 truncate" title="{{ $client->oauth_client_id }}">
                            {{ Str::limit($client->oauth_client_id, 16) }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Webhook Endpoint</span>
                        <div class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 truncate">
                            {{ Str::afterLast($client->webhook_url, '/') ?: '/webhook' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Last Sync</span>
                        <div class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ $client->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Total Tokens</span>
                        <div class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $stats['total_tokens'] }} Issued
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout for Users -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Active Users (Left Col) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">User Sedang Aktif</h3>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 rounded">
                        {{ $activeUsers->count() }} Online
                    </span>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-400 border-b border-slate-50 dark:border-slate-800/50">
                            <tr>
                                <th class="px-5 py-3 font-semibold uppercase tracking-wider">User</th>
                                <th class="px-5 py-3 font-semibold uppercase tracking-wider">Email</th>
                                <th class="px-5 py-3 font-semibold uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @forelse($activeUsers as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full border border-slate-100 dark:border-slate-700">
                                        <div class="font-bold text-slate-700 dark:text-slate-300">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-right">
                                    <button class="text-indigo-600 hover:underline font-bold">Manage</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-slate-400 italic">
                                    Tidak ada user yang sedang online di aplikasi ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inactive/Registered Users -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800/50">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">User Terdaftar (Offline)</h3>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @forelse($inactiveUsers as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors opacity-60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full grayscale border border-slate-100 dark:border-slate-700">
                                        <div class="font-bold text-slate-600 dark:text-slate-400">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-right text-slate-400">Offline</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-slate-400 italic">
                                    Semua user terdaftar sedang online atau belum ada user lain.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info (Right Col) -->
        <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="bg-indigo-600 rounded-md p-6 text-white shadow-lg shadow-indigo-500/20">
                <h4 class="text-[10px] font-bold uppercase tracking-widest opacity-80 mb-4">Security Overview</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs opacity-90">Revoked Tokens</span>
                        <span class="text-sm font-mono font-bold">{{ $stats['revoked_tokens'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs opacity-90">Active Sessions</span>
                        <span class="text-sm font-mono font-bold">{{ $activeUsers->count() }}</span>
                    </div>
                    <div class="pt-4 border-t border-white/10">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-shield-check text-indigo-300"></i>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Health Status</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: 100%"></div>
                        </div>
                        <p class="text-[9px] mt-2 opacity-70 italic">Koneksi webhook dan integrasi kunci OAuth terpantau stabil.</p>
                    </div>
                </div>
            </div>

            <!-- Required Access Areas Box -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50 pb-2">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white">Required Access Areas</h4>
                    <a href="{{ route('sso.clients.edit-access-areas', $client) }}" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Manage</a>
                </div>
                
                <div class="space-y-2">
                    @if($client->accessAreas->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($client->accessAreas as $area)
                                <span class="px-2 py-1 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <i class="fa-solid fa-shield-halved text-slate-400 mr-1"></i> {{ $area->name }}
                                </span>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-500 mt-2 italic">User harus memiliki SEMUA area di atas untuk dapat login.</p>
                    @else
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded text-center">
                            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                <i class="fa-solid fa-globe mr-1"></i> Open Client (Tanpa Restriksi)
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Webhook Test Box (Interactive AJAX) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50 pb-2">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white">Integrasi Webhook</h4>
                    <span id="webhook-status-dot" class="w-2 h-2 rounded-full bg-slate-300"></span>
                </div>
                
                <div class="space-y-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Endpoint URL</span>
                        <code class="text-[10px] p-2 bg-slate-50 dark:bg-slate-800 rounded font-mono text-slate-600 dark:text-slate-300 break-all">
                            {{ $client->webhook_url }}
                        </code>
                    </div>

                    <div id="webhook-result" class="hidden p-3 rounded text-[10px] font-medium leading-relaxed">
                        <!-- Result message will appear here -->
                    </div>

                    <button type="button" id="btn-test-webhook" onclick="runWebhookTest()" class="w-full py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[11px] font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-vial"></i>
                        <span>Test Connection</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    async function runWebhookTest() {
        const btn = document.getElementById('btn-test-webhook');
        const dot = document.getElementById('webhook-status-dot');
        const resultBox = document.getElementById('webhook-result');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');

        // Loading state
        btn.disabled = true;
        btnText.textContent = 'Testing...';
        btnIcon.className = 'fa-solid fa-spinner fa-spin';
        resultBox.classList.add('hidden');
        dot.className = 'w-2 h-2 rounded-full bg-amber-400 animate-pulse';

        try {
            const response = await fetch('{{ route("sso.clients.test-webhook", $client) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            resultBox.classList.remove('hidden');
            resultBox.textContent = data.message;

            if (data.success) {
                dot.className = 'w-2 h-2 rounded-full bg-emerald-500';
                resultBox.className = 'p-3 rounded text-[10px] font-medium leading-relaxed bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
                showToast('Sukses', 'Webhook terhubung dengan baik', 'success');
            } else {
                dot.className = 'w-2 h-2 rounded-full bg-red-500';
                resultBox.className = 'p-3 rounded text-[10px] font-medium leading-relaxed bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
                showToast('Error', 'Gagal menghubungi webhook', 'error');
            }

        } catch (error) {
            dot.className = 'w-2 h-2 rounded-full bg-red-500';
            resultBox.classList.remove('hidden');
            resultBox.className = 'p-3 rounded text-[10px] font-medium leading-relaxed bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
            resultBox.textContent = '❌ Terjadi kesalahan jaringan: ' + error.message;
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Test Connection';
            btnIcon.className = 'fa-solid fa-vial';
        }
    }
</script>
@endpush
@endsection
