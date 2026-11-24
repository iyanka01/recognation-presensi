@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Laporan & Rekapitulasi Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Cetak dan unduh rekap presensi per bulan atau rentang tanggal tertentu.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('laporan.export', request()->all()) }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-colors border border-emerald-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                <span>Export Excel (.xlsx)</span>
            </a>
            <a href="{{ route('laporan.print', request()->all()) }}" target="_blank" class="px-3.5 py-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 transition-all shadow-sm shadow-sky-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Rekap PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200" x-data="{ 
        mode: '{{ $mode }}', 
        tipeUser: '{{ $tipe }}',
        departemen: '{{ $departemen_id }}'
    }">
        <form action="{{ route('laporan.index') }}" method="GET" class="space-y-4">
            
            <!-- Mode Selector Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Pilihan Periode:</span>
                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all"
                       :class="mode === 'rentang' ? 'bg-sky-50 text-sky-700 border border-sky-300 font-bold' : 'text-slate-600 hover:bg-slate-100 border border-transparent'">
                    <input type="radio" name="mode" value="rentang" x-model="mode" class="text-sky-600 focus:ring-sky-500">
                    <span>Rentang Waktu (Dari - Sampai)</span>
                </label>
                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all"
                       :class="mode === 'bulan' ? 'bg-sky-50 text-sky-700 border border-sky-300 font-bold' : 'text-slate-600 hover:bg-slate-100 border border-transparent'">
                    <input type="radio" name="mode" value="bulan" x-model="mode" class="text-sky-600 focus:ring-sky-500">
                    <span>Per Bulan</span>
                </label>
            </div>

            <!-- Filter Controls Grid: 1 Baris Sejajar -->
            <div class="flex flex-wrap items-end gap-3">
                
                <!-- 1. Departemen -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Departemen</label>
                    <select name="departemen_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                        <option value="all">-- Semua Departemen --</option>
                        @foreach($departemens as $dep)
                            <option value="{{ $dep->id }}" {{ $departemen_id == $dep->id ? 'selected' : '' }}>
                                {{ $dep->kode_departemen }} - {{ $dep->nama_departemen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Tipe Pengguna (Dialihkan persis sesudah Departemen) -->
                <div class="w-full sm:w-36">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tipe Pengguna</label>
                    <select name="tipe" x-model="tipeUser" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                    </select>
                </div>

                <!-- 3. Kelas (Tampil jika Siswa) -->
                <div class="w-full sm:w-44" x-show="tipeUser == 'siswa'">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kelas</label>
                    <select name="kelas_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_rombel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4 & 5. Date Inputs (Rentang Waktu atau Per Bulan) -->
                <div class="w-full sm:w-40" x-show="mode === 'rentang'">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ $dari }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                </div>

                <div class="w-full sm:w-40" x-show="mode === 'rentang'">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ $sampai }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                </div>

                <div class="w-full sm:w-48" x-show="mode === 'bulan'">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Bulan & Tahun</label>
                    <input type="month" name="bulan" value="{{ $bulan }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                </div>

                <!-- Tombol Submit Filter -->
                <div class="w-full sm:w-auto">
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all h-[38px] flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="ti ti-filter text-sm"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-12 text-center font-semibold text-slate-600">NO</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">NAMA LENGKAP</th>
                        @if($tipe == 'siswa')
                        <th class="py-2.5 px-3 font-semibold text-slate-600">KELAS</th>
                        <th class="py-2.5 px-3 font-semibold text-slate-600">NIS</th>
                        @else
                        <th class="py-2.5 px-3 font-semibold text-slate-600">NIP / NRY</th>
                        @endif
                        <th class="py-2.5 px-3 font-semibold text-center text-slate-600">JAM MASUK</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-slate-600">JAM PULANG</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-emerald-700 bg-emerald-50/50">HADIR</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-rose-700 bg-rose-50/50">TERLAMBAT</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-sky-700 bg-sky-50/50">IZIN</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-amber-700 bg-amber-50/50">SAKIT</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-slate-600 bg-slate-100/50">ALPA</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-indigo-700 bg-indigo-50/50">% HADIR</th>
                        <th class="py-2.5 px-3 font-semibold text-center text-slate-600">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($laporan as $idx => $row)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-2.5 px-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-800">{{ $row['user']->nama_lengkap }}</td>
                        
                        @if($tipe == 'siswa')
                        <td class="py-2.5 px-3 text-slate-600 font-medium">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $row['user']->kelas->nama_rombel ?? '-' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">{{ $row['user']->nis ?? '-' }}</td>
                        @else
                        <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">{{ $row['user']->nip ?? '-' }}</td>
                        @endif

                        <td class="py-2.5 px-3 text-center font-mono font-medium text-slate-700">{{ $row['jam_masuk'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-slate-700">{{ $row['jam_pulang'] ?? '-' }}</td>
                        
                        <td class="py-2.5 px-3 text-center font-bold text-emerald-700 bg-emerald-50/30">{{ $row['hadir'] ?: '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-rose-700 bg-rose-50/30">{{ $row['terlambat'] ?: '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-sky-700 bg-sky-50/30">{{ $row['izin'] ?: '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-amber-700 bg-amber-50/30">{{ $row['sakit'] ?: '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-500 bg-slate-50/30">{{ $row['alpa'] ?: '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-black text-indigo-700 bg-indigo-50/30">
                            <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $row['persen'] >= 90 ? 'bg-emerald-100 text-emerald-800' : ($row['persen'] >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $row['persen'] }}%
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            @php
                                $detailParams = array_merge(request()->all(), ['id' => $row['user']->id, 'tipe' => $tipe]);
                            @endphp
                            <a href="{{ route('laporan.print_siswa', $detailParams) }}" target="_blank" class="inline-flex items-center justify-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors" title="Cetak Rincian Individual">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $tipe == 'siswa' ? 13 : 12 }}" class="py-12 text-center bg-white">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ti ti-database-off text-3xl text-slate-300 mb-2"></i>
                                <h3 class="text-sm font-bold text-slate-700">Data Tidak Ditemukan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tidak ada data presensi untuk periode dan filter yang Anda pilih.</p>
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
