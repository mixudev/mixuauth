<x-app-modal 
    id="logDetailsModal" 
    maxWidth="2xl" 
    title="Security Audit Analysis" 
    description="Deep investigation of authentication signals and system enforcement." 
    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/></svg>" 
    iconColor="indigo"
>
    <div class="space-y-8">
        {{-- Header Section: Identity & Status --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <div id="det-user-avatar" class="w-12 h-12 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h4 id="det-email" class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight"></h4>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Audit Log ID: #<span id="det-id"></span></p>
                </div>
            </div>
            <div id="det-status-badge" class="flex items-center"></div>
        </div>

        {{-- Grid Info: Time, IP, Device --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Timestamp</label>
                <p id="det-time" class="text-[11px] font-bold text-slate-700 dark:text-slate-300 font-mono"></p>
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">IP Address</label>
                <p id="det-ip" class="text-[11px] font-bold text-slate-700 dark:text-slate-300 font-mono"></p>
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Origin Country</label>
                <p id="det-country" class="text-[11px] font-bold text-slate-700 dark:text-slate-300"></p>
            </div>
        </div>

        {{-- Device & Browser --}}
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-8">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Platform & OS</p>
                    <p id="det-platform" class="text-xs font-bold text-slate-700 dark:text-slate-200"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Web Browser</p>
                    <p id="det-browser" class="text-xs font-bold text-slate-700 dark:text-slate-200"></p>
                </div>
            </div>
        </div>

        {{-- Risk Analysis Card --}}
        <div id="risk-analysis-section" class="relative overflow-hidden p-6 rounded border ">
            <div class="relative z-10 flex flex-col md:flex-row justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest">AI Intelligence Insight</h5>
                        <span id="det-risk-label" class="px-2 py-0.5 rounded text-[9px] font-black uppercase"></span>
                    </div>
                    <div id="det-risk-desc" class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed max-w-md"></div>
                    
                    <div class="mt-4 flex flex-wrap gap-1.5" id="det-flags"></div>
                </div>

                <div class="flex flex-col items-center justify-center min-w-[120px] border-l border-slate-200 dark:border-slate-800 pl-6">
                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-2">Risk Score</p>
                    <div class="relative flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="transparent" class="text-slate-100 dark:text-slate-800" />
                            <circle id="det-risk-circle" cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="transparent" stroke-dasharray="175.92" stroke-dashoffset="175.92" class="transition-all duration-1000 ease-out" />
                        </svg>
                        <span id="det-risk-score" class="absolute text-sm font-black tabular-nums"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Technical Meta --}}
        <div x-data="{ open: false }" class="border-t border-slate-100 dark:border-slate-800 pt-4">
            <button @click="open = !open" class="flex items-center gap-2 text-[10px] font-bold text-slate-400 hover:text-indigo-500 transition-colors uppercase tracking-widest">
                <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                Debug Technical Metadata
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-slate-900 rounded border border-slate-800">
                        <p class="text-[9px] font-bold text-slate-500 uppercase mb-2">User Agent String</p>
                        <p id="det-ua-full" class="text-[10px] text-slate-400 font-mono break-all leading-normal"></p>
                    </div>
                    <div class="p-3 bg-slate-900 rounded border border-slate-800">
                        <p class="text-[9px] font-bold text-slate-500 uppercase mb-2">Raw AI Response</p>
                        <pre id="det-raw" class="text-[10px] text-slate-400 font-mono leading-normal overflow-x-auto"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                AI-Powered Security Verification System
            </div>
            <button onclick="AppModal.close('logDetailsModal')" class="modal-btn-cancel">Close Audit</button>
        </div>
    </x-slot>
</x-app-modal>
