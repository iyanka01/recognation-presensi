@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Jam Absensi</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola aturan jam masuk, batas terlambat, dan pulang.</p>
        </div>
        <button @click="$dispatch('open-modal')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-[7px] flex items-center gap-2 transition-colors shadow-sm text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Atur Jam
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-[7px] flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-[7px] flex items-start gap-3">
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
    }" class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] p-6">
        
        <!-- Target Selector -->
        <div class="max-w-md mx-auto mb-8 text-center">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas / Guru untuk melihat jadwal:</label>
            <div class="relative">
                <select x-model="selectedTarget" class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-[7px] text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                    <option value="" disabled>-- Pilih Target --</option>
                    <template x-for="target in targets" :key="target.id">
                        <option :value="target.id" x-text="target.nama"></option>
                    </template>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>

        <!-- Initial State (No target selected) -->
        <div x-show="!selectedTarget" class="py-12 text-center" x-transition.opacity>
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
            </div>
            <h3 class="text-base font-semibold text-slate-700 mb-1">Pilih Target</h3>
            <p class="text-slate-500 text-sm">Silakan pilih kelas atau guru dari dropdown di atas untuk melihat jam absensi.</p>
        </div>

        <!-- Schedule View (Target selected) -->
        <div x-show="selectedTarget" style="display: none;" x-transition.opacity>
            
            <!-- Empty State for Selected Target -->
            <div x-show="!hasAnySchedule()" class="py-10 text-center bg-rose-50 rounded-[7px] border border-rose-100" style="display: none;">
                <svg class="w-12 h-12 text-rose-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-base font-semibold text-rose-800 mb-1">Belum Ada Pengaturan</h3>
                <p class="text-rose-600/80 text-sm mb-4">Target ini belum memiliki jadwal absensi untuk hari apapun.</p>
                <button @click="$dispatch('open-modal')" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 text-sm font-medium rounded-[7px] transition-colors">
                    Atur Jam Sekarang
                </button>
            </div>

            <!-- Matrix Grid -->
            <div x-show="hasAnySchedule()" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" style="display: none;">
                <template x-for="hari in hariList" :key="hari">
                    <div class="relative overflow-hidden rounded-[7px] border transition-all duration-200"
                         :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-white border-gray-200 shadow-sm hover:shadow-md' : 'bg-gray-50 border-gray-100 opacity-60'">
                        
                        <!-- Day Header -->
                        <div class="px-4 py-3 border-b" :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'bg-blue-50 border-blue-100' : 'bg-gray-100 border-gray-200'">
                            <h4 class="font-semibold text-sm text-center" :class="matrix[selectedTarget] && matrix[selectedTarget][hari] ? 'text-blue-700' : 'text-gray-500'" x-text="hari"></h4>
                        </div>
                        
                        <!-- Time Slots -->
                        <div class="p-3 flex flex-col gap-2">
                            <template x-if="matrix[selectedTarget] && matrix[selectedTarget][hari]">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2.5 rounded-[7px] bg-emerald-50 border border-emerald-100">
                                        <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Mulai Masuk</span>
                                        <span class="text-sm font-bold text-emerald-800" x-text="matrix[selectedTarget][hari].jam_mulai.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2.5 rounded-[7px] bg-rose-50 border border-rose-100">
                                        <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Batas Terlambat</span>
                                        <span class="text-sm font-bold text-rose-800" x-text="matrix[selectedTarget][hari].jam_terlambat.substring(0,5)"></span>
                                    </div>
                                    <div class="flex items-center justify-between p-2.5 rounded-[7px] bg-blue-50 border border-blue-100">
                                        <span class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider">Mulai Pulang</span>
                                        <span class="text-sm font-bold text-blue-800" x-text="matrix[selectedTarget][hari].jam_pulang.substring(0,5)"></span>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="!matrix[selectedTarget] || !matrix[selectedTarget][hari]">
                                <div class="py-6 flex flex-col items-center justify-center text-gray-400">
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
        <div class="fixed inset-0 transition-opacity bg-slate-900/60" @click="open = false"></div>

        <div class="relative inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl rounded-[7px] sm:my-8 sm:align-middle"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 class="text-base font-semibold text-slate-800">Pengaturan Jam Absensi</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('jam-absensi.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-5">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Target Kelas/Guru <span class="text-rose-500">*</span></label>
                            <select name="target_id" x-model="target_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                @foreach($allTargets as $target)
                                    <option value="{{ $target['id'] }}">{{ $target['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
                            <select name="hari" x-model="hari" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                @foreach($hariList as $hari)
                                    <option value="{{ $hari }}">{{ $hari }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Mulai Absen Masuk</label>
                            <input type="time" name="jam_mulai" x-model="jam_mulai" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-center text-emerald-700 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-rose-600 mb-1.5">Batas Terlambat</label>
                            <input type="time" name="jam_terlambat" x-model="jam_terlambat" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all text-center text-rose-700 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Mulai Absen Pulang</label>
                            <input type="time" name="jam_pulang" x-model="jam_pulang" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-[7px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-center text-blue-700 font-medium">
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-[7px] border border-gray-200" x-show="target_id !== 'guru'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="showCopy" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Salin jam ini ke kelas lainnya?</span>
                            </label>
                        </div>
                        
                        <div x-show="showCopy" x-transition class="space-y-2 mt-3 pt-3 border-t border-gray-200 max-h-48 overflow-y-auto">
                            <p class="text-xs text-slate-500 mb-2 font-medium">Pilih kelas tujuan salinan:</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($kelases as $k)
                                <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded-[7px] border border-gray-100 hover:border-blue-200 transition-colors" x-show="target_id !== '{{ $k->id }}'">
                                    <input type="checkbox" name="copy_to_classes[]" value="{{ $k->id }}" class="w-3.5 h-3.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" x-model="copyClasses">
                                    <span class="text-xs text-slate-700 font-medium">Kelas {{ $k->nama_rombel }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" @click="copyClasses = [{{ implode(',', array_map(function($k){ return "'".$k['id']."'"; }, $kelases->toArray())) }}]" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Pilih Semua</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="copyClasses = []" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Kosongkan</button>
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-[7px] transition-colors shadow-sm">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
