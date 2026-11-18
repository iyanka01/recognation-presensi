@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<div class="space-y-6" x-data="guruPageController()">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative w-full sm:w-96">
            <input type="text" x-model="searchQuery" placeholder="Cari guru..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
            <i class="ti ti-search text-slate-400 absolute left-3 top-3 text-base"></i>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <button type="button" @click="openSyncModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto">
                <i class="ti ti-brand-telegram text-base"></i>
                Sinkronkan Telegram
            </button>
            <button type="button" @click="$dispatch('open-guru-import-modal')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto">
                <i class="ti ti-file-spreadsheet text-base"></i>
                Import Excel
            </button>
            <a href="{{ route('guru.create') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-[7px] flex items-center gap-2 transition-all shadow-sm w-full sm:w-auto justify-center">
                <i class="ti ti-plus text-base"></i>
                Tambah Guru
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div x-data="{ selected: [], selectAll: false, toggleAll() { this.selectAll = !this.selectAll; if (this.selectAll) { this.selected = Array.from(document.querySelectorAll('.guru-checkbox:not([style*=\'display: none\'])')).map(cb => cb.value); } else { this.selected = []; } } }" class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border border-slate-200 overflow-hidden">
        
        <!-- Bulk Delete Action Bar -->
        <div x-show="selected.length > 0" x-transition class="bg-rose-50 px-6 py-3 border-b border-rose-100 flex items-center justify-between" style="display: none;">
            <span class="text-sm font-semibold text-rose-700">
                <span x-text="selected.length"></span> guru dipilih
            </span>
            <form action="{{ route('guru.bulkDestroy') }}" method="POST" @submit.prevent="if(selected.length > 0 && confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' guru terpilih?')) $el.submit()">
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
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">USER</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">ROLE / JABATAN</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">STATUS</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">JOINED</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($guru as $index => $g)
                    <tr x-show="isRowVisible('{{ $g->id }}')"
                        class="hover:bg-slate-50/70 transition-colors" :class="{'bg-rose-50/30': selected.includes('{{ $g->id }}')}">
                        <td class="py-3.5 px-4 text-center">
                            <input type="checkbox" value="{{ $g->id }}" x-model="selected" class="guru-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0 cursor-pointer" 
                                     @if($g->foto_wajah)
                                     @click="$dispatch('preview-face', { name: '{{ addslashes($g->nama_lengkap) }}', nip: '{{ $g->nip ?? '-' }}', photo: '{{ asset('storage/' . $g->foto_wajah) }}', registeredAt: '{{ $g->updated_at ? $g->updated_at->format('d M Y H:i') : '-' }}' })"
                                     title="Klik untuk melihat foto pendaftaran wajah"
                                     @else
                                     title="{{ $g->foto_guru ? 'Foto Profil Guru' : 'Belum Ada Foto' }}"
                                     @endif>
                                    @if($g->foto_wajah)
                                        <img src="{{ asset('storage/' . $g->foto_wajah) }}" alt="{{ $g->nama_lengkap }}" class="w-10 h-10 rounded-full object-cover border border-emerald-300 shadow-sm">
                                    @elseif($g->foto_guru)
                                        <img src="{{ asset('storage/' . $g->foto_guru) }}" alt="{{ $g->nama_lengkap }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100">
                                            {{ substr($g->nama_lengkap, 0, 1) }}
                                        </div>
                                    @endif
                                    <!-- Indicator dot at bottom right (like the image) -->
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $g->foto_wajah || $g->face_descriptor ? 'bg-emerald-500' : 'bg-slate-400' }}" title="{{ $g->foto_wajah || $g->face_descriptor ? 'Wajah Terdaftar' : 'Belum Ada Face ID' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-sm leading-tight truncate">{{ $g->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5 truncate">
                                        NIP: {{ $g->nip ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="ti ti-briefcase text-xs"></i>
                                    <span>{{ $g->jabatan ?? 'Guru Pengajar' }}</span>
                                </span>
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $g->jenis_kelamin == 'L' ? 'bg-indigo-50 text-indigo-600' : 'bg-pink-50 text-pink-600' }}" title="{{ $g->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}">
                                    {{ $g->jenis_kelamin }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex flex-col gap-0.5">
                                @if($g->status == 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        Inactive
                                    </span>
                                @endif
                                
                                <span class="inline-flex items-center gap-1 text-[11px] {{ $g->foto_wajah || $g->face_descriptor ? 'text-emerald-600' : 'text-slate-400' }}">
                                    <i class="ti {{ $g->foto_wajah || $g->face_descriptor ? 'ti-face-id' : 'ti-face-id-error' }} text-xs"></i>
                                    {{ $g->foto_wajah || $g->face_descriptor ? 'Face ID Ready' : 'No Face ID' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                            {{ $g->created_at ? $g->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Tombol Reset Wajah --}}
                                @if(!empty($g->face_descriptor) || !empty($g->foto_wajah))
                                <form action="{{ route('guru.reset-face', $g->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset pendaftaran wajah untuk guru {{ addslashes($g->nama_lengkap) }}?\n\nGuru harus memindai ulang wajahnya untuk absensi.');" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-[7px] transition-colors" title="Reset Pendaftaran Wajah">
                                        <i class="ti ti-rotate-clockwise text-base"></i>
                                    </button>
                                </form>
                                @endif

                                <!-- View QR / ID Card Button (Eye icon like the image) -->
                                <button type="button" @click="$dispatch('open-qr', { id: '{{ $g->id }}', name: '{{ addslashes($g->nama_lengkap) }}', code: '{{ $g->nip ?? 'N/A' }}', printUrl: '{{ route('guru.id-card', $g->id) }}' })" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-[7px] transition-colors" title="Lihat QR Code & ID Card">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                                
                                <!-- Edit Button (Pencil icon like the image) -->
                                <a href="{{ route('guru.edit', $g->id) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-[7px] transition-colors" title="Edit">
                                    <i class="ti ti-pencil text-base"></i>
                                </a>

                                <!-- Delete Button (Trash icon like the image) -->
                                <form action="{{ route('guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Hapus guru ini?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-[7px] transition-colors" title="Hapus">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            <i class="ti ti-users-minus text-3xl mb-1 block"></i>
                            Belum ada data guru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer: Pagination matching the user's design -->
        <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-medium">
            <div>
                Menampilkan <span class="font-bold text-slate-700" x-text="filteredTotal === 0 ? 0 : ((currentPage - 1) * perPage + 1)"></span> - <span class="font-bold text-slate-700" x-text="Math.min(currentPage * perPage, filteredTotal)"></span> dari <span class="font-bold text-slate-700" x-text="filteredTotal"></span> guru
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

    <!-- Modal Preview Foto Wajah Guru -->
    <div x-data="{ open: false, data: null }"
         x-on:preview-face.window="
            data = $event.detail;
            open = true;
         "
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         style="display: none;"
         x-transition.opacity>
        
        <div @click.away="open = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in" x-transition.scale.origin.bottom>
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="font-bold text-slate-800 text-sm">Foto Pendaftaran Wajah</h3>
                </div>
                <button @click="open = false" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 flex flex-col items-center text-center">
                <div class="relative w-44 h-44 rounded-2xl overflow-hidden border-4 border-emerald-100 shadow-md mb-4 bg-slate-100 flex items-center justify-center">
                    <img :src="data?.photo" :alt="data?.name" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 right-2 bg-emerald-600/90 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Terverifikasi
                    </span>
                </div>
                <h4 class="font-bold text-base text-slate-800" x-text="data?.name"></h4>
                <p class="text-slate-500 text-xs mt-0.5">NIP: <span class="font-medium text-slate-700" x-text="data?.nip"></span></p>
                <div class="mt-3 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl text-[11px] text-slate-500">
                    Waktu Scan: <span class="font-semibold text-slate-700" x-text="data?.registeredAt"></span>
                </div>
            </div>
            
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="open = false" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all shadow-sm">
                    Tutup
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
                <h3 class="font-semibold text-slate-800">QR Code Guru</h3>
                <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-8 flex flex-col items-center text-center">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-4 inline-block">
                    <div id="qrcode-container"></div>
                </div>
                <h4 class="font-bold text-lg text-slate-800" x-text="data?.name"></h4>
                <p class="text-slate-500 text-sm mt-1">NIP: <span x-text="data?.code"></span></p>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
                <a :href="data?.printUrl" target="_blank" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl transition-colors shadow-sm shadow-sky-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak ID Card
                </a>
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
                                    <p class="text-sm text-slate-500 mb-4">Pastikan guru telah mengirim pesan ke Bot Telegram dengan format <code class="bg-slate-100 px-2 py-1 rounded text-slate-700">/sync No_HP</code> atau <code class="bg-slate-100 px-2 py-1 rounded text-slate-700">/sync NIP</code> sebelum Anda memulai sinkronisasi.</p>
                                    
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

    <!-- Modal Import Guru -->
    <div x-data="{ open: false }" 
         @open-guru-import-modal.window="open = true" 
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
                    
                    <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Import Data Guru</h3>
                                    <p class="text-sm text-slate-500 mb-2">Pilih file Excel (.xlsx) atau CSV untuk mengunggah data guru secara massal. Format NIP dan No HP menggunakan tanda petik satu di depan (misal: <code>'1980...</code>) dan format tanggal lahir menggunakan <strong>DD-MM-YYYY</strong> (contoh: <code>15-01-1980</code>). Password default akun guru yang di-import: <code>guru123</code>.</p>
                                    
                                    <a href="{{ route('guru.downloadTemplate') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm text-emerald-700 hover:text-emerald-800 font-bold mb-5 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2.5 rounded-xl transition-all border border-emerald-200 shadow-sm">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Download Template Excel (.xlsx)</span>
                                    </a>

                                    <div class="mt-2">
                                        <label for="file_import_guru" class="block text-sm font-medium leading-6 text-slate-900">Pilih File (.xlsx, .xls, .csv)</label>
                                        <input type="file" name="file_import" id="file_import_guru" accept=".xlsx,.xls,.csv" required class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-sky-200 rounded-xl cursor-pointer">
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
</div>
@php
    $guruJson = $guru->map(function($g) {
        return [
            'id' => (string) $g->id,
            'search' => strtolower($g->nama_lengkap . ' ' . $g->nip . ' ' . ($g->jabatan ?? ''))
        ];
    })->values();
@endphp
<script>
function guruPageController() {
    const baseSync = typeof telegramSyncBulk === 'function' ? telegramSyncBulk('guru') : {};
    return {
        ...baseSync,
        searchQuery: '',
        allGuru: @json($guruJson),
        currentPage: 1,
        perPage: 10,

        init() {
            this.$watch('searchQuery', () => { this.currentPage = 1; });
        },

        get filteredGuru() {
            if (!this.searchQuery) return this.allGuru;
            const query = this.searchQuery.toLowerCase();
            return this.allGuru.filter(g => g.search.includes(query));
        },

        get filteredTotal() {
            return this.filteredGuru.length;
        },

        get totalPages() {
            return Math.ceil(this.filteredTotal / this.perPage) || 1;
        },

        get pagedIds() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredGuru.slice(start, start + this.perPage).map(g => g.id);
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
