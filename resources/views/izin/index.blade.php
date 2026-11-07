@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Input Perizinan</h1>
            <p class="text-sm text-slate-500 mt-1">Catat ketidakhadiran siswa atau guru (Sakit, Izin, Alpa).</p>
        </div>
        <button @click="$dispatch('open-modal')" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-amber-300/40">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Catat Izin Baru
        </button>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any() || session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="text-sm font-medium">
            @if(session('error')) <p>{{ session('error') }}</p> @endif
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Nama / Identitas</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                        <th class="p-4 font-semibold text-center">Status</th>
                        <th class="p-4 font-semibold">Keterangan</th>
                        <th class="p-4 font-semibold text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($izins as $idx => $izin)
                    @php
                        $user = $izin->user_type == 'siswa' ? \App\Models\Siswa::find($izin->user_id) : \App\Models\Guru::find($izin->user_id);
                        if(!$user) continue;
                        
                        $isToday = \Carbon\Carbon::parse($izin->tanggal)->isToday();
                        $statusColor = match($izin->status) {
                            'Izin' => 'bg-sky-100 text-sky-700',
                            'Sakit' => 'bg-amber-100 text-amber-700',
                            'Alpa' => 'bg-rose-100 text-rose-700',
                            'Dinas Luar' => 'bg-emerald-100 text-emerald-700',
                            'Cuti' => 'bg-purple-100 text-purple-700',
                            'Dispensasi' => 'bg-teal-100 text-teal-700',
                            default => 'bg-slate-100 text-slate-700'
                        };
                    @endphp
                    <tr class="hover:bg-sky-50/30 transition-colors {{ !$isToday ? 'opacity-80' : '' }}">
                        <td class="p-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold shrink-0">
                                    {{ substr($user->nama_lengkap, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $user->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500 flex items-center gap-2">
                                        <span class="uppercase tracking-wider text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100">{{ $izin->user_type }}</span>
                                        @if($izin->user_type == 'siswa')
                                            NIS: {{ $user->nis }}
                                        @else
                                            NIP: {{ $user->nip }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($izin->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($izin->tanggal)->locale('id')->dayName }}</div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusColor }}">
                                {{ $izin->status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-600 truncate max-w-xs" title="{{ $izin->keterangan ?? '-' }}">
                                {{ $izin->keterangan ?? '-' }}
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            @if($izin->bukti)
                            <a href="{{ Storage::url($izin->bukti) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat File
                            </a>
                            @else
                            <span class="text-xs text-slate-300 italic">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center bg-white">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-8 ring-slate-50/50">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Catatan</h3>
                                <p class="text-slate-500 font-medium mb-4">Belum ada siswa atau guru yang tercatat Izin, Sakit, atau Alpa.</p>
                                <button @click="$dispatch('open-modal')" class="px-5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-600 font-medium rounded-xl transition-colors">
                                    Catat Izin Sekarang
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Input Izin -->
<div x-data="{ 
        open: false,
        searchQuery: '',
        userId: '',
        tanggalMulai: '{{ \Carbon\Carbon::today()->toDateString() }}',
        tanggalSelesai: '{{ \Carbon\Carbon::today()->toDateString() }}',
        status: 'Izin'
    }" 
    @open-modal.window="open = true"
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
    x-transition.opacity.duration.300ms>
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle">
            
            <div class="px-6 py-4 border-b border-sky-100 flex items-center justify-between bg-sky-50/50/50">
                <h3 class="text-lg font-bold text-slate-800">Catat Ketidakhadiran Baru</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('izin.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-5">
                    
                    <!-- Searchable Select (Native + Alpine Filter Simulation) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pilih Siswa / Guru <span class="text-rose-500">*</span></label>
                        <select name="user_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all font-medium text-slate-700">
                            <option value="" disabled selected>-- Cari dan Pilih Orang --</option>
                            <optgroup label="Daftar Siswa">
                                @foreach($siswas as $siswa)
                                    <option value="siswa|{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas {{ $siswa->kelas->nama_rombel ?? '-' }})</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Daftar Guru">
                                @foreach($gurus as $guru)
                                    <option value="guru|{{ $guru->id }}">{{ $guru->nama_lengkap }} (NIP: {{ $guru->nip }})</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Anda bisa mengetik untuk mencari nama dengan cepat pada menu dropdown.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mulai Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" x-model="tanggalMulai" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all font-medium text-slate-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sampai Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" x-model="tanggalSelesai" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all font-medium text-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status Ketidakhadiran <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Izin' ? 'ring-2 ring-sky-500/20 border-sky-500 bg-sky-50/50' : ''">
                                <input type="radio" name="status" value="Izin" x-model="status" required class="w-4 h-4 text-sky-600 border-slate-300 focus:ring-sky-500">
                                <span class="text-xs font-bold text-slate-700">Izin</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Sakit' ? 'ring-2 ring-amber-500/20 border-amber-500 bg-amber-50/50' : ''">
                                <input type="radio" name="status" value="Sakit" x-model="status" required class="w-4 h-4 text-amber-600 border-slate-300 focus:ring-amber-500">
                                <span class="text-xs font-bold text-slate-700">Sakit</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Alpa' ? 'ring-2 ring-rose-500/20 border-rose-500 bg-rose-50/50' : ''">
                                <input type="radio" name="status" value="Alpa" x-model="status" required class="w-4 h-4 text-rose-600 border-slate-300 focus:ring-rose-500">
                                <span class="text-xs font-bold text-slate-700">Alpa</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Dinas Luar' ? 'ring-2 ring-emerald-500/20 border-emerald-500 bg-emerald-50/50' : ''">
                                <input type="radio" name="status" value="Dinas Luar" x-model="status" required class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-slate-700">Dinas Luar</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Cuti' ? 'ring-2 ring-purple-500/20 border-purple-500 bg-purple-50/50' : ''">
                                <input type="radio" name="status" value="Cuti" x-model="status" required class="w-4 h-4 text-purple-600 border-slate-300 focus:ring-purple-500">
                                <span class="text-xs font-bold text-slate-700">Cuti</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-2.5 border border-sky-200 rounded-xl hover:bg-slate-50 transition-colors" :class="status == 'Dispensasi' ? 'ring-2 ring-teal-500/20 border-teal-500 bg-teal-50/50' : ''">
                                <input type="radio" name="status" value="Dispensasi" x-model="status" required class="w-4 h-4 text-teal-600 border-slate-300 focus:ring-teal-500">
                                <span class="text-xs font-bold text-slate-700">Dispensasi</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan / Alasan Tambahan</label>
                        <textarea name="keterangan" rows="2" placeholder="Cth: Mengikuti lomba tingkat provinsi, sakit demam, dll." class="w-full px-4 py-3 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all placeholder:text-slate-400"></textarea>
                    </div>

                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-amber-300/40">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
