@extends('layouts.app')

@section('title', 'Data Kelas & Rombel')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    openTingkat: {},
    init() {
        // Secara default buka semua tingkat agar mudah dilihat
        @foreach($groupedKelas as $tingkat => $rombels)
            this.openTingkat['{{ addslashes($tingkat) }}'] = true;
        @endforeach
    },
    toggleTingkat(tingkat) {
        this.openTingkat[tingkat] = !this.openTingkat[tingkat];
    },
    expandAll() {
        @foreach($groupedKelas as $tingkat => $rombels)
            this.openTingkat['{{ addslashes($tingkat) }}'] = true;
        @endforeach
    },
    collapseAll() {
        @foreach($groupedKelas as $tingkat => $rombels)
            this.openTingkat['{{ addslashes($tingkat) }}'] = false;
        @endforeach
    }
}">
    
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative w-full sm:w-96">
            <input type="text" x-model="search" placeholder="Cari tingkat atau nama rombel..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all shadow-sm">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" @click="expandAll()" class="px-3.5 py-2.5 bg-white border border-sky-200 hover:bg-sky-50 text-slate-600 font-medium text-xs rounded-xl transition-all shadow-sm">
                Buka Semua
            </button>
            <button type="button" @click="collapseAll()" class="px-3.5 py-2.5 bg-white border border-sky-200 hover:bg-sky-50 text-slate-600 font-medium text-xs rounded-xl transition-all shadow-sm">
                Tutup Semua
            </button>
            <button onclick="document.getElementById('modal-kelas').classList.remove('hidden')" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium text-sm rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200 w-full sm:w-auto justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kelas / Rombel
            </button>
        </div>
    </div>

    <!-- Grouped Class Accordion List -->
    <div class="space-y-4">
        @forelse($groupedKelas as $tingkat => $rombels)
            @php
                $tingkatClean = \Illuminate\Support\Str::startsWith(strtolower($tingkat), 'kelas') ? $tingkat : 'Kelas ' . $tingkat;
                $totalSiswa = $rombels->sum('siswa_count');
                $totalRombel = $rombels->count();
            @endphp
            <div x-data="{
                tingkatName: '{{ strtolower($tingkatClean) }}',
                rombelNames: '{{ strtolower($rombels->pluck('nama_rombel')->implode(' ')) }}',
                matchesSearch() {
                    if (!search) return true;
                    const s = search.toLowerCase();
                    return this.tingkatName.includes(s) || this.rombelNames.includes(s);
                }
            }" x-show="matchesSearch()" class="bg-white rounded-2xl shadow-sm border border-sky-100 overflow-hidden transition-all duration-200">
                
                <!-- Accordion Header: Klik untuk buka/tutup sub-kelas -->
                <div @click="toggleTingkat('{{ addslashes($tingkat) }}')" class="p-4 sm:p-5 bg-gradient-to-r from-sky-50/70 to-indigo-50/40 hover:from-sky-100/70 hover:to-indigo-100/50 cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-sky-100/80 transition-colors select-none">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center font-bold text-base shadow-sm shadow-sky-300">
                            {{ $tingkat }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-slate-800 text-base sm:text-lg">{{ $tingkatClean }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                    {{ $totalRombel }} Rombel
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    {{ $totalSiswa }} Siswa
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Klik untuk melihat dan mengelola daftar sub-kelas (rombel) di {{ $tingkatClean }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end sm:self-center">
                        <button type="button" @click.stop="openModalAddRombel('{{ addslashes($tingkat) }}')" class="px-3 py-1.5 bg-white hover:bg-sky-50 text-sky-600 border border-sky-200 rounded-lg text-xs font-semibold flex items-center gap-1 shadow-2xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Rombel
                        </button>
                        <div class="w-8 h-8 rounded-lg bg-white border border-sky-200 text-slate-500 flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': openTingkat['{{ addslashes($tingkat) }}'] }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Sub-Kelas / Rombel Table (Muncul saat diklik) -->
                <div x-show="openTingkat['{{ addslashes($tingkat) }}']" x-transition class="overflow-x-auto bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="py-3 px-5 font-semibold w-12 text-center">No</th>
                                <th class="py-3 px-5 font-semibold">Sub Kelas / Rombel</th>
                                <th class="py-3 px-5 font-semibold">Jumlah Siswa</th>
                                <th class="py-3 px-5 font-semibold">Wali Kelas</th>
                                <th class="py-3 px-5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($rombels as $idx => $r)
                            <tr class="hover:bg-sky-50/30 transition-colors">
                                <td class="py-3.5 px-5 text-center text-slate-400 font-medium text-xs">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                        <span class="font-bold text-slate-800 text-sm sm:text-base">{{ $r->nama_rombel }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <a href="{{ route('siswa.index', ['kelas_id' => $r->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition-colors" title="Klik untuk lihat daftar siswa di rombel ini">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ $r->siswa_count }} Siswa
                                    </a>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600">
                                    @if($r->wali_kelas)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs font-bold">
                                                {{ substr($r->wali_kelas->nama_lengkap, 0, 1) }}
                                            </div>
                                            <span class="text-xs font-medium">{{ $r->wali_kelas->nama_lengkap }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('siswa.index', ['kelas_id' => $r->id]) }}" class="px-2.5 py-1.5 text-xs text-sky-600 hover:bg-sky-50 rounded-lg font-medium transition-colors border border-sky-100 flex items-center gap-1" title="Lihat Siswa">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Siswa
                                        </a>
                                        <a href="{{ route('kelas.edit', $r->id) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-amber-100" title="Edit Rombel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <form action="{{ route('kelas.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus rombel {{ $r->nama_rombel }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors border border-rose-100" title="Hapus Rombel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-sky-100 p-12 text-center text-slate-500">
                <div class="w-16 h-16 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h4 class="font-bold text-slate-800 text-base">Belum Ada Data Kelas</h4>
                <p class="text-sm text-slate-400 mt-1 mb-4">Tambahkan kelas dan rombel pertama Anda untuk memulai.</p>
                <button onclick="document.getElementById('modal-kelas').classList.remove('hidden')" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium text-sm rounded-xl inline-flex items-center gap-2 shadow-sm shadow-sky-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Kelas Sekarang
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Kelas / Rombel -->
    @push('modals')
    <div id="modal-kelas" class="fixed inset-0 z-[100] overflow-y-auto hidden" x-data="{
        tingkatValue: '',
        rombels: [''],
        setTingkat(val) {
            this.tingkatValue = val;
        }
    }">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modal-kelas').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10">
                
                <div class="bg-white px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tambah Kelas & Rombel</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan tingkat kelas dan buat satu atau beberapa sub-kelas sekaligus.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-kelas').classList.add('hidden')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                            <input type="text" name="tingkat" id="input-modal-tingkat" x-model="tingkatValue" placeholder="Misal: X, XI, XII, atau 7, 8, 9, 10" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all font-semibold">
                            
                            <!-- Pilihan Cepat Tingkat -->
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[11px] text-slate-400">Pilihan cepat:</span>
                                @foreach(['X', 'XI', 'XII', '7', '8', '9', '10', '11', '12'] as $pilihan)
                                    <button type="button" @click="setTingkat('{{ $pilihan }}')" class="px-2 py-0.5 text-xs rounded-md bg-slate-100 hover:bg-sky-100 text-slate-600 hover:text-sky-700 font-medium transition-colors border border-slate-200">
                                        {{ $pilihan }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Sub Kelas / Rombel <span class="text-rose-500">*</span></label>
                                <span class="text-xs text-sky-600">Bisa buat lebih dari 1</span>
                            </div>
                            <template x-for="(rombel, index) in rombels" :key="index">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" name="nama_rombel[]" x-model="rombels[index]" placeholder="Misal: X-1, X-2, atau 10 IPA 1" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                                    
                                    <button type="button" @click="rombels.splice(index, 1)" x-show="rombels.length > 1" class="p-2.5 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors shrink-0" title="Hapus baris ini">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    
                                    <button type="button" @click="rombels.push('')" x-show="index === rombels.length - 1" class="p-2.5 text-sky-500 bg-sky-50 hover:bg-sky-100 rounded-xl transition-colors shrink-0" title="Tambah rombel lagi">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Wali Kelas (Opsional)</label>
                            <select name="wali_kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="bg-sky-50/30 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl border-t border-sky-100">
                        <button type="button" onclick="document.getElementById('modal-kelas').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-all shadow-sm shadow-sky-300">Simpan Kelas</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endpush
</div>

<script>
function openModalAddRombel(tingkat) {
    const modal = document.getElementById('modal-kelas');
    if (modal) {
        modal.classList.remove('hidden');
        const input = document.getElementById('input-modal-tingkat');
        if (input) {
            input.value = tingkat;
            input.dispatchEvent(new Event('input'));
        }
    }
}
</script>
@endsection
