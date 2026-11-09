@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div class="space-y-6" x-data="siswaPageController()">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative w-full sm:w-96">
            <input type="text" x-model="searchQuery" placeholder="Cari nama, NIS, atau kelas siswa..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all shadow-sm">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <button type="button" @click="openSyncModal()" class="px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm shadow-sky-200 w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                Sinkronkan Telegram
            </button>
            <button type="button" @click="$dispatch('open-import-modal')" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium text-sm rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm shadow-emerald-200 w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import Excel
            </button>
            <a href="{{ route('siswa.create') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-amber-300/40 w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Siswa
            </a>
        </div>
    </div>

    <!-- Filter Buttons: Pilih Kelas Utama & Sub Kelas / Rombel -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-4 sm:p-5 border border-sky-100 shadow-sm space-y-3.5">
        
        <!-- Baris 1: Filter Tingkat Kelas (Button Utama) -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <div class="flex items-center gap-1.5 min-w-36 text-slate-700 font-bold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Pilih Kelas:
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" 
                        @click="selectTingkat('')" 
                        :class="!selectedTingkat ? 'bg-sky-600 text-white shadow-sm shadow-sky-300 font-bold ring-2 ring-sky-300' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    Semua Kelas
                </button>

                @foreach($tingkatList as $t)
                    @php
                        $tClean = \Illuminate\Support\Str::startsWith(strtolower($t), 'kelas') ? $t : 'Kelas ' . $t;
                        $countInTingkat = $kelasList->where('tingkat', $t)->sum('siswa_count');
                    @endphp
                    <button type="button" 
                            @click="selectTingkat('{{ addslashes($t) }}')" 
                            :class="selectedTingkat === '{{ addslashes($t) }}' ? 'bg-sky-600 text-white shadow-sm shadow-sky-300 font-bold ring-2 ring-sky-300' : 'bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>{{ $tClean }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="selectedTingkat === '{{ addslashes($t) }}' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'">
                            {{ $countInTingkat }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Baris 2: Sub-Filter Pilih Rombel (Muncul ketika Tingkat diklik!) -->
        <div x-show="selectedTingkat" x-transition class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-2.5">
            <div class="flex items-center gap-1.5 min-w-36 text-emerald-700 font-bold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Pilih Rombel:
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" 
                        @click="selectRombel('')" 
                        :class="!selectedKelasId ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-300 font-bold ring-2 ring-emerald-300' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    Semua Rombel
                </button>

                <template x-for="r in availableRombels" :key="r.id">
                    <button type="button" 
                            @click="selectRombel(r.id)" 
                            :class="selectedKelasId === r.id ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-300 font-bold ring-2 ring-emerald-300' : 'bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 font-medium'"
                            class="px-3 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span x-text="r.nama_rombel"></span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="selectedKelasId === r.id ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" x-text="r.siswa_count"></span>
                    </button>
                </template>

                <button type="button" @click="resetFilter()" class="px-2.5 py-1 text-xs text-rose-500 hover:bg-rose-50 rounded-lg transition-colors ml-auto flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table Siswa -->
    <div x-data="{ selected: [], selectAll: false, toggleAll() { this.selectAll = !this.selectAll; if (this.selectAll) { this.selected = Array.from(document.querySelectorAll('.siswa-checkbox:not([style*=\'display: none\'])')).map(cb => cb.value); } else { this.selected = []; } } }" class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        
        <!-- Bulk Delete Action Bar -->
        <div x-show="selected.length > 0" x-transition class="bg-rose-50/80 px-6 py-3 border-b border-rose-100 flex items-center justify-between" style="display: none;">
            <span class="text-sm font-medium text-rose-700">
                <span x-text="selected.length"></span> siswa dipilih
            </span>
            <form action="{{ route('siswa.bulkDestroy') }}" method="POST" @submit.prevent="if(selected.length > 0 && confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' siswa terpilih?')) $el.submit()">
                @csrf
                <template x-for="id in selected">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-rose-700 shadow-rose-200'" class="px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus Terpilih
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-4 h-4 rounded border-sky-200 text-sky-600 focus:ring-sky-500">
                        </th>
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Siswa</th>
                        <th class="p-4 font-semibold">NIS/NISN</th>
                        <th class="p-4 font-semibold">Kelas & Rombel</th>
                        <th class="p-4 font-semibold">L/P</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($siswa as $index => $s)
                    @php
                        $tRow = $s->kelas ? $s->kelas->tingkat : '';
                        $kRow = $s->kelas_id ?? '';
                        $searchStr = $s->nama_lengkap . ' ' . $s->nis . ' ' . $s->nisn . ' ' . ($s->kelas ? ($s->kelas->tingkat . ' ' . $s->kelas->nama_rombel) : '');
                    @endphp
                    <tr x-show="isRowVisible('{{ addslashes($tRow) }}', '{{ addslashes($kRow) }}', '{{ addslashes($searchStr) }}')"
                        class="hover:bg-sky-50/30 transition-colors" 
                        :class="{'bg-rose-50/30': selected.includes('{{ $s->id }}')}">
                        <td class="p-4 text-center">
                            <input type="checkbox" value="{{ $s->id }}" x-model="selected" class="siswa-checkbox w-4 h-4 rounded border-sky-200 text-sky-600 focus:ring-sky-500">
                        </td>
                        <td class="p-4 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($s->foto_siswa)
                                    <img src="{{ asset('storage/' . $s->foto_siswa) }}" alt="{{ $s->nama_lengkap }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-800">{{ $s->nama_lengkap }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-800 font-medium font-mono text-xs">{{ $s->nis ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $s->nisn ?? '-' }}</div>
                        </td>
                        <td class="p-4 font-medium text-slate-800">
                            @if($s->kelas)
                                <button type="button" @click="selectTingkat('{{ addslashes($s->kelas->tingkat) }}'); selectRombel('{{ $s->kelas_id }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 font-semibold border border-sky-200 transition-colors text-xs" title="Filter rombel ini">
                                    <span>{{ $s->kelas->tingkat }}</span>
                                    <span>•</span>
                                    <span>{{ $s->kelas->nama_rombel }}</span>
                                </button>
                            @else
                                <span class="text-slate-400 text-xs italic">Tanpa Kelas</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md {{ $s->jenis_kelamin == 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }} text-xs font-bold">
                                {{ $s->jenis_kelamin }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $s->status == 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $s->status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="$dispatch('open-qr', { id: '{{ $s->id }}', name: '{{ addslashes($s->nama_lengkap) }}', code: '{{ $s->nis }}', printUrl: '{{ route('siswa.id-card', $s->id) }}' })" class="p-2 text-sky-500 hover:bg-sky-50 rounded-lg transition-colors" title="Lihat QR Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>
                                <a href="{{ route('siswa.edit', $s->id) }}" class="p-2 text-sky-500 hover:bg-sky-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">
                            Belum ada data siswa.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
                                    <p class="text-sm text-slate-500 mb-2">Pilih file Excel (.xlsx) atau CSV untuk mengunggah data siswa secara massal. Format penomoran (NIS, NISN, No HP) menggunakan tanda petik satu di depan (misal: <code>'0081...</code>) dan format tanggal lahir menggunakan <strong>DD-MM-YYYY</strong> (contoh: <code>07-07-2002</code>).</p>
                                        
                                    <a href="{{ route('siswa.downloadTemplate') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm text-emerald-700 hover:text-emerald-800 font-bold mb-5 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2.5 rounded-xl transition-all border border-emerald-200 shadow-sm">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Download Template Excel (.xlsx)</span>
                                    </a>

                                    <div class="mt-2">
                                        <label for="file_import" class="block text-sm font-medium leading-6 text-slate-900">Pilih File (.xlsx, .xls, .csv)</label>
                                        <input type="file" name="file_import" id="file_import" accept=".xlsx,.xls,.csv" required class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-sky-200 rounded-xl cursor-pointer">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-sky-50/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">Mulai Import</button>
                            <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
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

<script>
function siswaPageController() {
    const baseSync = typeof telegramSyncBulk === 'function' ? telegramSyncBulk('siswa') : {};
    return {
        ...baseSync,
        selectedTingkat: '{{ request('tingkat', '') }}',
        selectedKelasId: '{{ request('kelas_id', '') }}',
        searchQuery: '{{ request('q', '') }}',
        allClasses: @json($kelasList),

        get availableRombels() {
            if (!this.selectedTingkat) return [];
            return this.allClasses.filter(k => k.tingkat === this.selectedTingkat);
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
            if (this.selectedKelasId === kelasId) {
                this.selectedKelasId = '';
            } else {
                this.selectedKelasId = kelasId;
            }
        },

        resetFilter() {
            this.selectedTingkat = '';
            this.selectedKelasId = '';
            this.searchQuery = '';
        },

        isRowVisible(tingkat, kelasId, searchText) {
            if (this.selectedTingkat && tingkat !== this.selectedTingkat) {
                return false;
            }
            if (this.selectedKelasId && kelasId !== this.selectedKelasId) {
                return false;
            }
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                if (!searchText.toLowerCase().includes(query)) {
                    return false;
                }
            }
            return true;
        }
    };
}
</script>
@endsection
