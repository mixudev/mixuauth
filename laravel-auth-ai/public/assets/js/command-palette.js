window.CommandPalette = {
    isOpen: false,
    activeIndex: -1,
    searchableItems: [], // { title, url, category, icon }
    searchTimeout: null,
    abortController: null, // Untuk membatalkan fetch lama
    
    init() {
        this.el = document.getElementById('commandPalette');
        this.input = document.getElementById('commandPalette-input');
        this.resultsEl = document.getElementById('commandPalette-results');
        
        if (!this.el || !this.input) return;

        this.bindEvents();
        this.bindTrigger();
        this.indexSidebar();
        this.close();
        this.renderInitial();
    },

    bindTrigger() {
        const trigger = document.getElementById('globalSearchTrigger');
        if (trigger) {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
            });
        }
    },

    bindEvents() {
        // Hotkey Ctrl + K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.toggle();
            }
            
            if (this.isOpen) {
                if (e.key === 'Escape') this.close();
                if (e.key === 'ArrowDown') { e.preventDefault(); this.navigate(1); }
                if (e.key === 'ArrowUp') { e.preventDefault(); this.navigate(-1); }
                if (e.key === 'Enter') { e.preventDefault(); this.select(); }
            }
        });

        this.input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            this.handleInput(query);
        });

        // Close on backdrop click (already handled by onclick in blade, but just in case)
    },

    handleInput(query) {
        clearTimeout(this.searchTimeout);

        // Gagalkan pencarian server-side sebelumnya jika ada
        if (this.abortController) {
            this.abortController.abort();
        }

        if (!query) {
            this.renderInitial();
            return;
        }

        // 1. Jalankan pencarian lokal segera
        this.search(query);

        // 2. Debounce untuk pencarian server-side
        if (query.length >= 2) {
            this.searchTimeout = setTimeout(() => this.fetchDynamicResults(query), 300);
        }
    },

    toggle() {
        this.isOpen ? this.close() : this.open();
    },

    open() {
        this.isOpen = true;
        this.el.classList.add('open');
        this.el.classList.remove('invisible');
        this.el.setAttribute('aria-hidden', 'false');
        this.el.inert = false;
        this.input.value = '';
        this.renderInitial();
        
        // Paksa fokus segera
        this.input.focus();
        // Backup fokus jika transisi CSS mengganggu
        setTimeout(() => this.input.focus(), 10);
        setTimeout(() => this.input.focus(), 100);
    },

    close() {
        this.isOpen = false;
        this.el.classList.remove('open');
        this.el.classList.add('invisible');
        this.el.setAttribute('aria-hidden', 'true');
        this.el.inert = true;
        this.input.blur();
    },

    indexSidebar() {
        const links = document.querySelectorAll('.sidebar-link:not(.opacity-50)');
        this.searchableItems = [];
        
        links.forEach(link => {
            const label = link.querySelector('.sidebar-label')?.textContent.trim();
            const url = link.getAttribute('href');
            const iconSvg = link.querySelector('.sidebar-icon svg')?.outerHTML;
            
            if (label && url && url !== '#') {
                this.searchableItems.push({
                    title: label,
                    url: url,
                    category: 'Pages',
                    icon: iconSvg || '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7"/></svg>'
                });
            }
        });

        // Global Actions
        this.searchableItems.push({ title: 'Logout Account', url: '/logout', category: 'Actions', icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>' });
        this.searchableItems.push({ title: 'Toggle Light/Dark Mode', action: 'toggleDark', category: 'Actions', icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>' });
        this.searchableItems.push({ title: 'View System Health', action: 'toggleHealth', category: 'Actions', icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' });
    },

    renderInitial() {
        const initialEl = document.getElementById('commandPalette-initial');
        const dynamicEl = document.getElementById('commandPalette-dynamic');
        const emptyEl = document.getElementById('commandPalette-empty');
        
        if (!initialEl || !dynamicEl || !emptyEl) return;

        initialEl.classList.remove('hidden');
        dynamicEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
        
        const listContainer = document.getElementById('initial-list-container');
        if (!listContainer) return;
        listContainer.innerHTML = '';
        
        this.searchableItems.slice(0, 6).forEach((item, idx) => {
            listContainer.appendChild(this.createItemEl(item, idx));
        });
        
        this.activeIndex = -1;
    },

    search(query) {
        const dynamicEl = document.getElementById('commandPalette-dynamic');
        const initialEl = document.getElementById('commandPalette-initial');
        const emptyEl = document.getElementById('commandPalette-empty');

        initialEl.classList.add('hidden');
        
        const filtered = this.searchableItems.filter(item => 
            item.title.toLowerCase().includes(query.toLowerCase())
        );

        if (filtered.length === 0) {
            dynamicEl.classList.add('hidden');
            emptyEl.classList.remove('hidden'); // Tampilkan empty segera
        } else {
            emptyEl.classList.add('hidden');
            dynamicEl.classList.remove('hidden');
            this.renderResults(filtered);
        }
    },

    async fetchDynamicResults(query) {
        this.abortController = new AbortController();
        const signal = this.abortController.signal;

        try {
            const response = await fetch(`/dashboard/api/global-search?q=${encodeURIComponent(query)}`, { signal });
            if (!response.ok) throw new Error('Search failed');
            const data = await response.json();
            
            // Re-fetch local results to stay fresh
            const localResults = this.searchableItems.filter(item => 
                item.title.toLowerCase().includes(query.toLowerCase())
            );
            
            const combined = [...localResults, ...data];
            
            const dynamicEl = document.getElementById('commandPalette-dynamic');
            const emptyEl = document.getElementById('commandPalette-empty');

            if (combined.length === 0) {
                emptyEl.classList.remove('hidden');
                dynamicEl.classList.add('hidden');
            } else {
                emptyEl.classList.add('hidden');
                this.renderResults(combined);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
            console.error('Search error', e);
        } finally {
            this.abortController = null;
        }
    },

    renderResults(items) {
        const dynamicEl = document.getElementById('commandPalette-dynamic');
        if (!dynamicEl) return;

        dynamicEl.innerHTML = '';
        dynamicEl.classList.remove('hidden');

        const groups = {};
        items.forEach(item => {
            if (!groups[item.category]) groups[item.category] = [];
            groups[item.category].push(item);
        });

        let totalIdx = 0;
        for (const [cat, catItems] of Object.entries(groups)) {
            const title = document.createElement('div');
            title.className = 'palette-category-title';
            title.textContent = cat;
            dynamicEl.appendChild(title);
            
            catItems.forEach(item => {
                dynamicEl.appendChild(this.createItemEl(item, totalIdx++));
            });
        }

        this.activeIndex = totalIdx > 0 ? 0 : -1;
        this.updateActive();
    },

    createItemEl(item, index) {
        const a = document.createElement('a');
        a.className = 'palette-item group';
        a.href = item.url || '#';
        a.dataset.index = index;
        
        if (item.action) {
            a.onclick = (e) => {
                e.preventDefault();
                if (typeof window[item.action] === 'function') {
                    window[item.action]();
                    this.close();
                }
            };
        }

        // ── Safe DOM builder — item.title & item.category via textContent ──
        const iconWrap = document.createElement('div');
        iconWrap.className = 'w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 group-hover:text-violet-500 transition-colors';
        const iconInner = document.createElement('div');
        iconInner.className = 'w-4 h-4';
        // item.icon berasal dari konstanta internal sidebar atau SVG hardcoded — aman
        iconInner.innerHTML = item.icon;
        iconWrap.appendChild(iconInner);

        const textWrap = document.createElement('div');
        textWrap.className = 'flex-1';

        const titleEl = document.createElement('div');
        titleEl.className = 'text-[13px] font-medium text-slate-700 dark:text-slate-100';
        titleEl.textContent = item.title; // textContent — XSS safe

        const catEl = document.createElement('div');
        catEl.className = 'text-[10px] text-slate-400 font-mono uppercase tracking-wider';
        catEl.textContent = item.category; // textContent — XSS safe

        textWrap.appendChild(titleEl);
        textWrap.appendChild(catEl);

        const chevron = document.createElement('div');
        chevron.className = 'text-slate-300 dark:text-slate-600 opacity-0 group-[.active]:opacity-100 transition-opacity';
        chevron.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

        a.appendChild(iconWrap);
        a.appendChild(textWrap);
        a.appendChild(chevron);

        a.onmouseenter = () => {
            this.activeIndex = index;
            this.updateActive();
        };

        return a;
    },

    navigate(dir) {
        const items = document.querySelectorAll('.palette-item');
        if (items.length === 0) return;

        this.activeIndex += dir;
        if (this.activeIndex < 0) this.activeIndex = items.length - 1;
        if (this.activeIndex >= items.length) this.activeIndex = 0;
        
        this.updateActive();
        
        const activeEl = document.querySelector('.palette-item.active');
        if (activeEl) {
            activeEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    },

    updateActive() {
        const items = document.querySelectorAll('.palette-item');
        items.forEach((item, idx) => {
            item.classList.toggle('active', idx === this.activeIndex);
        });
    },

    select() {
        const activeEl = document.querySelector('.palette-item.active');
        if (activeEl) activeEl.click();
    }
};

document.addEventListener('DOMContentLoaded', () => window.CommandPalette.init());
