@extends('layouts.app-dashboard')

@section('title', 'Security Audit Logs - MixuAuth')
@section('page-title', 'Security Audit')
@section('page-sub', 'Pantau dan analisa aktivitas autentikasi sistem secara real-time dengan dukungan AI.')

@section('content')

{{-- STATS SECTION --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm">
        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Attempts</p>
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($stats['total']) }}</h3>
    </div>
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
        <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Success</p>
        <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($stats['success']) }}</h3>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
        <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mb-1">Failed</p>
        <h3 class="text-xl font-bold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($stats['failed']) }}</h3>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-slate-800"></div>
        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Blocked</p>
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 tabular-nums">{{ number_format($stats['blocked']) }}</h3>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
        <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest mb-1">MFA Required</p>
        <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($stats['otp']) }}</h3>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <form action="{{ route('admin.security.logs.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari email atau IP..." class="w-full pl-8 pr-4 py-1.5 text-[11px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded focus:outline-none focus:border-indigo-500 transition-all">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            
            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-[11px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded focus:outline-none focus:border-indigo-500 transition-all text-slate-600 dark:text-slate-300">
                <option value="">Semua Status</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                <option value="otp_required" {{ request('status') == 'otp_required' ? 'selected' : '' }}>MFA Required</option>
            </select>

            <button type="submit" class="px-4 py-1.5 bg-slate-800 dark:bg-slate-700 text-white text-[11px] font-bold rounded hover:bg-slate-900 transition-colors">Filter</button>
        </form>

        @if(request('search') || request('status'))
        <a href="{{ route('admin.security.logs.index') }}" class="p-1.5 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Reset Filter">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
    </div>

    <div class="flex items-center gap-3 w-full lg:w-auto">
        <button onclick="openBulkDeleteLogsModal()" class="px-4 py-1.5 border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 text-[11px] font-bold rounded bg-red-50/50 dark:bg-red-500/5 hover:bg-red-100 transition-all flex items-center justify-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/></svg>
            Bersihkan Log
        </button>
    </div>
</div>

{{-- AUDIT TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Waktu & IP</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Identitas</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">AI Risk & Decision</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Status</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $log->occurred_at->format('d/m/Y H:i:s') }}</span>
                            <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $log->ip_address }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                @if($log->user)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate">{{ $log->email_attempted }}</span>
                                <span class="text-[9px] text-slate-400 uppercase tracking-tighter">{{ $log->user ? 'Verified User' : 'Unknown Identity' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $decision = $log->decision;
                            $riskScore = $log->risk_score;
                            
                            $decisionStyles = [
                                'ALLOW' => 'text-emerald-500',
                                'OTP'   => 'text-amber-500',
                                'BLOCK' => 'text-red-500',
                                'MFA'   => 'text-indigo-500',
                            ];
                            
                            $decisionLabels = [
                                'ALLOW' => 'PASSED',
                                'OTP'   => 'AI CHALLENGE',
                                'BLOCK' => 'AI BLOCKED',
                                'MFA'   => 'MFA ENFORCED',
                            ];
                            
                            $currentLabel = $decisionLabels[$decision] ?? ($decision ?: 'NONE');
                            $currentStyle = $decisionStyles[$decision] ?? 'text-slate-400';
                        @endphp
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $riskScore >= 80 ? 'bg-red-500' : ($riskScore >= 50 ? 'bg-amber-500' : ($riskScore > 0 ? 'bg-emerald-500' : 'bg-slate-300')) }}"></span>
                                <span class="text-[11px] font-bold {{ $riskScore >= 80 ? 'text-red-500' : ($riskScore >= 50 ? 'text-amber-500' : ($riskScore > 0 ? 'text-emerald-500' : 'text-slate-500')) }} tabular-nums">
                                    {{ $riskScore !== null ? $riskScore . '%' : '--' }}
                                </span>
                            </div>
                            <span class="text-[9px] font-black uppercase {{ $currentStyle }}">{{ $currentLabel }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $statusStyles = [
                                'success' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                'failed' => 'bg-red-50 text-red-600 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                                'blocked' => 'bg-slate-900 text-white border-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700',
                                'otp_required' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                'fallback' => 'bg-sky-50 text-sky-600 border-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/20',
                            ];
                            $style = $statusStyles[$log->status] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                        @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider {{ $style }}">
                            {{ str_replace('_', ' ', $log->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <button onclick="viewLogDetails({{ $log->id }})" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-indigo-600 transition-colors" title="Lihat Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-20 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">Tidak ada log ditemukan</p>
                            <p class="text-[11px] text-slate-400 mt-1">Data audit keamanan belum tersedia atau filter tidak cocok.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $logs->links() }}
</div>

{{-- MODALS --}}
@include('admin.security.log.modals.details')
@include('admin.security.log.modals.bulk_delete')

<script>
    const API_BASE = '{{ url("admin/security/logs") }}';
    const CSRF = '{{ csrf_token() }}';

    async function viewLogDetails(id) {
        try {
            const resp = await fetch(`${API_BASE}/${id}/details`);
            const res = await resp.json();
            
            if (res.success) {
                const data = res.data;
                
                // Basic Info
                document.getElementById('det-id').textContent = data.id;
                document.getElementById('det-email').textContent = data.email;
                document.getElementById('det-ip').textContent = data.ip;
                document.getElementById('det-time').textContent = data.occurred_at;
                document.getElementById('det-country').textContent = data.country_code || 'Internal / Private IP';
                
                // Device Info
                document.getElementById('det-browser').textContent = `${data.browser} ${data.browser_version}`;
                document.getElementById('det-platform').textContent = `${data.platform} ${data.platform_version} (${data.device})`;
                document.getElementById('det-ua-full').textContent = data.ua;
                document.getElementById('det-raw').textContent = JSON.stringify(data.raw, null, 2);

                // Risk Analysis
                const score = data.risk_score || 0;
                const decision = data.decision;
                const riskLabelEl = document.getElementById('det-risk-label');
                const riskDescEl = document.getElementById('det-risk-desc');
                const sectionEl = document.getElementById('risk-analysis-section');
                const circleEl = document.getElementById('det-risk-circle');
                const scoreEl = document.getElementById('det-risk-score');

                // Color Logic
                let colorClass = 'emerald';
                let label = 'PASSED';
                let desc = 'Aktivitas ini dinilai aman oleh AI. Tidak ada anomali signifikan yang ditemukan pada pola akses ini.';

                if (decision === 'MFA') {
                    colorClass = 'indigo';
                    label = 'MFA ENFORCED';
                    desc = 'Verifikasi tambahan (OTP) diwajibkan karena pengaturan akun atau kebijakan sistem (Always MFA), terlepas dari skor risiko.';
                } else if (decision === 'OTP') {
                    colorClass = 'amber';
                    label = 'AI CHALLENGE';
                    desc = 'AI mendeteksi anomali ringan atau pola baru. Verifikasi MFA diwajibkan untuk memastikan identitas pengguna.';
                } else if (decision === 'BLOCK') {
                    colorClass = 'red';
                    label = 'AI BLOCKED';
                    desc = 'Akses ditolak secara otomatis karena terdeteksi sebagai ancaman keamanan tingkat tinggi.';
                }

                // Apply Colors
                const colorMap = {
                    emerald: { border: 'border-emerald-100', text: 'text-emerald-600', circle: 'text-emerald-500', label: 'bg-emerald-100 text-emerald-600' },
                    amber: { border: 'border-amber-100', text: 'text-amber-600', circle: 'text-amber-500', label: 'bg-amber-100 text-amber-600' },
                    red: { border: 'border-red-100', text: 'text-red-600', circle: 'text-red-500', label: 'bg-red-100 text-red-600' },
                    indigo: { border: 'border-indigo-100', text: 'text-indigo-600', circle: 'text-indigo-500', label: 'bg-indigo-100 text-indigo-600' }
                };

                const currentMap = colorMap[colorClass];
                sectionEl.className = `relative overflow-hidden p-6 rounded border transition-all duration-500 ${currentMap.border}`;
                riskLabelEl.className = `px-2 py-0.5 rounded text-[9px] font-black uppercase ${currentMap.label}`;
                riskLabelEl.textContent = label;
                riskDescEl.textContent = desc;
                
                scoreEl.textContent = decision === 'MFA' ? '--' : score + '%';
                scoreEl.className = `absolute text-sm font-black tabular-nums ${currentMap.text}`;
                
                circleEl.className = `transition-all duration-1000 ease-out ${currentMap.circle}`;
                const offset = 175.92 - (175.92 * (decision === 'MFA' ? 100 : score) / 100);
                circleEl.style.strokeDashoffset = offset;

                // Status Badge
                const statusStyles = {
                    'success': 'bg-emerald-50 text-emerald-600 border-emerald-100',
                    'failed': 'bg-red-50 text-red-600 border-red-100',
                    'blocked': 'bg-slate-900 text-white border-slate-800',
                    'otp_required': 'bg-amber-50 text-amber-600 border-amber-100'
                };
                const sStyle = statusStyles[data.status] || 'bg-slate-50 text-slate-600 border-slate-100';
                document.getElementById('det-status-badge').innerHTML = `
                    <span class="px-3 py-1 rounded text-[10px] font-bold border uppercase tracking-wider ${sStyle}">
                        ${data.status.replace('_', ' ')}
                    </span>
                `;

                // Flags
                const flagsCont = document.getElementById('det-flags');
                flagsCont.innerHTML = '';
                if (data.reason_flags && data.reason_flags.length > 0) {
                    data.reason_flags.forEach(flag => {
                        const span = document.createElement('span');
                        span.className = 'px-2 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[9px] text-slate-600 dark:text-slate-400 font-mono';
                        span.textContent = flag;
                        flagsCont.appendChild(span);
                    });
                } else {
                    flagsCont.innerHTML = '<span class="text-[10px] text-slate-400 italic font-medium">No behavioral flags detected</span>';
                }

                AppModal.open('logDetailsModal');
            }
        } catch (err) {
            console.error('[viewLogDetails]', err);
            showToast('error', 'Gagal memuat detail log audit.');
        }
    }

    window.openBulkDeleteLogsModal = function() {
        AppModal.open('bulkDeleteLogsModal');
    }

    window.submitBulkDeleteLogs = function() {
        const start = document.getElementById('bulk-log-start-date').value;
        const end = document.getElementById('bulk-log-end-date').value;

        if (!start || !end) {
            showToast('error', 'Tentukan rentang waktu penghapusan.');
            return;
        }

        const btn = document.getElementById('btn-submit-bulk-delete-logs');
        const spinner = document.getElementById('bulk-delete-logs-spinner');
        
        btn.disabled = true;
        spinner.classList.remove('hidden');

        fetch(`${API_BASE}/bulk-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ start_date: start, end_date: end })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', res.message);
                AppModal.close('bulkDeleteLogsModal');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('error', res.message || 'Gagal menghapus log.');
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        })
        .catch(() => {
            showToast('error', 'Kesalahan koneksi server.');
            btn.disabled = false;
            spinner.classList.add('hidden');
        });
    }

    function showToast(type, message) {
        if (window.AppPopup) {
            if (type === 'success') AppPopup.success({ description: message });
            else AppPopup.error({ description: message });
        } else {
            alert(message);
        }
    }
</script>
@endsection
