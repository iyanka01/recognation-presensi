@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedTarget: '',
    matrix: {{ json_encode($matrix) }},
    targets: {{ json_encode($allTargets) }},
    hariList: {{ json_encode($hariList) }},
    
    // Modal Tambah State
    modalTambah: false,
    target_id: 'guru',
    hari: 'Senin',
    jam_mulai: '06:00',
    jam_terlambat: '07:00',
    jam_pulang: '14:00',
    showCopy: false,
    copyClasses: [],

    // Modal Edit State
    modalEdit: false,
    editId: '',
    editHari: '',
    editTargetNama: '',
    editMulai: '06:00',
    editTerlambat: '07:00',
    editPulang: '14:00',

    hasAnySchedule() {
        if(!this.selectedTarget || !this.matrix[this.selectedTarget]) return false;
        let targetMatrix = this.matrix[this.selectedTarget];
        for (let day in targetMatrix) {
            if (targetMatrix[day]) return true;
        }
        return false;
    },

    openTambahForDay(targetKey, day) {
        this.target_id = targetKey;
        this.hari = day;
        this.modalTambah = true;
    },

    openEdit(item, targetName) {
        this.editId = item.id;
        this.editHari = item.hari;
        this.editTargetNama = targetName;
        this.editMulai = item.jam_mulai.substring(0, 5);
        this.editTerlambat = item.jam_terlambat.substring(0, 5);
        this.editPulang = item.jam_pulang.substring(0, 5);
        this.modalEdit = true;
    },

    getSelectedTargetName() {
        let t = this.targets.find(x => x.id === this.selectedTarget);
        return t ? t.nama : '';
    }
}">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Pengaturan Jam Absensi</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola aturan jam masuk, batas terlambat, dan pulang per Departemen, Guru, atau Kelas.</p>
        </div>
        <button @click="modalTambah = true" type="button" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
            <i class="ti ti-clock-plus text-base"></i>
            <span>+ Atur Jam</span>
        </button>
    </div>

    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-[7px] flex items-start gap-2.5 text-xs">
        <i class="ti ti-circle-check text-base text-emerald-500 shrink-0"></i>
        <p class="font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-[7px] flex items-start gap-2.5 text-xs">
        <i class="ti ti-alert-triangle text-base text-rose-500 shrink-0"></i>
        <p class="font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Container Utama -->
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm p-5 sm:p-6">
        
        <!-- Target Selector Dropdown -->
        <div class="max-w-md mx-auto mb-8 text-center">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-2">
                Pilih Departemen / Guru / Kelas:
            </label>
            <div class="relative">
                <select x-model="selectedTarget" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                    <option value="" disabled>-- Pilih Target Aturan Jam --</option>
                    <template x-for="target in targets" :key="target.id">
                        <option :value="target.id" x-text="target.nama"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- Initial State (Belum memilih target) -->
        <div x-show="!selectedTarget" class="py-12 text-center" x-transition.opacity>
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400 mb-3 border border-slate-200 dark:border-slate-700">
                <i class="ti ti-calendar-time text-2xl"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Pilih Target</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Silakan pilih Departemen, Guru, atau Kelas pada dropdown di atas untuk melihat dan mengelola jam absensi.</p>
        </div>

        <!-- Target Terpilih -->
        <div x-show="selectedTarget" style="display: none;" x-transition.opacity>
            
            <!-- Empty State jika Target belum ada jadwal -->
            <div x-show="!hasAnySchedule()" class="py-10 text-center bg-amber-50/50 dark:bg-amber-950/20 rounded-[7px] border border-amber-200/80 dark:border-amber-800/40" style="display: none;">
                <i class="ti ti-calendar-off text-3xl text-amber-500 mb-2"></i>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Pengaturan Jam</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 mt-0.5">Target ini belum memiliki jadwal absensi masuk dan pulang untuk hari apapun.</p>
                <button @click="openTambahForDay(selectedTarget, 'Senin')" type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-[7px] shadow-sm transition-all cursor-pointer">
                    Atur Jam Sekarang
                </button>
            </div>

            <!-- Matrix Grid 7 Hari -->
            <div x-show="hasAnySchedule()" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" style="display: none;">
                <template x-for="hari in hariList" :key="hari">
                    <div class="relative overflow-hidden rounded-[7px] border transition-all duration-200"
                         :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-white dark:bg-slate-900/50 border-slate-200 dark:border-slate-700 shadow-sm' : 'bg-slate-50 dark:bg-slate-900/20 border-slate-200/60 dark:border-slate-800 opacity-60'">
                        
                        <!-- Header Hari -->
                        <div class="px-3.5 py-2.5 border-b flex items-center justify-between"
                             :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700'">
                            <h4 class="font-bold text-xs" :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'text-blue-700 dark:text-blue-300' : 'text-slate-400'" x-text="hari"></h4>

                            <!-- Badge status aktif/libur -->
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded"
                                  :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-800'"
                                  x-text="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'Aktif' : 'Libur'">
                            </span>
                        </div>
                        
                        <!-- Isi Waktu -->
                        <div class="p-3.5 flex flex-col gap-2">
                            <template x-if="matrix[selectedTarget] && matrix[selectedTarget][hari]">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2 rounded-[5px] bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40">
                                        <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Mulai Masuk</span>
                                        <span class="text-xs font-bold font-mono text-emerald-800 dark:text-emerald-300" x-text="matrix[selectedTarget][hari].jam_mulai.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-[5px] bg-rose-50/70 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40">
                                        <span class="text-[10px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Batas Terlambat</span>
                                        <span class="text-xs font-bold font-mono text-rose-800 dark:text-rose-300" x-text="matrix[selectedTarget][hari].jam_terlambat.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-[5px] bg-blue-50/70 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40">
                                        <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Mulai Pulang</span>
                                        <span class="text-xs font-bold font-mono text-blue-800 dark:text-blue-300" x-text="matrix[selectedTarget][hari].jam_pulang.substring(0,5)"></span>
                                    </div>

                                    <!-- Tombol Edit & Hapus (Per Hari) -->
                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-1.5">
                                        <!-- Tombol Edit -->
                                        <button type="button"
                                                @click="openEdit(matrix[selectedTarget][hari], getSelectedTargetName())"
                                                title="Edit Jam Hari Ini"
                                                class="px-2 py-1 bg-sky-500 hover:bg-sky-600 text-white rounded-[5px] text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                            <i class="ti ti-edit text-xs"></i>
                                            <span>Edit</span>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form :action="'{{ url('jam-absensi') }}/' + matrix[selectedTarget][hari].id" method="POST" onsubmit="return confirm('Hapus pengaturan jam absensi untuk hari ini (dijadikan libur)?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    title="Hapus Jadwal (Jadikan Libur)"
                                                    class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-[5px] transition-colors cursor-pointer">
                                                <i class="ti ti-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="!matrix[selectedTarget] || !matrix[selectedTarget][hari]">
                                <div class="py-5 flex flex-col items-center justify-center text-slate-400 dark:text-slate-600">
                                    <i class="ti ti-calendar-off text-2xl mb-1 opacity-60"></i>
                                    <span class="text-[11px] font-semibold uppercase tracking-wider mb-2">Libur</span>
                                    <button @click="openTambahForDay(selectedTarget, hari)" type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 dark:bg-slate-800 dark:hover:bg-blue-950 text-slate-600 hover:text-blue-600 dark:text-slate-300 text-[10px] font-bold rounded-[5px] border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                                        + Set Jam
                                    </button>
                                </div>
                            </template>
                        </div>
                        
                    </div>
                </template>
            </div>

        </div>
    </div>

    <!-- Modal Form Tambah / Set Jam -->
    <div x-show="modalTambah"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         x-transition.opacity.duration.300ms>
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="modalTambah = false"></div>

            <div class="relative inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-800 shadow-xl rounded-[7px] border border-slate-200 dark:border-slate-700 sm:my-8 sm:align-middle">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="ti ti-clock-plus text-blue-600 text-base"></i>
                        Atur Jam Absensi
                    </h3>
                    <button @click="modalTambah = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('jam-absensi.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        
                        <div class="grid grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Absensi <span class="text-rose-500">*</span></label>
                                <select name="target_id" x-model="target_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    <optgroup label="Umum / Guru">
                                        <option value="guru">Semua Guru (Umum)</option>
                                    </optgroup>
                                    <optgroup label="Per Departemen / Unit">
                                        @foreach($departemens as $d)
                                            <option value="dep_{{ $d->id }}">Departemen: {{ $d->nama_departemen }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Per Rombel Kelas">
                                        @foreach($kelases as $k)
                                            <option value="{{ $k->id }}">Kelas {{ $k->nama_rombel }} {{ $k->departemen ? '('.$k->departemen->nama_departemen.')' : '' }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hari <span class="text-rose-500">*</span></label>
                                <select name="hari" x-model="hari" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    @foreach($hariList as $h)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Masuk</label>
                                <input type="time" name="jam_mulai" x-model="jam_mulai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-emerald-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-rose-600 mb-1">Batas Terlambat</label>
                                <input type="time" name="jam_terlambat" x-model="jam_terlambat" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-rose-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Pulang</label>
                                <input type="time" name="jam_pulang" x-model="jam_pulang" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-blue-600 focus:outline-none">
                            </div>
                        </div>

                        <!-- Opsi salin ke kelas lain jika target berupa kelas -->
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-[7px] border border-slate-200 dark:border-slate-700" x-show="!target_id.startsWith('dep_') && target_id !== 'guru'">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="showCopy" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Salin jam ini ke kelas lainnya?</span>
                            </label>
                            
                            <div x-show="showCopy" x-transition class="space-y-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 max-h-40 overflow-y-auto">
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($kelases as $k)
                                    <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-[5px] border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300" x-show="target_id !== '{{ $k->id }}'">
                                        <input type="checkbox" name="copy_to_classes[]" value="{{ $k->id }}" class="rounded text-blue-600" x-model="copyClasses">
                                        <span>Kelas {{ $k->nama_rombel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" @click="modalTambah = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-[7px] transition-all shadow-sm cursor-pointer">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Form Edit Jam -->
    <div x-show="modalEdit"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         x-transition.opacity.duration.300ms>
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="modalEdit = false"></div>

            <div class="relative inline-block w-full max-w-md overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-800 shadow-xl rounded-[7px] border border-slate-200 dark:border-slate-700 sm:my-8 sm:align-middle">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="ti ti-edit text-sky-500 text-base"></i>
                            Edit Jam Absensi: <span x-text="editHari" class="text-blue-600"></span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5" x-text="editTargetNama"></p>
                    </div>
                    <button @click="modalEdit = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('jam-absensi') }}/' + editId" method="POST">
                    @csrf @method('PUT')
                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Masuk</label>
                                <input type="time" name="jam_mulai" x-model="editMulai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-emerald-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-rose-600 mb-1">Batas Terlambat</label>
                                <input type="time" name="jam_terlambat" x-model="editTerlambat" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-rose-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Pulang</label>
                                <input type="time" name="jam_pulang" x-model="editPulang" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-mono font-bold text-center text-blue-600 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" @click="modalEdit = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-[7px] transition-all shadow-sm cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
