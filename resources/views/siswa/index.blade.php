@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div class="space-y-6" x-data="siswaPageController()">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Master Data Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data siswa, rombel, dan cetak ID Card.</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <button type="button" @click="openSyncModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto">
                <i class="ti ti-brand-telegram text-base"></i>
                Sinkronkan Telegram
            </button>
            <button type="button" @click="$dispatch('open-import-modal')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto">
                <i class="ti ti-file-spreadsheet text-base"></i>
                Import Excel
            </button>
            <a href="{{ route('siswa.create') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-[7px] flex items-center gap-2 transition-all shadow-sm w-full sm:w-auto justify-center">
                <i class="ti ti-plus text-base"></i>
                Tambah Siswa
            </a>
        </div>
    </div>

    <!-- Filter Bar Model Dropdown Bertingkat 2 Baris (Sesuai Desain Gambar) -->
    <div class="bg-white rounded-[7px] p-4 sm:p-5 border border-slate-200 shadow-sm space-y-4">
        <form action="{{ route('siswa.index') }}" method="GET" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <div class="space-y-3 flex-1">
                <!-- BARIS 1: Departemen & Tahun Ajaran -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <!-- Dropdown Departemen -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="w-24 text-xs font-bold text-slate-700 whitespace-nowrap">Departemen</label>
                        <select name="departemen_id" onchange="this.form.submit()" class="w-full sm:w-64 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-[7px] text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs">
                            @foreach($departemens as $dep)
                                <option value="{{ $dep->id }}" {{ $selectedDepartemenId == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->kode_departemen }} - {{ $dep->nama_departemen }}
                                </option>
                            @endforeach
                            <option value="all" {{ $selectedDepartemenId === 'all' ? 'selected' : '' }}>-- Semua Departemen --</option>
                        </select>
                    </div>

                    <!-- Tahun Ajaran (Otomatis Aktif) -->
                    <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-4">
                        <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Tahun Ajaran</label>
                        <div class="px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-[7px] text-xs font-mono font-bold text-slate-800 shadow-inner">
                            {{ $tahunAjaranAktif ? $tahunAjaranAktif->tahun_ajaran : '2026/2027' }}
                        </div>
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTahunAjaranId }}">
                    </div>
                </div>

                <!-- BARIS 2: Tingkat & Kelas / Rombel -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="w-24 text-xs font-bold text-slate-700 whitespace-nowrap">Kelas</label>
                        
                        <!-- Dropdown Tingkat -->
                        <select x-model="selectedTingkat" @change="onTingkatChange()" class="w-28 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-[7px] text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs">
                            <option value="">-- Semua --</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>

                        <!-- Dropdown Sub-Kelas / Rombel -->
                        <select x-model="selectedKelasId" :disabled="!selectedTingkat" class="w-full sm:w-56 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-[7px] text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs disabled:bg-slate-100 disabled:text-slate-400">
                            <option value="" x-text="selectedTingkat ? '-- Semua Rombel Tingkat ' + selectedTingkat + ' --' : '-- Pilih Rombel --'"></option>
                            <template x-for="r in availableRombels" :key="r.id">
                                <option :value="r.id" x-text="r.nama_rombel + ' (' + r.siswa_count + ' siswa)'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Input Pencarian Nama / NIS (Opsional) -->
                    <div class="relative w-full sm:w-64 sm:ml-2">
                        <input type="text" x-model="searchQuery" placeholder="Ketik nama atau NIS..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-[7px] text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <i class="ti ti-search text-slate-400 absolute left-2.5 top-2 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Tombol Kaca Pembesar 🔍 (Sesuai Gambar) -->
            <div class="flex items-center justify-end lg:pl-4 border-t lg:border-t-0 lg:border-l border-slate-100 pt-3 lg:pt-0 shrink-0">
                <button type="button" @click="currentPage = 1" class="w-12 h-12 rounded-2xl bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 flex items-center justify-center shadow-sm hover:scale-105 active:scale-95 transition-all cursor-pointer" title="Cari / Terapkan Filter">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <button type="button" @click="resetFilter()" x-show="selectedTingkat || selectedKelasId || searchQuery" class="ml-2 text-xs text-rose-500 hover:text-rose-700 underline font-semibold" style="display: none;">
                    Reset
                </button>
            </div>

        </form>
    </div>

    <!-- Data Table Siswa -->
    <div x-data="{ selected: [], selectAll: false, toggleAll() { this.selectAll = !this.selectAll; if (this.selectAll) { this.selected = Array.from(document.querySelectorAll('.siswa-checkbox:not([style*=\'display: none\'])')).map(cb => cb.value); } else { this.selected = []; } } }" class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border border-slate-200 overflow-hidden">
        
        <!-- Bulk Delete Action Bar -->
        <div x-show="selected.length > 0" x-transition class="bg-rose-50 px-6 py-3 border-b border-rose-100 flex items-center justify-between" style="display: none;">
            <span class="text-sm font-semibold text-rose-700">
                <span x-text="selected.length"></span> siswa dipilih
            </span>
            <form action="{{ route('siswa.bulkDestroy') }}" method="POST" @submit.prevent="if(selected.length > 0 && confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' siswa terpilih?')) $el.submit()">
                @csrf
                <template x-for="id in selected">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-rose-700 shadow-rose-200'" class="px-4 py-2 bg-rose-600 text-white text-xs font-semibold rounded-[7px] transition-colors flex items-center gap-2 shadow-sm">
                    <i class="ti ti-trash text-sm"></i>
                    Hapus Terpilih
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">USER</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">ROLE / KELAS</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">STATUS</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">JOINED</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswa as $index => $s)
                    <tr x-show="isRowVisible('{{ $s->id }}')"
                        class="hover:bg-slate-50/70 transition-colors" 
                        :class="{'bg-rose-50/30': selected.includes('{{ $s->id }}')}">
                        <td class="py-2.5 px-3 text-center">
                            <input type="checkbox" value="{{ $s->id }}" x-model="selected" class="siswa-checkbox w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2.5">
                                <div class="relative shrink-0">
                                    @if($s->foto_siswa)
                                        <img src="{{ asset('storage/' . $s->foto_siswa) }}" alt="{{ $s->nama_lengkap }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100">
                                            {{ substr($s->nama_lengkap, 0, 1) }}
                                        </div>
                                    @endif
                                    <!-- Indicator dot at bottom right -->
                                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white {{ $s->status == 'Aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-xs leading-tight truncate">{{ $s->nama_lengkap }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5 truncate">
                                        NIS: {{ $s->nis ?? '-' }} • NISN: {{ $s->nisn ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-1.5">
                                @if($s->kelas)
                                    <button type="button" @click="selectTingkat('{{ addslashes($s->kelas->tingkat) }}'); selectRombel('{{ $s->kelas_id }}')" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors" title="Filter rombel ini">
                                        <i class="ti ti-school text-[11px]"></i>
                                        <span>{{ $s->kelas->tingkat }} • {{ $s->kelas->nama_rombel }}</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                        Tanpa Kelas
                                    </span>
                                @endif
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[9px] font-bold {{ $s->jenis_kelamin == 'L' ? 'bg-indigo-50 text-indigo-600' : 'bg-pink-50 text-pink-600' }}" title="{{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}">
                                    {{ $s->jenis_kelamin }}
                                </span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            @if($s->status == 'Aktif')
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 whitespace-nowrap text-[11px] text-slate-500 font-medium">
                            {{ $s->created_at ? $s->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- View / QR Button -->
                                <button type="button" @click="$dispatch('open-qr', { id: '{{ $s->id }}', name: '{{ addslashes($s->nama_lengkap) }}', code: '{{ $s->nis }}', printUrl: '{{ route('siswa.id-card', $s->id) }}' })" class="p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-[6px] transition-colors" title="Lihat QR Code & ID Card">
                                    <i class="ti ti-eye text-sm"></i>
                                </button>
                                <!-- Edit Button -->
                                <a href="{{ route('siswa.edit', $s->id) }}" class="p-1 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-[6px] transition-colors" title="Edit">
                                    <i class="ti ti-pencil text-sm"></i>
                                </a>
                                <!-- Delete Button -->
                                <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-[6px] transition-colors" title="Hapus">
                                        <i class="ti ti-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            <i class="ti ti-users-minus text-3xl mb-1 block"></i>
                            Belum ada data siswa.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer: Pagination matching the user's design -->
        <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-medium">
            <div>
                Menampilkan <span class="font-bold text-slate-700" x-text="filteredTotal === 0 ? 0 : ((currentPage - 1) * perPage + 1)"></span> - <span class="font-bold text-slate-700" x-text="Math.min(currentPage * perPage, filteredTotal)"></span> dari <span class="font-bold text-slate-700" x-text="filteredTotal"></span> siswa
            </div>
            <!-- Pagination Controls matching image style -->
            <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                <!-- Prev -->
                <button type="button" @click="prevPage()" :disabled="currentPage === 1" 
                        class="w-8 h-8 rounded-[7px] border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-colors">
                    <i class="ti ti-chevron-left text-xs"></i>
                </button>

                <!-- Page numbers -->
                <template x-for="p in paginationPages" :key="p">
                    <div>
                        <template x-if="p === '...'">
                            <span class="w-8 h-8 flex items-center justify-center text-slate-400 font-semibold">...</span>
                        </template>
                        <template x-if="p !== '...'">
                            <button type="button" @click="goToPage(p)" 
                                    class="w-8 h-8 rounded-[7px] font-semibold text-xs flex items-center justify-center transition-all"
                                    :class="currentPage === p ? 'bg-teal-500 text-white shadow-sm' : 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-700'"
                                    x-text="p">
                            </button>
                        </template>
                    </div>
                </template>

                <!-- Next -->
                <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" 
                        class="w-8 h-8 rounded-[7px] border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-colors">
                    <i class="ti ti-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div x-data="{ open: false, data: null, qrObj: null }"
         x-on:open-qr.window="
            open = true; 
            data = $event.detail;
            $nextTick(() => {
                document.getElementById('qrcode-container').innerHTML = '';
                qrObj = new QRCode(document.getElementById('qrcode-container'), {
                    text: data.id,
                    width: 200,
                    height: 200,
                    colorDark : '#0f172a',
                    colorLight : '#ffffff',
                    correctLevel : QRCode.CorrectLevel.H
                });
            });
         "
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-sky-900/40 backdrop-blur-sm"
         style="display: none;"
         x-transition.opacity>
        
        <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" x-transition.scale.origin.bottom>
            <div class="px-6 py-4 border-b border-sky-100 flex items-center justify-between bg-sky-50/50">
                <h3 class="font-semibold text-slate-800">QR Code Siswa</h3>
                <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-8 flex flex-col items-center text-center">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-4 inline-block">
                    <div id="qrcode-container"></div>
                </div>
                <h4 class="font-bold text-lg text-slate-800" x-text="data?.name"></h4>
                <p class="text-slate-500 text-sm mt-1">NIS: <span x-text="data?.code"></span></p>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
                <a :href="data?.printUrl" target="_blank" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl transition-colors shadow-sm shadow-sky-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak ID Card
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    <div x-data="{ open: false }" 
         @open-import-modal.window="open = true" 
         @keydown.escape.window="open = false" 
         class="relative z-50" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         x-show="open" 
         style="display: none;">
        
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-sky-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Import Data Siswa</h3>
                                    <p class="text-xs text-slate-500 mb-3">Pilih file Excel (.xlsx) atau CSV untuk mengunggah data siswa secara massal. Format penomoran (NIS, NISN, No HP) menggunakan tanda petik satu di depan (misal: <code>'0081...</code>) dan format tanggal lahir menggunakan <strong>DD-MM-YYYY</strong> (contoh: <code>07-07-2002</code>).</p>
                                        
                                    <a href="{{ route('siswa.downloadTemplate') }}" class="inline-flex items-center gap-2 text-xs text-emerald-700 hover:text-emerald-800 font-bold mb-4 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl transition-all border border-emerald-200 shadow-sm">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Download Template Excel (.xlsx)</span>
                                    </a>

                                    @if($departemens->isEmpty())
                                        <!-- Peringatan Jika Departemen Belum Dibuat -->
                                        <div class="p-3.5 mb-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                                            <i class="ti ti-alert-triangle text-amber-600 text-lg shrink-0 mt-0.5"></i>
                                            <div>
                                                <p class="font-bold">Departemen Belum Dibuat!</p>
                                                <p class="mt-0.5 text-amber-800 leading-relaxed">
                                                    Anda belum memiliki data Departemen / Unit Sekolah (misal: SMP atau SMA). Silakan buat Departemen terlebih dahulu sebelum mengimpor siswa.
                                                </p>
                                                <a href="{{ route('departemen.index') }}" class="inline-flex items-center gap-1 font-bold text-amber-900 underline mt-1.5 hover:text-amber-950">
                                                    <span>Buat Departemen Sekarang &rarr;</span>
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Pilihan Departemen Tujuan (Wajib) -->
                                        <div class="mb-3 text-left">
                                            <label for="modal_departemen_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                                Departemen / Unit Sekolah <span class="text-rose-500">*</span>
                                            </label>
                                            <select name="departemen_id" id="modal_departemen_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all shadow-xs">
                                                <option value="">-- Pilih Departemen Tujuan Siswa --</option>
                                                @foreach($departemens as $dep)
                                                    <option value="{{ $dep->id }}" {{ (isset($selectedDepartemenId) && $selectedDepartemenId == $dep->id) ? 'selected' : '' }}>
                                                        🏫 {{ $dep->nama_departemen }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-[11px] text-slate-400 mt-1">Siswa dan rombel kelas pada file akan otomatis didaftarkan ke unit sekolah ini.</p>
                                        </div>
                                    @endif

                                    <div class="text-left">
                                        <label for="file_import" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Pilih File (.xlsx, .xls, .csv) <span class="text-rose-500">*</span></label>
                                        <input type="file" name="file_import" id="file_import" accept=".xlsx,.xls,.csv" required {{ $departemens->isEmpty() ? 'disabled' : '' }} class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button type="submit" {{ $departemens->isEmpty() ? 'disabled' : '' }} class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-500 sm:w-auto transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                Mulai Import
                            </button>
                            <button type="button" @click="open = false" class="mt-2 sm:mt-0 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-100 sm:w-auto transition-colors cursor-pointer">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Telegram Sync Modal -->
    <div x-show="syncModalOpen" 
         class="relative z-50" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         style="display: none;">
        <div x-show="syncModalOpen" class="fixed inset-0 bg-sky-900/40 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="syncModalOpen" @click.away="syncModalOpen = false" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Sinkronisasi Telegram Massal</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">Pastikan wali murid telah mengirim pesan ke Bot Telegram dengan format <code class="bg-slate-100 px-2 py-1 rounded text-slate-700">/sync No_HP_Wali</code> atau <code class="bg-slate-100 px-2 py-1 rounded text-slate-700">/sync NIS</code> sebelum Anda memulai sinkronisasi.</p>
                                    
                                    <div x-show="syncLoading" class="text-center py-4">
                                        <svg class="animate-spin h-8 w-8 text-sky-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm text-slate-500 mt-2">Menyinkronkan data...</p>
                                    </div>

                                    <div x-show="syncResultMessage" class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                                        <p class="text-sm text-emerald-700 font-medium" x-text="syncResultMessage"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-sky-50/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="sync()" :disabled="syncLoading" class="inline-flex w-full justify-center rounded-xl bg-sky-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-600 sm:ml-3 sm:w-auto transition-colors disabled:opacity-50">Mulai Sinkronisasi</button>
                        <button type="button" @click="syncModalOpen = false" :disabled="syncLoading" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal / Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $siswaJson = $siswa->map(function($s) {
        $tingkat = $s->kelas ? $s->kelas->tingkat : '';
        $rombel = $s->kelas ? $s->kelas->nama_rombel : '';
        return [
            'id' => (string) $s->id,
            'tingkat' => (string) $tingkat,
            'kelas_id' => (string) ($s->kelas_id ?? ''),
            'search' => strtolower($s->nama_lengkap . ' ' . $s->nis . ' ' . $s->nisn . ' ' . $tingkat . ' ' . $rombel)
        ];
    })->values();
@endphp

<script>
function siswaPageController() {
    const baseSync = typeof telegramSyncBulk === 'function' ? telegramSyncBulk('siswa') : {};
    return {
        ...baseSync,
        selectedTingkat: '{{ request('tingkat', '') }}',
        selectedKelasId: '{{ request('kelas_id', '') }}',
        searchQuery: '{{ request('q', '') }}',
        allClasses: @json($kelasList),
        allSiswa: @json($siswaJson),
        currentPage: 1,
        perPage: 10,

        init() {
            this.$watch('selectedTingkat', () => { this.currentPage = 1; });
            this.$watch('selectedKelasId', () => { this.currentPage = 1; });
            this.$watch('searchQuery', () => { this.currentPage = 1; });
        },

        get availableRombels() {
            if (!this.selectedTingkat) return [];
            return this.allClasses.filter(k => k.tingkat === this.selectedTingkat);
        },

        onTingkatChange() {
            this.selectedKelasId = '';
        },

        selectTingkat(tingkat) {
            if (this.selectedTingkat === tingkat) {
                this.selectedTingkat = '';
                this.selectedKelasId = '';
            } else {
                this.selectedTingkat = tingkat;
                this.selectedKelasId = '';
            }
        },

        selectRombel(kelasId) {
            if (this.selectedKelasId === String(kelasId)) {
                this.selectedKelasId = '';
            } else {
                this.selectedKelasId = String(kelasId);
            }
        },

        resetFilter() {
            this.selectedTingkat = '';
            this.selectedKelasId = '';
            this.searchQuery = '';
            this.currentPage = 1;
        },

        get filteredSiswa() {
            return this.allSiswa.filter(s => {
                if (this.selectedTingkat && s.tingkat !== this.selectedTingkat) {
                    return false;
                }
                if (this.selectedKelasId && s.kelas_id !== this.selectedKelasId) {
                    return false;
                }
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    if (!s.search.includes(query)) {
                        return false;
                    }
                }
                return true;
            });
        },

        get filteredTotal() {
            return this.filteredSiswa.length;
        },

        get totalPages() {
            return Math.ceil(this.filteredTotal / this.perPage) || 1;
        },

        get pagedIds() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredSiswa.slice(start, start + this.perPage).map(s => s.id);
        },

        isRowVisible(id) {
            return this.pagedIds.includes(String(id));
        },

        get paginationPages() {
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) {
                return Array.from({length: total}, (_, i) => i + 1);
            }
            if (current <= 3) {
                return [1, 2, 3, 4, '...', total];
            }
            if (current >= total - 2) {
                return [1, '...', total - 3, total - 2, total - 1, total];
            }
            return [1, '...', current - 1, current, current + 1, '...', total];
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        }
    };
}
</script>
@endsection
