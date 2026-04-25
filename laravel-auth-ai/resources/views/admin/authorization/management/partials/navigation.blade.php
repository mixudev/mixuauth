<div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 pb-px">
    <button 
        onclick="switchSection('roles')" 
        id="nav-roles"
        class="nav-tab group relative pb-4 text-xs font-bold transition-all duration-300 text-indigo-600 dark:text-indigo-400"
    >
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span>Manajemen Role</span>
        </div>
        <div id="line-roles" class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-600 dark:bg-indigo-400"></div>
    </button>

    <button 
        onclick="switchSection('permissions')" 
        id="nav-permissions"
        class="nav-tab group relative pb-4 text-xs font-bold transition-all duration-300 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
    >
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Daftar Permission</span>
        </div>
        <div id="line-permissions" class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 transition-all duration-300"></div>
    </button>

    <button 
        onclick="switchSection('assignment')" 
        id="nav-assignment"
        class="nav-tab group relative pb-4 text-xs font-bold transition-all duration-300 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
    >
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <span>Penetapan Role (Assign)</span>
        </div>
        <div id="line-assignment" class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 transition-all duration-300"></div>
    </button>
</div>
