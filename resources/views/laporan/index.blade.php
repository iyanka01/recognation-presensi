@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Laporan & Rekapitulasi</h1>
            <p class="text-sm text-slate-500 mt-1">Cetak dan unduh rekap presensi bulanan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.export', request()->all()) }}" class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium rounded-xl flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                Export Excel (CSV)
            </a>
            <a href="{{ route('laporan.print', request()->all()) }}" target="_blank" class="px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-sky-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <form action="{{ route('laporan.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4" x-data="{ tipeUser: '{{ $tipe }}' }">
            
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bulan & Tahun</label>
                <input type="month" name="bulan" value="{{ $bulan }}" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
            </div>
            
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tipe Pengguna</label>
                <select name="tipe" x-model="tipeUser" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                </select>
            </div>

            <div class="w-full md:w-1/4" x-show="tipeUser == 'siswa'">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Kelas</label>
                <select name="kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_rombel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-xl transition-colors h-[42px] flex items-center justify-center">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Nama Lengkap</th>
                        @if($tipe == 'siswa')
                        <th class="p-4 font-semibold">Kelas</th>
                        @else
                        <th class="p-4 font-semibold">NIP</th>
                        @endif
                        <th class="p-4 font-semibold text-center text-emerald-600">Hadir</th>
                        <th class="p-4 font-semibold text-center text-rose-600">Terlambat</th>
                        <th class="p-4 font-semibold text-center text-sky-600">Izin</th>
                        <th class="p-4 font-semibold text-center text-amber-600">Sakit</th>
                        <th class="p-4 font-semibold text-center text-slate-400">Alpa</th>
                        <th class="p-4 font-semibold text-center text-indigo-600">% Hadir</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($laporan as $idx => $row)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-4 font-bold text-slate-800">{{ $row['user']->nama_lengkap }}</td>
                        
                        @if($tipe == 'siswa')
                        <td class="p-4 text-slate-600 font-medium">{{ $row['user']->kelas->nama_rombel ?? '-' }}</td>
                        @else
                        <td class="p-4 text-slate-600 font-medium">{{ $row['user']->nip }}</td>
                        @endif
                        
                        <td class="p-4 text-center font-bold text-emerald-700 bg-emerald-50/30">{{ $row['hadir'] ?: '-' }}</td>
                        <td class="p-4 text-center font-bold text-rose-700 bg-rose-50/30">{{ $row['terlambat'] ?: '-' }}</td>
                        <td class="p-4 text-center font-bold text-sky-700 bg-sky-50/30">{{ $row['izin'] ?: '-' }}</td>
                        <td class="p-4 text-center font-bold text-amber-700 bg-amber-50/30">{{ $row['sakit'] ?: '-' }}</td>
                        <td class="p-4 text-center font-bold text-slate-500 bg-slate-50/30">{{ $row['alpa'] ?: '-' }}</td>
                        <td class="p-4 text-center font-black text-indigo-700 bg-sky-50/30">{{ $row['persen'] }}%</td>
                        <td class="p-4 text-center">
                            <a href="{{ route('laporan.print_siswa', ['id' => $row['user']->id, 'tipe' => $tipe, 'bulan' => $bulan]) }}" target="_blank" class="inline-flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors" title="Cetak Detail Siswa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-12 text-center bg-white">
                            <h3 class="text-lg font-bold text-slate-700 mb-1">Data Kosong</h3>
                            <p class="text-slate-500 font-medium">Tidak ada data untuk filter yang Anda pilih.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
