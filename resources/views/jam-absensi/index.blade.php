@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Jam Absensi</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola aturan jam masuk, batas terlambat, dan pulang.</p>
        </div>
        <button @click="$dispatch('open-modal')" class="px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-sky-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Atur Jam
        </button>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <div x-data="{ 
        selectedTarget: '', 
        matrix: {{ json_encode($matrix) }},
        targets: {{ json_encode($allTargets) }},
        hariList: {{ json_encode($hariList) }},
        
        hasAnySchedule() {
            if(!this.selectedTarget || !this.matrix[this.selectedTarget]) return false;
            let targetMatrix = this.matrix[this.selectedTarget];
            for (let day in targetMatrix) {
                if (targetMatrix[day]) return true;
            }
            return false;
        }
    }" class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 p-6">
        
        <div class="max-w-md mx-auto mb-8 text-center">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas / Guru untuk melihat jadwal:</label>
            <div class="relative">
                <select x-model="selectedTarget" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-sky-200 rounded-xl text-base font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer shadow-sm">
                    <option value="" disabled>-- Pilih Target --</option>
                    <template x-for="target in targets" :key="target.id">
                        <option :value="target.id" x-text="target.nama"></option>
                    </template>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>

        <!-- Initial State (No target selected) -->
        <div x-show="!selectedTarget" class="py-12 text-center" x-transition.opacity>
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4 ring-8 ring-slate-50/50">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700 mb-1">Pilih Target</h3>
            <p class="text-slate-500 font-medium">Silakan pilih kelas atau guru dari dropdown di atas untuk melihat jam absensi.</p>
        </div>

        <!-- Schedule View (Target selected) -->
        <div x-show="selectedTarget" style="display: none;" x-transition.opacity class="animate-fade-in-up">
            
            <!-- Empty State for Selected Target -->
            <div x-show="!hasAnySchedule()" class="py-10 text-center bg-rose-50/50 rounded-2xl border border-rose-100" style="display: none;">
                <svg class="w-12 h-12 text-rose-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-base font-bold text-rose-800 mb-1">Belum Ada Pengaturan</h3>
                <p class="text-rose-600/80 text-sm mb-4">Target ini belum memiliki jadwal absensi untuk hari apapun.</p>
                <button @click="$dispatch('open-modal')" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 text-sm font-medium rounded-lg transition-colors">
                    Atur Jam Sekarang
                </button>
            </div>

            <!-- Matrix Grid -->
            <div x-show="hasAnySchedule()" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" style="display: none;">
                <template x-for="hari in hariList" :key="hari">
                    <div class="relative overflow-hidden rounded-2xl border transition-all duration-300"
                         :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-white border-sky-100 shadow-sm shadow-indigo-100/50' : 'bg-slate-50 border-slate-100 opacity-60'">
                        
                        <div class="px-4 py-3 border-b" :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-sky-50/50 border-sky-100' : 'bg-slate-100/50 border-slate-200'">
                            <h4 class="font-bold text-center" :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'text-indigo-800' : 'text-slate-500'" x-text="hari"></h4>
                        </div>
                        
                        <div class="p-4 flex flex-col gap-2.5">
                            <template x-if="matrix[selectedTarget] && matrix[selectedTarget][hari]">
                                <div>
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50 border border-emerald-100 mb-2">
                                        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Mulai Masuk</span>
                                        <span class="font-bold text-emerald-800" x-text="matrix[selectedTarget][hari].jam_mulai.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-rose-50 border border-rose-100 mb-2 relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-rose-500/10 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-in-out"></div>
                                        <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider relative z-10">Batas Terlambat</span>
                                        <span class="font-bold text-rose-800 relative z-10" x-text="matrix[selectedTarget][hari].jam_terlambat.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-sky-50 border border-sky-100">
                                        <span class="text-xs font-semibold text-sky-700 uppercase tracking-wider">Mulai Pulang</span>
                                        <span class="font-bold text-sky-800" x-text="matrix[selectedTarget][hari].jam_pulang.substring(0,5)"></span>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="!matrix[selectedTarget] || !matrix[selectedTarget][hari]">
                                <div class="py-6 flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    <span class="text-xs font-medium uppercase tracking-widest">Libur</span>
                                </div>
                            </template>
                        </div>
                        
                    </div>
                </template>
            </div>

        </div>
    </div>

<!-- Modal Form -->
<div x-data="{ 
        open: false,
        target_id: 'guru',
        hari: 'Senin',
        jam_mulai: '06:00',
        jam_terlambat: '07:00',
        jam_pulang: '14:00',
        showCopy: false,
        copyClasses: []
    }" 
    @open-modal.window="open = true"
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
    x-transition.opacity.duration.300ms>
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="px-6 py-4 border-b border-sky-100 flex items-center justify-between bg-sky-50/50/50">
                <h3 class="text-lg font-bold text-slate-800">Pengaturan Jam Absensi</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('jam-absensi.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-5">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Target Kelas/Guru <span class="text-rose-500">*</span></label>
                            <select name="target_id" x-model="target_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                @foreach($allTargets as $target)
                                    <option value="{{ $target['id'] }}">{{ $target['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
                            <select name="hari" x-model="hari" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                @foreach($hariList as $hari)
                                    <option value="{{ $hari }}">{{ $hari }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Mulai Absen Masuk</label>
                            <input type="time" name="jam_mulai" x-model="jam_mulai" required class="w-full px-3 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-center text-emerald-700 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5 text-rose-600">Batas Terlambat</label>
                            <input type="time" name="jam_terlambat" x-model="jam_terlambat" required class="w-full px-3 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all text-center text-rose-700 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Mulai Absen Pulang</label>
                            <input type="time" name="jam_pulang" x-model="jam_pulang" required class="w-full px-3 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all text-center text-sky-700 font-medium">
                        </div>
                    </div>

                    <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100" x-show="target_id !== 'guru'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="showCopy" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                <span class="text-sm font-medium text-indigo-900">Salin jam ini ke kelas lainnya?</span>
                            </label>
                        </div>
                        
                        <div x-show="showCopy" x-transition class="space-y-2 mt-3 pt-3 border-t border-sky-100 max-h-48 overflow-y-auto">
                            <p class="text-xs text-indigo-500 mb-2 font-medium">Pilih kelas tujuan salinan:</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($kelases as $k)
                                <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded-lg border border-indigo-50 hover:border-indigo-200 transition-colors" x-show="target_id !== '{{ $k->id }}'">
                                    <input type="checkbox" name="copy_to_classes[]" value="{{ $k->id }}" class="w-3.5 h-3.5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500" x-model="copyClasses">
                                    <span class="text-xs text-slate-700 font-medium">Kelas {{ $k->nama_rombel }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" @click="copyClasses = [{{ implode(',', array_map(function($k){ return "'".$k['id']."'"; }, $kelases->toArray())) }}]" class="text-xs text-sky-600 hover:text-sky-700 font-medium">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="copyClasses = []" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Kosongkan</button>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-sky-200">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
