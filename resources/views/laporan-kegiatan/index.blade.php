@extends('layouts.app')

@section('title', 'Laporan Kegiatan Khusus')

@section('content')
<div class="space-y-6">

    <!-- Header & Dropdown Pemilih Kegiatan -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Laporan Kegiatan Khusus</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rekapitulasi kehadiran peserta per agenda kegiatan khusus dengan opsi cetak PDF dan unduh Excel (.xlsx).</p>
        </div>

        <!-- Action Export Buttons -->
        @if($selectedKegiatan)
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <!-- Unduh Excel (Native .xlsx) -->
            <a href="{{ route('laporan-kegiatan.export-excel', ['kegiatan_id' => $selectedKegiatan->id]) }}"
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-1.5 shadow-sm transition-all flex-1 sm:flex-none">
                <i class="ti ti-file-spreadsheet text-base"></i>
                <span>Unduh Excel</span>
            </a>

            <!-- Cetak / PDF -->
            <a href="{{ route('laporan-kegiatan.print', ['kegiatan_id' => $selectedKegiatan->id]) }}" target="_blank"
               class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-1.5 shadow-sm transition-all flex-1 sm:flex-none">
                <i class="ti ti-printer text-base"></i>
                <span>Cetak / PDF</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Dropdown Selector Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('laporan-kegiatan.index') }}" class="flex items-center gap-3 w-full">
            <div class="flex items-center gap-2 flex-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                    <i class="ti ti-calendar-event text-blue-600 text-sm"></i>
                    Pilih Kegiatan:
                </label>
                <select name="kegiatan_id" onchange="this.form.submit()"
                        class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    @forelse($kegiatanList as $k)
                        @php
                            $tglFormat = \Carbon\Carbon::parse($k->tanggal)->locale('id')->translatedFormat('d F Y');
                        @endphp
                        <option value="{{ $k->id }}" {{ $selectedKegiatanId == $k->id ? 'selected' : '' }}>
                            [{{ $k->kode_kegiatan }}] {{ $k->nama_kegiatan }} &mdash; {{ $tglFormat }} ({{ $k->jam_range }} WIB)
                        </option>
                    @empty
                        <option value="">Belum ada agenda kegiatan khusus</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    @if($selectedKegiatan)
    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Peserta -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Target Peserta</span>
            <span class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $rekap['total'] }}</span>
        </div>

        <!-- Hadir -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border-l-4 border-l-emerald-500 border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Hadir Tepat</span>
            <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $rekap['hadir'] }}</span>
        </div>

        <!-- Terlambat -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border-l-4 border-l-rose-500 border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Terlambat</span>
            <span class="text-xl font-extrabold text-rose-600 dark:text-rose-400 mt-0.5 block">{{ $rekap['terlambat'] }}</span>
        </div>

        <!-- Izin / Sakit -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border-l-4 border-l-amber-500 border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Izin / Sakit</span>
            <span class="text-xl font-extrabold text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $rekap['izin'] + $rekap['sakit'] }}</span>
        </div>

        <!-- Belum Absen -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border-l-4 border-l-slate-400 border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Belum Hadir</span>
            <span class="text-xl font-extrabold text-slate-600 dark:text-slate-300 mt-0.5 block">{{ $rekap['belum_absen'] }}</span>
        </div>

        <!-- % Kehadiran -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] border-l-4 border-l-blue-500 border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">% Kehadiran</span>
            <span class="text-xl font-extrabold text-blue-600 dark:text-blue-400 mt-0.5 block">{{ $rekap['persen'] }}%</span>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                    Daftar Kehadiran Peserta &mdash; {{ $selectedKegiatan->nama_kegiatan }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Mode Absen: <strong>{{ $selectedKegiatan->mode_absen }}</strong> &bull;
                    Sasaran: <strong>{{ $sasaranText }}</strong>
                </p>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Menampilkan {{ $pesertaList->count() }} Peserta
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nomor Induk</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Kelas / Jabatan</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Jam Pulang</th>
                        <th class="py-3 px-4 text-center">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($pesertaList as $index => $item)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-500">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3 px-4 font-mono font-medium text-slate-600 dark:text-slate-300">
                            {{ $item['nomor_induk'] ?: '-' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-100">
                            {{ $item['nama'] }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $item['tipe'] === 'Guru' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' }}">
                                {{ $item['tipe'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            {{ $item['info'] }}
                        </td>
                        <td class="py-3 px-4 font-mono font-semibold {{ $item['jam_masuk'] !== '-' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $item['jam_masuk'] }}
                        </td>
                        <td class="py-3 px-4 font-mono font-semibold {{ $item['jam_pulang'] !== '-' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}">
                            {{ $item['jam_pulang'] }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($item['status'] === '-')
                                <span class="font-bold text-slate-400 text-sm">
                                    -
                                </span>
                            @else
                                @php
                                    $statusClasses = [
                                        'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300',
                                        'Terlambat' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300',
                                        'Izin' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300',
                                        'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300',
                                    ];
                                    $c = $statusClasses[$item['status']] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-[5px] text-[10px] font-bold border {{ $c }}">
                                    {{ $item['status'] }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                            Tidak ada peserta yang terdaftar pada kegiatan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 p-12 text-center text-slate-400 dark:text-slate-500">
        <i class="ti ti-calendar-off text-4xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum Ada Kegiatan Dipilih</h3>
        <p class="text-xs text-slate-400 mt-1">Silakan pilih atau tambahkan agenda kegiatan khusus terlebih dahulu.</p>
    </div>
    @endif

</div>
@endsection
