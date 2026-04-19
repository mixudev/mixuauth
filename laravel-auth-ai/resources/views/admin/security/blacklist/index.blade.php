@extends('layouts.app-dashboard')

@section('title', 'IP Blacklist - MixuAuth')
@section('page-title', 'IP Blacklist')
@section('page-sub', 'Kelola daftar alamat IP yang dilarang mengakses sistem secara permanen.')

@section('content')
<div class="space-y-6">
    <!-- Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('admin.security.blacklist.index') }}" method="GET" class="relative group max-w-sm w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-600 dark:text-slate-300 shadow-sm"
                placeholder="Cari IP atau alasan...">
        </form>

        <button onclick="document.getElementById('addBlacklistModal').classList.remove('hidden')" 
            class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 dark:bg-indigo-600 hover:bg-slate-800 dark:hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-indigo-500/10">
            <i class="fa-solid fa-plus text-[10px]"></i>
            Tambah IP Blacklist
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20">
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">IP Address</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Reason / Context</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">Added By</th>
                        <th class="px-6 py-4 text-[10px] font-mono uppercase tracking-wider text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($blacklist as $ip)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded bg-red-50 dark:bg-red-900/10 flex items-center justify-center text-red-500">
                                    <i class="fa-solid fa-ban text-[11px]"></i>
                                </div>
                                <span class="text-xs font-bold font-mono text-slate-700 dark:text-slate-200">{{ $ip->ip_address }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $ip->reason }}">
                                {{ $ip->reason ?? 'Tidak ada alasan.' }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">@humanstime($ip->created_at)</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-medium text-slate-600 dark:text-slate-400">
                                {{ $ip->blocked_by ?? 'System' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="confirmDelete('{{ $ip->id }}', '{{ $ip->ip_address }}')" 
                                class="w-8 h-8 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition-all">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                            <form id="delete-form-{{ $ip->id }}" action="{{ route('admin.security.blacklist.destroy', $ip) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-shield-slash text-3xl text-slate-200 dark:text-slate-800 mb-4"></i>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Daftar Blacklist Kosong</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada IP yang diblokir secara manual.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($blacklist->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            {{ $blacklist->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div id="addBlacklistModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('addBlacklistModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
            <form action="{{ route('admin.security.blacklist.store') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Tambah IP Blacklist</h3>
                    <button type="button" onclick="document.getElementById('addBlacklistModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">IP Address</label>
                        <input type="text" name="ip_address" required 
                            class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all dark:text-white"
                            placeholder="e.g. 192.168.1.1">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Alasan Pempensiunan</label>
                        <textarea name="reason" rows="3" 
                            class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all dark:text-white"
                            placeholder="Jelaskan mengapa IP ini diblokir..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addBlacklistModal').classList.add('hidden')" 
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition-all">
                        Blokir IP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, ip) {
        if(confirm(`Apakah Anda yakin ingin menghapus IP ${ip} dari daftar Blacklist?`)) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
</script>
@endsection
