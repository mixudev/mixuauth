<x-app-modal 
    id="deviceDetailsModal" 
    maxWidth="2xl" 
    title="Device Identity Profile" 
    description="Detailed verification data and trust history for this specific device." 
    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'/></svg>" 
    iconColor="violet"
>
    <div class="space-y-8">
        {{-- Identity Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <div id="dev-icon-placeholder" class="w-12 h-12 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'/></svg>
                </div>
                <div>
                    <h4 id="dev-user" class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight"></h4>
                    <p id="dev-email" class="text-[11px] text-slate-400 font-medium"></p>
                </div>
            </div>
            <div id="dev-status-badge"></div>
        </div>

        {{-- Device & Platform Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded border border-slate-100 dark:border-slate-800">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Platform & Environment</label>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">Browser</span>
                        <span id="dev-browser" class="text-[11px] font-bold text-slate-700 dark:text-slate-200"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">Operating System</span>
                        <span id="dev-os" class="text-[11px] font-bold text-slate-700 dark:text-slate-200"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">Device Type</span>
                        <span id="dev-type" class="text-[11px] font-bold text-slate-700 dark:text-slate-200 uppercase"></span>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded border border-slate-100 dark:border-slate-800">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Connectivity & Location</label>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">IP Address</span>
                        <span id="dev-ip" class="text-[11px] font-bold text-slate-700 dark:text-slate-200 font-mono"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">Origin Country</span>
                        <span id="dev-country" class="text-[11px] font-bold text-slate-700 dark:text-slate-200"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline & Trust --}}
        <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-4">Security Trust Timeline</label>
            <div class="flex items-center justify-between">
                <div class="text-center">
                    <p class="text-[9px] text-slate-400 uppercase mb-1">First Trusted</p>
                    <p id="dev-created" class="text-xs font-bold text-slate-700 dark:text-slate-200"></p>
                </div>
                <div class="flex-1 border-t border-dashed border-slate-200 dark:border-slate-800 mx-4"></div>
                <div class="text-center">
                    <p class="text-[9px] text-slate-400 uppercase mb-1">Last Activity</p>
                    <p id="dev-last-seen" class="text-xs font-bold text-slate-700 dark:text-slate-200"></p>
                </div>
                <div class="flex-1 border-t border-dashed border-slate-200 dark:border-slate-800 mx-4"></div>
                <div class="text-center">
                    <p class="text-[9px] text-slate-400 uppercase mb-1">Trust Expires</p>
                    <p id="dev-expires" class="text-xs font-bold text-indigo-500"></p>
                </div>
            </div>
        </div>

        {{-- Fingerprint --}}
        <div>
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Device Fingerprint Hash</label>
            <div class="p-3 bg-slate-950 rounded border border-slate-800 flex items-center justify-between group">
                <code id="dev-fingerprint" class="text-[10px] font-mono text-emerald-500 break-all leading-tight"></code>
                <button onclick="copyToClipboard(document.getElementById('dev-fingerprint').textContent)" class="p-1.5 text-slate-500 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-7 10h7m-7-3h7'/></svg>
                </button>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'/></svg>
                Hardware-Level Identity Verified
            </div>
            <button onclick="AppModal.close('deviceDetailsModal')" class="modal-btn-cancel">Close Profile</button>
        </div>
    </x-slot>
</x-app-modal>
