<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME') }} — @yield('title')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/icon/logo-2.png') }}">

    <script>
        (function () {
            // Theme Check
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Sidebar Check (Run ASAP to prevent flash)
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.documentElement.classList.add('sidebar-is-collapsed');
            }
        })();
    </script>

    <style>
        /* Instant width without waiting for external CSS */
        html.sidebar-is-collapsed #sidebar { width: 5rem !important; }
        html.sidebar-is-collapsed .main-wrapper { padding-left: 5rem !important; }
        html.sidebar-is-collapsed #sidebar .sidebar-label,
        html.sidebar-is-collapsed #sidebar .sidebar-badge,
        html.sidebar-is-collapsed #sidebar .sidebar-dot-badge,
        html.sidebar-is-collapsed #sidebar .sidebar-section-title,
        html.sidebar-is-collapsed #sidebar #logo-text,
        html.sidebar-is-collapsed #sidebar .sidebar-user-info,
        html.sidebar-is-collapsed #sidebar .sidebar-user-chevron { display: none !important; }
    </style>

    <script src="//unpkg.com/alpinejs" defer></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />


    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ time() }}" />
    <script src="{{ asset('assets/js/command-palette.js') }}?v={{ time() }}" defer></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['DM Mono', 'monospace']
                    }
                }
            }
        }
    </script>

</head>

<body class="font-sans bg-gray-200 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased">

    <!-- Main Command Palette -->
    <x-command-palette id="commandPalette" />

    <!-- Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden lg:hidden"
        onclick="toggleSidebar()"></div>

    <!-- ════════════ SIDEBAR ════════════ -->
    @include('partials.dashboard.sidebar')

    <!-- ════════════ MAIN ════════════ -->
    <div id="mainWrapper" class="main-wrapper flex flex-col min-h-screen">

        <!-- HEADER -->
        @include('partials.dashboard.header')

        <!-- CONTENT -->
        <main class="flex-1 p-5 md:p-8">

            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center gap-2 text-xs font-mono text-slate-400 mb-1.5">
                    <span>MixuAuth</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span id="breadcrumb">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>

            @yield('content')

        </main>

        <!-- FOOTER -->
        <footer
            class="px-8 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between flex-wrap gap-3">
            <div class="text-xs text-slate-400 font-mono">MixuAuth v2.4.1 · Identity Platform</div>
            <div class="flex items-center gap-4">
                <a href="#"
                    class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Docs</a>
                <a href="#"
                    class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Status</a>
                <a href="#"
                    class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Support</a>
            </div>
        </footer>
    </div>

    {{-- @include('components.app-popup') --}}

    <x-app-popup />

    <script>
        // ─── TOOLTIP POSITIONING (fixed position needs JS for Y coord)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                const tooltip = this.querySelector('.sidebar-tooltip');
                if (!tooltip) return;
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top + rect.height / 2) + 'px';
            });
        });

        // ─── DARK MODE
        function isDark() {
            return document.documentElement.classList.contains('dark');
        }

        function toggleDark() {
            document.documentElement.classList.toggle('dark');
            document.getElementById('iconMoon').classList.toggle('hidden');
            document.getElementById('iconSun').classList.toggle('hidden');
            localStorage.setItem('theme', isDark() ? 'dark' : 'light');
            setTimeout(() => {
                rebuildCharts();
            }, 50);
        }


        // ─── SIDEBAR (mobile slide)
        function toggleSidebar() {
            const s = document.getElementById('sidebar'),
                o = document.getElementById('sidebarOverlay');
            const isOpen = !s.classList.contains('-translate-x-full');
            s.classList.toggle('-translate-x-full', isOpen);
            o.classList.toggle('hidden', isOpen);
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) document.getElementById('sidebarOverlay').classList.add('hidden');
        });

        // ─── COLLAPSIBLE SIDEBAR (desktop)
        let sidebarCollapsed = false;

        function applyCollapseState(collapsed) {
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('mainWrapper');

            if (collapsed) {
                document.documentElement.classList.add('sidebar-is-collapsed');
                sidebar.classList.add('collapsed');
                mainWrapper.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-is-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-is-collapsed');
                sidebar.classList.remove('collapsed');
                mainWrapper.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-is-collapsed');
            }
        }

        function toggleCollapse() {
            sidebarCollapsed = !sidebarCollapsed;
            applyCollapseState(sidebarCollapsed, true);
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed ? '1' : '0');
        }

        // Restore state on load
        (function initCollapse() {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                sidebarCollapsed = true;
                applyCollapseState(true);
            }
        })();

        // ─── NAV
        const pageLabels = {
            dashboard: 'Dashboard',
            applications: 'Applications',
            users: 'Users',
            access_management: 'Access Management',
            roles: 'Roles',
            permissions: 'Permissions',
            logs: 'Authentication Logs',
            apikeys: 'API Keys',
            security: 'Security Settings',
            settings: 'System Settings',
            activity_logs: 'Log Aktivitas',
            other_logs: 'Lain-lain'
        };
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.classList.contains('dropdown-trigger')) {
                    e.preventDefault();
                    // If collapsed, expand first
                    if (sidebarCollapsed) {
                        toggleCollapse();
                    }

                    const isOpen = this.classList.contains('dropdown-open');

                    // Close other dropdowns if any (optional, keeping it simple for now)

                    if (!isOpen) {
                        this.classList.add('dropdown-open');
                    } else {
                        this.classList.remove('dropdown-open');
                    }
                    return;
                }

                const href = this.getAttribute('href');
                if (href && href !== '#' && href.trim() !== '') {
                    // Let the browser navigate naturally instead of blocking it
                    return;
                }
                
                e.preventDefault();
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const page = this.dataset.page;
                if (page) {
                    document.getElementById('pageTitle').textContent = pageLabels[page] || page;
                    document.getElementById('breadcrumb').textContent = pageLabels[page] || page;
                    showToast('Navigasi', 'Halaman ' + (pageLabels[page] || page) + ' dimuat', 'info');
                }
                if (window.innerWidth < 1024) toggleSidebar();
            });
        });

        // ─── DROPDOWNS
        function toggleDropdown() {
            document.getElementById('profileDropdown').classList.toggle('hidden');
        }

        function toggleNotif() {
            const p = document.getElementById('notifPanel');
            p.classList.toggle('open');
            if (p.classList.contains('open')) fetchNotifs();
        }
        document.addEventListener('click', e => {
            if (!document.getElementById('profileDropdownWrapper').contains(e.target)) document.getElementById(
                'profileDropdown').classList.add('hidden');
            if (!document.getElementById('notifWrapper').contains(e.target)) document.getElementById('notifPanel')
                .classList.remove('open');
        });

        // ─── NOTIFICATIONS
        async function fetchNotifs() {
            try {
                const req = await fetch('/dashboard/notifications/api');
                const res = await req.json();
                
                const list = document.getElementById('notifList');
                const badge = document.getElementById('notifBadge');
                if(!list) return;
                
                if (res.count > 0) {
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                if (res.data.length === 0) {
                    list.innerHTML = '<div class="px-4 py-6 text-center text-xs text-slate-500 font-medium">Semua aman. Tidak ada notifikasi baru! 🎉</div>';
                    return;
                }

                list.innerHTML = '';
                res.data.forEach(n => {
                    let bg, text, icon;
                    if (n.type === 'error' || n.type === 'failed') {
                        bg = 'bg-red-100 dark:bg-red-900/40';
                        text = 'text-red-500';
                        icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    } else if (n.type === 'warning') {
                        bg = 'bg-amber-100 dark:bg-amber-900/40';
                        text = 'text-amber-500';
                        icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />';
                    } else {
                        bg = 'bg-blue-100 dark:bg-blue-900/40';
                        text = 'text-blue-500';
                        icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    }

                    // ── Safe DOM builder — tidak ada innerHTML untuk data dinamis ──
                    const item = document.createElement('div');
                    item.className = 'notif-item px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex gap-3 items-start relative group';

                    // Icon bubble (SVG internal — aman pakai innerHTML)
                    const iconSpan = document.createElement('span');
                    iconSpan.className = `w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5 ${bg} ${text}`;
                    iconSpan.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${icon}</svg>`;
                    item.appendChild(iconSpan);

                    // Text content — textContent mencegah XSS
                    const body = document.createElement('div');
                    body.className = 'flex-1 min-w-0';

                    const titleEl = document.createElement('div');
                    titleEl.className = 'text-[11px] font-semibold text-slate-800 dark:text-slate-200';
                    titleEl.textContent = n.title;

                    const msgEl = document.createElement('div');
                    msgEl.className = 'text-[10px] text-slate-500 dark:text-slate-400 mt-0.5';
                    msgEl.style.lineHeight = '1.3';
                    msgEl.textContent = n.message;

                    const timeEl = document.createElement('div');
                    timeEl.className = 'text-[9px] font-mono text-slate-400 mt-1';
                    timeEl.textContent = n.time_ago;

                    body.appendChild(titleEl);
                    body.appendChild(msgEl);
                    body.appendChild(timeEl);
                    item.appendChild(body);

                    // Unread dot
                    if (!n.read_at) {
                        const dot = document.createElement('span');
                        dot.className = 'unread-dot w-1.5 h-1.5 rounded-full bg-violet-500 flex-shrink-0 mt-1.5';
                        item.appendChild(dot);
                    }

                    // Delete button
                    const actions = document.createElement('div');
                    actions.className = 'absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity';
                    const delBtn = document.createElement('button');
                    delBtn.className = 'p-1 text-slate-400 hover:text-red-500 transition-colors';
                    delBtn.title = 'Delete';
                    delBtn.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                    delBtn.addEventListener('click', (e) => deleteNotif(n.id, e));
                    actions.appendChild(delBtn);
                    item.appendChild(actions);

                    list.appendChild(item);
                });

            } catch(e) { console.error('Failed fetching notif', e); }
        }

        async function clearNotifs() {
            try {
                await fetch('/dashboard/notifications/api/read-all', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                
                // Clear UI gracefully
                const dots = document.querySelectorAll('.unread-dot');
                dots.forEach(el => el.remove());
                document.getElementById('notifBadge').style.display = 'none';
                showToast('Notifikasi', 'Semua notifikasi keamanan ditandai telah dibaca', 'success');
                fetchNotifs(); // Refresh content
            } catch(e) { }
        }

        async function deleteNotif(id, event) {
            if(event) event.stopPropagation();
            
            try {
                const res = await fetch(`/dashboard/notifications/api/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if(res.ok) {
                    fetchNotifs();
                    showToast('Sukses', 'Notifikasi dihapus', 'success');
                } else {
                    const err = await res.json();
                    showToast('Gagal', err.message || 'Gagal menghapus', 'error');
                }
            } catch(e) { 
                console.error('Delete failed', e);
                showToast('Error', 'Kesalahan jaringan', 'error');
            }
        }

        // ─── INIT
        window.addEventListener('load', () => {
            fetchNotifs();
        });

        // Function to shorten numbers (e.g. 1500 -> 1.5K, 2000000 -> 2JT)
        function shortNumber(n) {
            n = Number(n);

            if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1).replace('.0','') + 'B';
            if (n >= 1_000_000)     return (n / 1_000_000).toFixed(1).replace('.0','') + 'JT';
            if (n >= 1_000)         return (n / 1_000).toFixed(1).replace('.0','') + 'K';

            return n.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.short-number').forEach(el => {
                const raw = el.dataset.value;
                if (raw !== undefined) {
                    el.textContent = shortNumber(raw);
                }
            });

            // ─── CLOCK UPDATE
            const clockEl = document.getElementById('header-clock');
            if (clockEl) {
                const tz = '{{ app(\App\Modules\Timezone\Services\TimezoneService::class)->getUserTimezone() }}';
                const formatter = new Intl.DateTimeFormat('en-GB', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: false, timeZone: tz
                });
                
                const updateClock = () => {
                    try {
                        // Hilangkan detik jika user hanya minta H:i, tapi di sini saya kasih H:i:s biar keren
                        // Kalau mau H:i saja, hapus second: '2-digit' di atas
                        clockEl.textContent = formatter.format(new Date());
                    } catch (e) { console.error('Clock error', e); }
                };
                
                updateClock();
                setInterval(updateClock, 1000); // Update setiap detik
            }
        });
        
        // ─── GLOBAL TOAST WRAPPER
        window.showToast = function (a, b, c = 'success') {
            if (typeof AppPopup === 'undefined') return;
            
            let type = c, title = a, msg = b;
            const validTypes = ['success', 'error', 'warning', 'info', 'confirm'];
            
            // Handle (type, msg) signature
            if (validTypes.includes(a) && b && arguments.length <= 2) {
                type = a;
                title = a.charAt(0).toUpperCase() + a.slice(1);
                msg = b;
            }
            
            AppPopup.show({
                type: type,
                title: title,
                description: msg,
                showButton: type !== 'success',
                autoClose: type === 'success' ? 3000 : null
            });
        };
    </script>
    @stack('scripts')
</body>

</html>
