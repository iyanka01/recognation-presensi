@extends('layouts.app')

@section('title', 'Log Presensi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Log Presensi Harian</h2>
            <p class="text-sm text-slate-500 mt-1">Pantau kehadiran guru dan siswa setiap harinya.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <form action="{{ route('presensi.index') }}" method="GET" class="flex items-center gap-2 w-full">
                <input type="date" name="tanggal" value="{{ request('tanggal', $tanggal) }}" class="px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm w-full sm:w-auto">
                <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-sky-200 shrink-0">
                    Tampilkan
                </button>
            </form>
            <a href="{{ route('presensi.scan') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-amber-300/40 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Scanner
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden"
         x-data="{ 
            selectedIds: [], 
            selectAll: false,
            toggleAll() {
                if (this.selectAll) {
                    this.selectedIds = {{ json_encode($presensi->pluck('id')) }};
                } else {
                    this.selectedIds = [];
                }
            }
         }">
         
        <!-- Bulk Delete Action Bar -->
        <div x-show="selectedIds.length > 0" x-transition class="bg-rose-50 border-b border-rose-100 px-6 py-3 flex items-center justify-between" style="display: none;">
            <span class="text-sm font-medium text-rose-700">
                <span x-text="selectedIds.length"></span> data presensi dipilih
            </span>
            <form action="{{ route('presensi.bulkDestroy') }}" method="POST" onsubmit="return confirm('Anda yakin ingin mereset/menghapus data presensi yang dipilih?');">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-rose-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Reset Terpilih
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        </th>
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Tipe</th>
                        <th class="p-4 font-semibold">Nama Lengkap</th>
                        <th class="p-4 font-semibold">Jam Masuk</th>
                        <th class="p-4 font-semibold">Jam Pulang</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-center">Lokasi Presensi (GPS)</th>
                        <th class="p-4 font-semibold text-center">Aksi / Status Kunci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($presensi as $index => $p)
                    <tr class="hover:bg-sky-50/30 transition-colors" :class="{'bg-sky-50/50': selectedIds.includes('{{ $p->id }}')}">
                        <td class="p-4 text-center">
                            <input type="checkbox" x-model="selectedIds" value="{{ $p->id }}" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        </td>
                        <td class="p-4 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium {{ $p->user_type == 'guru' ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ ucfirst($p->user_type) }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($p->user)
                                <div class="font-medium text-slate-800">{{ $p->user->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $p->user_type == 'guru' ? 'NIP: ' . ($p->user->nip ?? '-') : 'NIS: ' . ($p->user->nis ?? '-') }}
                                </div>
                            @else
                                <span class="text-slate-400 italic">Data terhapus</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-800">{{ $p->jam_masuk ?? '-' }}</div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-800">{{ $p->jam_pulang ?? '-' }}</div>
                        </td>
                        <td class="p-4">
                            @php
                                $statusColor = match($p->status) {
                                    'Hadir' => 'bg-emerald-100 text-emerald-700',
                                    'Terlambat' => 'bg-amber-100 text-amber-700',
                                    'Izin', 'Sakit' => 'bg-blue-100 text-blue-700',
                                    'Alpa' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700'
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($p->latitude && $p->longitude)
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="text-xs font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                        {{ number_format($p->latitude, 4) }}, {{ number_format($p->longitude, 4) }}
                                    </span>
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <a href="https://www.google.com/maps?q={{ $p->latitude }},{{ $p->longitude }}" target="_blank"
                                           class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 hover:underline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Lihat di Maps
                                        </a>
                                        @if($p->distance_meter !== null)
                                            <span class="text-slate-400">• {{ $p->distance_meter }}m</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Tanpa GPS</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($p->user_type === 'guru' && $p->jam_masuk && !$p->jam_pulang)
                                @if($p->is_unlocked)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Dibuka oleh {{ $p->unlocked_by ?? 'Admin' }}">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        Kunci Dibuka
                                    </span>
                                @else
                                    <form action="{{ route('presensi.unlock', $p->id) }}" method="POST" onsubmit="return confirm('Buka kunci presensi untuk {{ $p->user ? addslashes($p->user->nama_lengkap) : 'Guru' }}? Guru ini akan diizinkan absen masuk kembali.');" class="inline-block">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-200 transition-all active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            <span>Buka Kunci</span>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-slate-500 font-medium">Belum ada data presensi pada tanggal ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
