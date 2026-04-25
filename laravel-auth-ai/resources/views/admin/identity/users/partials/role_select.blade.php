@props(['id', 'roles'])

<div x-data="{ 
    open: false, 
    search: '', 
    allRoles: {{ json_encode($roles->map(fn($r) => ['slug' => $r->slug, 'name' => $r->name])) }},
    selected: [],
    get filteredRoles() {
        if (!this.search) return this.allRoles;
        return this.allRoles.filter(r => r.name.toLowerCase().includes(this.search.toLowerCase()));
    },
    toggle(slug) {
        if (!slug) return;
        if (this.selected.includes(slug)) {
            this.selected = this.selected.filter(s => s !== slug);
        } else {
            this.selected.push(slug);
        }
        this.sync();
    },
    sync() {
        this.$nextTick(() => {
            const select = document.getElementById('{{ $id }}');
            if (select) {
                Array.from(select.options).forEach(opt => {
                    opt.selected = this.selected.includes(opt.value);
                });
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
}" 
x-init="sync()"
x-on:set-{{ $id }}-roles.window="selected = $event.detail; sync()"
class="relative mt-1">
    
    <!-- Selection Display -->
    <div @click="open = !open" 
         class="min-h-[42px] w-full p-2 rounded-lg bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-white/10 cursor-pointer flex flex-wrap gap-1.5 items-center pr-10 relative transition-all hover:border-slate-300 dark:hover:border-white/20">
        
        <template x-if="selected.length === 0">
            <span class="text-slate-400 text-[13px] ml-1">Pilih role pengguna...</span>
        </template>
        
        <template x-for="slug in selected" :key="slug">
            <div class="bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 border border-indigo-100 dark:border-indigo-500/20 transition-all">
                <span x-text="allRoles.find(r => r.slug === slug)?.name"></span>
                <button type="button" @click.stop="toggle(slug)" class="text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-200 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 flex items-center gap-1">
            <div x-show="selected.length > 0" @click.stop="selected = []; sync()" class="p-1 hover:text-red-500 transition-colors mr-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>

    <!-- Dropdown -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute z-[100] w-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-sm shadow-2xl overflow-hidden">
        
        <div class="p-2 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-black/20">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input x-model="search" type="text" placeholder="Cari role..." 
                       @click.stop
                       class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10 text-[13px] rounded-lg pl-8 pr-3 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
            </div>
        </div>

        <div class="max-h-56 overflow-y-auto p-1.5 custom-scrollbar">
            <template x-for="role in filteredRoles" :key="role.slug">
                <div @click="toggle(role.slug)" 
                     class="group flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-white/5"
                     :class="selected.includes(role.slug) ? 'bg-indigo-50/50 dark:bg-indigo-500/5' : ''">
                    
                    <div class="flex flex-col">
                        <span class="text-[13px] font-semibold transition-colors" 
                              :class="selected.includes(role.slug) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white'" 
                              x-text="role.name"></span>
                        <span class="text-[10px] text-slate-400 font-mono" x-text="role.slug"></span>
                    </div>

                    <div class="w-5 h-5 rounded-md border flex items-center justify-center transition-all duration-200"
                         :class="selected.includes(role.slug) ? 'bg-indigo-600 border-indigo-600 scale-110 shadow-lg shadow-indigo-500/20' : 'border-slate-300 dark:border-white/10 bg-white dark:bg-slate-800'">
                        <svg x-show="selected.includes(role.slug)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-transition:enter="transition scale-0" x-transition:enter-end="scale-100"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </template>
            
            <div x-show="filteredRoles.length === 0" class="py-8 px-4 text-center">
                <div class="w-10 h-10 bg-slate-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-xs text-slate-400">Role tidak ditemukan.</p>
            </div>
        </div>
    </div>

    <!-- Legacy Hidden Select (for compatibility with submission JS) -->
    <select :id="'{{ $id }}'" multiple class="hidden">
        @foreach($roles as $role)
            <option value="{{ $role->slug }}">{{ $role->name }}</option>
        @endforeach
    </select>
</div>
