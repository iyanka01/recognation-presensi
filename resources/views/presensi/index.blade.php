@extends('layouts.app')

@section('title', 'Log Presensi')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold text-slate-800">Log Presensi Harian</h2>
            <p class="text-sm text-slate-500 mt-1">Pantau kehadiran guru dan siswa setiap harinya.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <form action="{{ route('presensi.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <input type="date" name="tanggal" value="{{ request('tanggal', $tanggal) }}" class="px-3.5 py-2 bg-white border border-gray-200 rounded-[7px] text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-colors w-full sm:w-auto">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-[7px] text-sm flex items-center justify-center gap-2 transition-colors shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Tampilkan</span>
                </button>
            </form>
            <a href="{{ route('presensi.scan') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-[7px] text-sm flex items-center justify-center gap-2 transition-colors shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>Scanner</span>
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-[7px] flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Data Table Card Container -->
    <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border border-gray-100 overflow-hidden"
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
            <span class="text-sm font-semibold text-rose-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><span x-text="selectedIds.length"></span> data presensi dipilih</span>
            </span>
            <form action="{{ route('presensi.bulkDestroy') }}" method="POST" onsubmit="return confirm('Anda yakin ingin mereset/menghapus data presensi yang dipilih?');">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-[7px] flex items-center gap-1.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Reset Terpilih</span>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="p-4 w-12 text-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Tipe</th>
                        <th class="p-4 font-semibold">Nama Lengkap</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                        <th class="p-4 font-semibold">Jam Masuk</th>
                        <th class="p-4 font-semibold">Jam Pulang</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-center">Lokasi Presensi (GPS)</th>
                        <th class="p-4 font-semibold text-center">Aksi / Status Kunci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-slate-700">
                    @forelse($presensi as $index => $p)
                    @php
                        $isPastLocked = ($p->user_type === 'guru' && $p->tanggal < \Carbon\Carbon::today()->toDateString() && !$p->jam_pulang && !$p->is_unlocked);
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors {{ $isPastLocked ? 'bg-amber-50/70 border-amber-200' : '' }}" :class="{'bg-blue-50/40': selectedIds.includes('{{ $p->id }}')}">
                        <td class="p-4 text-center">
                            <input type="checkbox" x-model="selectedIds" value="{{ $p->id }}" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="p-4 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $p->user_type == 'guru' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($p->user_type) }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($p->user)
                                <div class="font-semibold text-slate-800">{{ $p->user->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $p->user_type == 'guru' ? 'NIP: ' . ($p->user->nip ?? '-') : 'NIS: ' . ($p->user->nis ?? '-') }}
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">Data terhapus</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap text-xs">
                            @if($isPastLocked)
                                <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}
                                </span>
                            @else
                                <span class="text-slate-600 font-medium">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap font-medium text-slate-700">
                            {{ $p->jam_masuk ?? '-' }}
                        </td>
                        <td class="p-4 whitespace-nowrap font-medium text-slate-700">
                            @if($isPastLocked)
                                <span class="text-rose-600 font-bold text-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Belum Pulang
                                </span>
                            @else
                                {{ $p->jam_pulang ?? '-' }}
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            @php
                                $statusColor = match($p->status) {
                                    'Hadir' => 'bg-emerald-100 text-emerald-800',
                                    'Terlambat' => 'bg-amber-100 text-amber-800',
                                    'Izin', 'Sakit' => 'bg-blue-100 text-blue-800',
                                    'Alpa' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            @if($p->latitude && $p->longitude)
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="text-xs font-mono text-slate-600 bg-gray-100 px-2 py-0.5 rounded">
                                        {{ number_format($p->latitude, 4) }}, {{ number_format($p->longitude, 4) }}
                                    </span>
                                    <div class="flex items-center justify-center gap-1.5 text-[11px]">
                                        <a href="https://www.google.com/maps?q={{ $p->latitude }},{{ $p->longitude }}" target="_blank"
                                           class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 hover:underline">
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
                        <td class="p-4 text-center whitespace-nowrap">
                            @if($p->user_type === 'guru' && $p->jam_masuk && !$p->jam_pulang)
                                @if($p->is_unlocked)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Dibuka oleh {{ $p->unlocked_by ?? 'Admin' }}">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        <span>Kunci Dibuka</span>
                                    </span>
                                @else
                                    <form action="{{ route('presensi.unlock', $p->id) }}" method="POST" onsubmit="return confirm('Buka kunci presensi untuk {{ $p->user ? addslashes($p->user->nama_lengkap) : 'Guru' }}? Guru ini akan diizinkan absen masuk kembali.');" class="inline-block">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[7px] bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-sm transition-colors active:scale-95">
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
                        <td colspan="10" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Belum ada data presensi</p>
                                <p class="text-xs text-slate-400 mt-1">Tidak ditemukan data presensi pada tanggal yang dipilih.</p>
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
