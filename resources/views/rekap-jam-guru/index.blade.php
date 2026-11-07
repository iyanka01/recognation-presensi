@extends('layouts.app')

@section('title', 'Rekap Jam Mengajar Guru')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Rekap Jam Mengajar Guru</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan akumulasi jumlah jam dan tatap muka mengajar guru berdasarkan jurnal pembelajaran.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('rekap-jam-guru.print', request()->only(['guru_id', 'dari', 'sampai'])) }}" target="_blank"
               class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl flex items-center gap-2 transition-all shadow-md shadow-sky-200 active:scale-95 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Rekap (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Kartu Atas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg shadow-sky-200">
            <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Total Jam Mengajar</p>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black">{{ $totalSemuaJam }}</span>
                <span class="text-xs text-sky-100 font-bold">Jam Pelajaran (JP)</span>
            </div>
            <p class="text-[11px] text-sky-100/90 mt-2 font-medium">Periode {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-sky-100 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tatap Muka / Sesi</p>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black text-slate-800">{{ $totalSemuaPertemuan }}</span>
                <span class="text-xs text-slate-400 font-bold">Pertemuan KBM</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Tercatat di Jurnal Pembelajaran</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-sky-100 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Guru Terdata</p>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black text-slate-800">{{ $rekap->count() }}</span>
                <span class="text-xs text-slate-400 font-bold">Tenaga Pengajar</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Semua guru aktif dalam sistem</p>
        </div>
    </div>

    <!-- Filter Rentang Tanggal & Guru -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('rekap-jam-guru.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-400/30">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-400/30">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filter Guru</label>
                <select name="guru_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-400/30">
                    <option value="">Semua Guru</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $filterGuru == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }} ({{ $g->nip ?? 'Tanpa NIP' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-sm transition-all shadow-sm">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['guru_id', 'dari', 'sampai']))
                <a href="{{ route('rekap-jam-guru.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-all">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Rekap Data -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-12 text-center">No</th>
                        <th class="p-4 font-semibold">Nama Guru</th>
                        <th class="p-4 font-semibold">NIP</th>
                        <th class="p-4 font-semibold">Mata Pelajaran yang Diajar</th>
                        <th class="p-4 font-semibold text-center text-slate-700 bg-slate-100/50">Jadwal Rutin</th>
                        <th class="p-4 font-semibold text-center text-sky-700 bg-sky-50/50">Sesi Terlaksana</th>
                        <th class="p-4 font-semibold text-center text-emerald-700 bg-emerald-50/50">Total Jam (JP)</th>
                        <th class="p-4 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-center text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="p-4">
                            <span class="font-bold text-slate-800 block leading-snug">{{ $r->guru->nama_lengkap }}</span>
                            <span class="text-[11px] text-slate-400">{{ $r->guru->email ?? '-' }}</span>
                        </td>
                        <td class="p-4 text-slate-600 font-medium">{{ $r->guru->nip ?? '-' }}</td>
                        <td class="p-4">
                            @if(count($r->mapel_summary) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($r->mapel_summary as $ms)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                        {{ $ms['nama_mapel'] }}: <strong class="ml-1 text-sky-800">{{ $ms['total_jam'] }} JP</strong>
                                    </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada jurnal KBM di rentang tanggal ini</span>
                            @endif
                        </td>
                        <td class="p-4 text-center font-bold text-slate-700 bg-slate-50/30">
                            {{ $r->total_jadwal_mingguan }} <span class="text-[11px] font-normal text-slate-400">kelas/minggu</span>
                        </td>
                        <td class="p-4 text-center font-bold text-sky-700 bg-sky-50/30">
                            {{ $r->total_pertemuan }} <span class="text-[11px] font-normal text-slate-400">kali</span>
                        </td>
                        <td class="p-4 text-center font-black text-emerald-700 bg-emerald-50/30 text-base">
                            {{ $r->total_jam }} <span class="text-xs font-semibold text-emerald-600">JP</span>
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('rekap-jam-guru.print-detail', ['guru_id' => $r->guru->id, 'dari' => $dari, 'sampai' => $sampai]) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 text-xs font-bold rounded-lg transition-all"
                               title="Cetak Rincian Jurnal Per Tanggal">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            Tidak ada data guru yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800 text-xs uppercase">
                        <td colspan="5" class="p-4 text-right">TOTAL KESELURUHAN:</td>
                        <td class="p-4 text-center text-sky-800 text-sm font-black">{{ $totalSemuaPertemuan }} Sesi</td>
                        <td class="p-4 text-center text-emerald-800 text-base font-black">{{ $totalSemuaJam }} JP</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
