    <aside id="sidebar"
        class="fixed top-0 left-0 h-full bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 z-40 flex flex-col -translate-x-full lg:translate-x-0"
        style="">

        <!-- Logo -->
        <div class="border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
            <div id="sidebar-logo-area" class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/30">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div id="logo-text" class="overflow-hidden">
                    <div
                        class="text-[15px] font-semibold tracking-tight text-slate-900 dark:text-white whitespace-nowrap">
                        MixuAuth</div>
                    <div class="text-[10px] font-mono text-slate-400 uppercase tracking-widest whitespace-nowrap">
                        Identity Platform</div>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 py-4 overflow-y-auto space-y-5 px-3" id="sidebarNav">
            <div>
                <p
                    class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">
                    Main</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('dashboard') }}" data-page="dashboard" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-label="Dashboard">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Dashboard</span>
                            <span class="sidebar-tooltip">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="applications" class="sidebar-link pointer-events-none opacity-50" aria-label="Applications">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Applications</span>
                            <span
                                class="sidebar-badge ml-auto text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-1.5 py-0.5 rounded-md">12</span>
                            <span class="sidebar-tooltip">Applications</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.users.index') }}" data-page="users" class="sidebar-link {{ request()->routeIs('dashboard.users.index') ? 'active' : '' }}" aria-label="Users">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Users</span>

                            <span data-value="{{ $statscount['users'] }}"
                                class="short-number sidebar-badge ml-auto text-[10px] font-mono bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded-md">
                                {{ $statscount['users'] }}
                            </span>

                            <span class="sidebar-tooltip">Users</span>
                            
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p
                    class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">
                    Access Control</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="#" data-page="roles" class="sidebar-link pointer-events-none opacity-50" aria-label="Roles">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Roles</span>
                            <span class="sidebar-tooltip">Roles</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="permissions" class="sidebar-link pointer-events-none opacity-50" aria-label="Permissions">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Permissions</span>
                            <span class="sidebar-tooltip">Permissions</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p
                    class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">
                    Security</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="" data-page="logs" class="sidebar-link " aria-label="Auth Logs">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Auth Logs</span>
                            <span data-value="{{ $statscount['loginLogs'] }}"
                                class="short-number sidebar-badge ml-auto text-[10px] font-mono bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded-md">
                                {{ $statscount['loginLogs'] }}
                            </span>
                            <span class="sidebar-tooltip">Auth Logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.notifications.all') }}" data-page="notifications" class="sidebar-link {{ request()->routeIs('dashboard.notifications.all') ? 'active' : '' }}" aria-label="Notifications">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <i class="fa-solid fa-bell" style="font-size: 15px;"></i>
                            </span>
                            <span class="sidebar-label">Notifications</span>
                            @if(($statscount['securityNotifications'] ?? 0) > 0)
                            <span class="sidebar-badge ml-auto text-[10px] font-mono bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-1.5 py-0.5 rounded-md">
                                {{ $statscount['securityNotifications'] }}
                            </span>
                            @endif
                            <span class="sidebar-tooltip">Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="otps" class="sidebar-link " aria-label="OTP Logs">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <i class="fa-solid fa-key" style="font-size: 15px;"></i>
                            </span>
                            <span class="sidebar-label">OTP Logs</span>
                            <span class="sidebar-tooltip">OTP Logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="devices" class="sidebar-link " aria-label="Trusted Devices">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <i class="fa-solid fa-desktop" style="font-size: 15px;"></i>
                            </span>
                            <span class="sidebar-label">Trusted Devices</span>
                            <span class="sidebar-tooltip">Trusted Devices</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="blacklist" class="sidebar-link " aria-label="IP Blacklist">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <i class="fa-solid fa-ban" style="font-size: 15px;"></i>
                            </span>
                            <span class="sidebar-label">IP Blacklist</span>
                            <span class="sidebar-tooltip">IP Blacklist</span>
                        </a>
                    </li>
                    <li>
                        <a href="" data-page="whitelist" class="sidebar-link " aria-label="IP Whitelist">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <i class="fa-solid fa-check-double" style="font-size: 15px;"></i>
                            </span>
                            <span class="sidebar-label">IP Whitelist</span>
                            <span class="sidebar-tooltip">IP Whitelist</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="apikeys" class="sidebar-link pointer-events-none opacity-50" aria-label="API Keys">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">API Keys</span>
                            <span
                                class="sidebar-dot-badge ml-auto w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                            <span class="sidebar-tooltip">API Keys</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p
                    class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">
                    System</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="#" data-page="security" class="sidebar-link pointer-events-none opacity-50" aria-label="Security Settings">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Security Settings</span>
                            <span class="sidebar-tooltip">Security Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-page="settings" class="sidebar-link pointer-events-none opacity-50" aria-label="System Settings">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">System Settings</span>
                            <span class="sidebar-tooltip">System Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="sidebar-link dropdown-trigger pointer-events-none opacity-50" aria-label="Logs Dropdown">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="sidebar-label">Log</span>
                            <svg class="dropdown-chevron w-3.5 h-3.5 ml-auto text-slate-400 sidebar-label"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span class="sidebar-tooltip">Log</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <div class="sidebar-submenu-inner">
                                <li>
                                    <a href="#" data-page="activity_logs" class="sidebar-link text-xs py-1.5"
                                        aria-label="Log Aktivitas">
                                        <span class="sidebar-label">Log Aktivitas</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" data-page="other_logs" class="sidebar-link text-xs py-1.5"
                                        aria-label="Lain-lain">
                                        <span class="sidebar-label">Lain-lain</span>
                                    </a>
                                </li>
                            </div>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
            <div id="sidebar-footer-area" class="flex items-center">
                <div id="user-card"
                    class="flex items-center gap-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-all duration-300">
                    <div
                        class="w-8 h-8 flex-shrink-0">
                        <img src="https://i.pravatar.cc/300" alt="Avatar" class="w-full h-full rounded-full object-cover">
                    </div>
                    <div class="sidebar-user-info overflow-hidden flex-1">
                        <div class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate whitespace-nowrap">
                            {{ auth()->user()->name ?? '' }}
                        </div>
                        <div class="text-[11px] text-slate-400 truncate whitespace-nowrap">
                            {{ auth()->user()->email ?? '' }}
                        </div>
                    </div>
                    <svg class="sidebar-user-chevron w-4 h-4 text-slate-400 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>
            </div>
        </div>
    </aside>