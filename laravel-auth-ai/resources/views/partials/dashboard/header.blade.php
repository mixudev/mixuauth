        <header
            class="sticky top-0 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-b border-slate-100 dark:border-slate-800 h-[70px] flex items-center px-4 md:px-6 gap-4">

            <!-- Mobile menu toggle -->
            <button onclick="toggleSidebar()"
                class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                style="transition:background 150ms" aria-label="Open sidebar">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Desktop collapse toggle — left of search -->
            <button id="collapseBtn" onclick="toggleCollapse()"
                class="w-9 h-9 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                style="transition:background 150ms" aria-label="Toggle sidebar">
                <svg id="collapseBtnIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Search -->
            <div class="flex-1 max-w-md relative hidden lg:block">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input id="searchInput" type="text" placeholder="Search users, apps, logs..."
                    oninput="handleSearch(this.value)"
                    class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400"
                    style="transition:border 150ms, box-shadow 150ms" />
                <div id="searchResults"
                    class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden py-1">
                </div>
            </div>

            <div class="flex items-center gap-1.5 ml-auto">

                <!-- Dark mode toggle -->
                <button onclick="toggleDark()"
                    class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                    style="transition:background 150ms" title="Toggle dark mode">
                    <svg id="iconMoon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg id="iconSun" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <circle cx="12" cy="12" r="5" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                    </svg>
                </button>

                <!-- Notifications -->
                <div class="relative" id="notifWrapper">
                    <button onclick="toggleNotif()"
                        class="relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                        style="transition:background 150ms">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span id="notifBadge"
                            class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></span>
                    </button>
                    <div id="notifPanel"
                        class="notif-panel absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg shadow-2xl z-50">
                        <div
                            class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Notifications</span>
                            <button onclick="clearNotifs()"
                                class="text-xs text-violet-600 dark:text-violet-400 hover:underline">Mark all
                                read</button>
                        </div>
                        <div id="notifList"
                            class="max-h-72 overflow-y-auto divide-y divide-slate-50 dark:divide-slate-700/50">
                            <div class="px-4 py-8 text-center text-xs text-slate-400">Loading data...</div>
                        </div>
                        <div
                            class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-700 text-xs text-violet-600 dark:text-violet-400 hover:underline cursor-pointer">
                            View all →</div>
                    </div>
                </div>

                <!-- Activity -->
                <button
                    class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                    style="transition:background 150ms">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </button>

                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>

                <!-- Clock Section -->
                <div class="hidden sm:flex flex-col items-end px-3 py-1 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700/50">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="header-clock" class="text-xs font-bold text-slate-800 dark:text-slate-100 tabular-nums">
                            @localtimef(now(), 'H:i')
                        </span>
                    </div>
                    <span class="text-[9px] text-slate-400 font-medium uppercase tracking-wider">@timezone</span>
                </div>

                <!-- Profile -->
                <div class="relative" id="profileDropdownWrapper">
                    <button onclick="toggleDropdown()"
                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                        style="transition:background 150ms">
                        <div class="w-7 h-7 rounded-full  flex  flex-shrink-0">
                            <img src="https://i.pravatar.cc/300" alt="Avatar"
                                class="w-full h-full rounded-full object-cover">
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ auth()->user()->name ?? '' }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="profileDropdown"
                        class="hidden absolute right-0 top-full mt-2 w-52 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-700 mb-1">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ auth()->user()->name ?? '' }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ auth()->user()->email ?? '' }}
                            </div>
                        </div>
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white"
                            style="transition:background 100ms"><svg class="w-4 h-4 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>Profile</a>
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white"
                            style="transition:background 100ms"><svg class="w-4 h-4 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>Account Settings</a>
                        <div class="border-t border-slate-100 dark:border-slate-700 mt-1 pt-1">

                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="button" id="btn-logout"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500
                                        hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1
                                                a3 3 0 01-3 3H6a3 3 0 01-3-3V7
                                                a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </header>

        <script>
            document.getElementById('btn-logout').addEventListener('click', () => {
                AppPopup.confirm({
                    title: 'Logout?',
                    description: 'Sesi kamu akan diakhiri',
                    confirmText: 'Logout',
                    cancelText: 'Batal',
                    confirmClass: 'bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700',
                    onConfirm: () => {
                        document.getElementById('logout-form').submit();
                    }
                });
            });
        </script>
