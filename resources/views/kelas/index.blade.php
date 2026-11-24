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
    
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-4 rounded-[7px] border border-slate-200 shadow-sm">
        
        <!-- Filter Departemen Utama -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="flex items-center gap-2">
                <i class="ti ti-building text-blue-600 text-lg"></i>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider whitespace-nowrap">Departemen:</span>
            </div>
            <form action="{{ route('kelas.index') }}" method="GET" class="flex-1 md:w-64">
                <select name="departemen_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-[7px] text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all shadow-xs">
                    @foreach($departemens as $dep)
                        <option value="{{ $dep->id }}" {{ $selectedDepartemenId == $dep->id ? 'selected' : '' }}>
                            {{ $dep->nama_departemen }} ({{ $dep->tingkatan }})
                        </option>
                    @endforeach
                    <option value="all" {{ $selectedDepartemenId === 'all' ? 'selected' : '' }}>-- Semua Departemen --</option>
                </select>
            </form>
        </div>

        <div class="relative w-full md:w-72">
            <input type="text" x-model="search" placeholder="Cari tingkat atau rombel..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-[7px] text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-xs">
            <i class="ti ti-search text-slate-400 absolute left-3 top-2.5 text-sm"></i>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="button" @click="expandAll()" class="px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-[7px] transition-all shadow-xs flex items-center gap-1">
                <i class="ti ti-arrows-maximize text-xs"></i>
                Buka
            </button>
            <button type="button" @click="collapseAll()" class="px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-[7px] transition-all shadow-xs flex items-center gap-1">
                <i class="ti ti-arrows-minimize text-xs"></i>
                Tutup
            </button>
            <button onclick="document.getElementById('modal-kelas').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-1.5 transition-all shadow-xs justify-center whitespace-nowrap">
                <i class="ti ti-plus text-sm"></i>
                Tambah Kelas
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
            }" x-show="matchesSearch()" class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border border-slate-200 overflow-hidden transition-all duration-200">
                
                <!-- Accordion Header: Klik untuk buka/tutup sub-kelas -->
                <div @click="toggleTingkat('{{ addslashes($tingkat) }}')" class="p-4 sm:p-5 bg-slate-50/80 hover:bg-slate-100/70 cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 transition-colors select-none">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="px-3 py-2 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0 whitespace-nowrap">
                            {{ $tingkatClean }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-slate-800 text-base sm:text-lg leading-tight">{{ $tingkatClean }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                                    {{ $totalRombel }} Rombel
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                    {{ $totalSiswa }} Siswa
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">Daftar sub-kelas (rombel) pada {{ $tingkatClean }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end sm:self-center">
                        <button type="button" @click.stop="openModalAddRombel('{{ addslashes($tingkat) }}')" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-blue-600 border border-slate-200 rounded-[7px] text-xs font-semibold flex items-center gap-1 shadow-sm transition-colors">
                            <i class="ti ti-plus text-xs"></i>
                            + Rombel
                        </button>
                        <div class="w-8 h-8 rounded-[7px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': openTingkat['{{ addslashes($tingkat) }}'] }">
                            <i class="ti ti-chevron-down text-base"></i>
                        </div>
                    </div>
                </div>

                <!-- Sub-Kelas / Rombel Table (Muncul saat diklik) -->
                <div x-show="openTingkat['{{ addslashes($tingkat) }}']" x-transition class="overflow-x-auto bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-3 px-5 w-12 text-center text-slate-600">No</th>
                                <th class="py-3 px-5 text-slate-600">Sub Kelas / Rombel</th>
                                <th class="py-3 px-5 text-slate-600">Jumlah Siswa</th>
                                <th class="py-3 px-5 text-slate-600">Wali Kelas</th>
                                <th class="py-3 px-5 text-slate-600 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($rombels as $idx => $r)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-5 text-center text-slate-400 font-medium text-xs">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                        <span class="font-bold text-slate-800 text-sm">{{ $r->nama_rombel }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <a href="{{ route('siswa.index', ['kelas_id' => $r->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs border border-emerald-200 transition-colors" title="Lihat siswa di rombel ini">
                                        <i class="ti ti-users text-xs"></i>
                                        <span>{{ $r->siswa_count }} Siswa</span>
                                    </a>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600">
                                    @if($r->wali_kelas)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">
                                                {{ substr($r->wali_kelas->nama_lengkap, 0, 1) }}
                                            </div>
                                            <span class="text-xs font-semibold text-slate-700">{{ $r->wali_kelas->nama_lengkap }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- View Siswa (Eye Icon) -->
                                        <a href="{{ route('siswa.index', ['kelas_id' => $r->id]) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-[7px] transition-colors" title="Lihat Daftar Siswa">
                                            <i class="ti ti-eye text-base"></i>
                                        </a>
                                        <!-- Edit Rombel (Pencil Icon) -->
                                        <a href="{{ route('kelas.edit', $r->id) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-[7px] transition-colors" title="Edit Rombel">
                                            <i class="ti ti-pencil text-base"></i>
                                        </a>
                                        <!-- Delete Rombel (Trash Icon) -->
                                        <form action="{{ route('kelas.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus rombel {{ $r->nama_rombel }}?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-[7px] transition-colors" title="Hapus Rombel">
                                                <i class="ti ti-trash text-base"></i>
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
            <div class="bg-white rounded-[7px] shadow-sm border border-slate-200 p-12 text-center text-slate-500">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-[7px] flex items-center justify-center mx-auto mb-3">
                    <i class="ti ti-school-off text-3xl"></i>
                </div>
                <h4 class="font-bold text-slate-800 text-base">Belum Ada Data Kelas</h4>
                <p class="text-sm text-slate-400 mt-1 mb-4">Tambahkan kelas dan rombel pertama Anda untuk memulai.</p>
                <button onclick="document.getElementById('modal-kelas').classList.remove('hidden')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] inline-flex items-center gap-2 shadow-sm">
                    <i class="ti ti-plus text-sm"></i>
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

            <div class="inline-block transform overflow-hidden rounded-[7px] bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 border border-slate-200">
                
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Tambah Kelas & Rombel</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan tingkat kelas dan buat satu atau beberapa sub-kelas sekaligus.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-kelas').classList.add('hidden')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-[7px] hover:bg-slate-200 transition-colors">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        <!-- Pilihan Departemen / Instansi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Departemen / Instansi Sekolah <span class="text-rose-500">*</span></label>
                            <select name="departemen_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-semibold">
                                @foreach($departemens as $dep)
                                    <option value="{{ $dep->id }}" {{ $selectedDepartemenId == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->nama_departemen }} ({{ $dep->tingkatan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                            <input type="text" name="tingkat" id="input-modal-tingkat" x-model="tingkatValue" placeholder="Misal: X, XI, XII, atau 7, 8, 9, 10" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-semibold">
                            
                            <!-- Pilihan Cepat Tingkat -->
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[11px] text-slate-400">Pilihan cepat:</span>
                                @foreach(['X', 'XI', 'XII', '7', '8', '9', '10', '11', '12'] as $pilihan)
                                    <button type="button" @click="setTingkat('{{ $pilihan }}')" class="px-2.5 py-1 text-xs rounded-[7px] bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-700 font-semibold transition-colors border border-slate-200">
                                        {{ $pilihan }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Sub Kelas / Rombel <span class="text-rose-500">*</span></label>
                                <span class="text-xs text-blue-600 font-medium">Bisa buat lebih dari 1</span>
                            </div>
                            <template x-for="(rombel, index) in rombels" :key="index">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" name="nama_rombel[]" x-model="rombels[index]" placeholder="Misal: X-1, X-2, atau 10 IPA 1" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    
                                    <button type="button" @click="rombels.splice(index, 1)" x-show="rombels.length > 1" class="p-2.5 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-[7px] transition-colors shrink-0" title="Hapus baris ini">
                                        <i class="ti ti-minus text-base"></i>
                                    </button>
                                    
                                    <button type="button" @click="rombels.push('')" x-show="index === rombels.length - 1" class="p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-[7px] transition-colors shrink-0" title="Tambah rombel lagi">
                                        <i class="ti ti-plus text-base"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Wali Kelas (Opsional)</label>
                            <select name="wali_kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-[7px] border-t border-slate-200">
                        <button type="button" onclick="document.getElementById('modal-kelas').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-[7px] transition-all shadow-sm">Simpan Kelas</button>
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
