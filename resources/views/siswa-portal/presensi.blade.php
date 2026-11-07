@extends('layouts.siswa')

@section('title', 'Riwayat Presensi')
@section('back_url', route('siswa.dashboard'))

@section('content')
<div class="space-y-4" x-data="{ tab: 'mapel' }">

    {{-- Filter Rentang Tanggal (Persis seperti gambar 1) --}}
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-sky-100 shadow-sm">
        <form method="GET" action="{{ route('siswa.presensi') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Tanggal Mulai --}}
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input type="date" name="dari" value="{{ $dari }}" 
                           class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input type="date" name="sampai" value="{{ $sampai }}" 
                           class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-sky-200 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Tampilkan Data
                </button>
                <a href="{{ route('siswa.presensi.cetak', ['dari' => $dari, 'sampai' => $sampai]) }}" target="_blank"
                   class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-emerald-200 flex items-center justify-center gap-1.5 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak EKBM
                </a>
            </div>
        </form>
    </div>

    {{-- Tabs Switcher --}}
    <div class="bg-slate-200/70 p-1 rounded-2xl flex text-xs font-bold">
        <button type="button" @click="tab = 'mapel'"
                :class="tab === 'mapel' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-800'"
                class="flex-1 py-2.5 rounded-xl transition-all text-center flex items-center justify-center gap-1.5">
            <span>📚</span>
            <span>Kehadiran E-KBM (Mapel)</span>
        </button>
        <button type="button" @click="tab = 'harian'"
                :class="tab === 'harian' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-800'"
                class="flex-1 py-2.5 rounded-xl transition-all text-center flex items-center justify-center gap-1.5">
            <span>📅</span>
            <span>Presensi Harian (Gerbang)</span>
        </button>
    </div>

    {{-- ============ TAB 1: KEHADIRAN E-KBM (FORMAT SESUAI GAMBAR 1) ============ --}}
    <div x-show="tab === 'mapel'" class="space-y-4">
        @if($riwayatMapel->count() > 0)
            @php
                // Kelompokkan data per tanggal
                $groupedMapel = $riwayatMapel->groupBy('tanggal');
            @endphp

            @foreach($groupedMapel as $tgl => $items)
            <div class="space-y-2">
                {{-- Header Tanggal (contoh: 27 Juli 2026) --}}
                <div class="text-xs font-medium text-slate-500 pl-1 pt-1">
                    {{ \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('D MMMM Y') }}
                </div>

                {{-- List Item Kehadiran Mapel --}}
                @foreach($items as $m)
                @php
                    $kelasNama = $m->jadwal->kelas->nama_rombel ?? ($siswa->kelas->nama_rombel ?? 'IX-A');
                    $guruNama = $m->jadwal->guru->nama_lengkap ?? ($m->jadwal->guru->nama ?? 'Guru Pengampu');
                    $mapelNama = $m->jadwal->mataPelajaran->nama_mapel ?? 'Mata Pelajaran';
                @endphp
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:border-sky-200 transition-all flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-sm text-slate-800">{{ $siswa->nama_lengkap }}</h4>
                            <span class="inline-block px-1.5 py-0.5 bg-blue-600 text-white font-extrabold text-[10px] rounded leading-none uppercase">
                                {{ $kelasNama }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 truncate">
                            {{ $mapelNama }} - <span class="font-medium text-slate-700">{{ $guruNama }}</span>
                        </p>
                    </div>

                    {{-- Badge Status (Blue rounded pill persis seperti di gambar) --}}
                    <div class="shrink-0">
                        <span class="inline-block px-3.5 py-1 text-xs font-bold rounded-lg text-white bg-blue-600 shadow-sm">
                            {{ $m->status }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach

            {{-- Ringkasan Total Hadir, Izin, Sakit, Alpa --}}
            @php
                $totHadir = $riwayatMapel->where('status', 'Hadir')->count();
                $totIzin  = $riwayatMapel->where('status', 'Izin')->count();
                $totSakit = $riwayatMapel->where('status', 'Sakit')->count();
                $totAlpa  = $riwayatMapel->where('status', 'Alpa')->count();
            @endphp
            <div class="grid grid-cols-4 gap-2 pt-1 text-center">
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl py-2 px-1">
                    <p class="text-[10px] text-emerald-700 font-bold uppercase">Hadir</p>
                    <p class="text-sm sm:text-base font-black text-emerald-800">{{ $totHadir }}</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-2xl py-2 px-1">
                    <p class="text-[10px] text-blue-700 font-bold uppercase">Izin</p>
                    <p class="text-sm sm:text-base font-black text-blue-800">{{ $totIzin }}</p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl py-2 px-1">
                    <p class="text-[10px] text-amber-700 font-bold uppercase">Sakit</p>
                    <p class="text-sm sm:text-base font-black text-amber-800">{{ $totSakit }}</p>
                </div>
                <div class="bg-rose-50 border border-rose-200 rounded-2xl py-2 px-1">
                    <p class="text-[10px] text-rose-700 font-bold uppercase">Alpa</p>
                    <p class="text-sm sm:text-base font-black text-rose-800">{{ $totAlpa }}</p>
                </div>
            </div>

            {{-- Pesan di bagian bawah persis di gambar --}}
            <div class="text-center py-3.5 px-4 bg-slate-200/60 rounded-2xl text-xs text-slate-600 font-medium">
                Saat ini, data kehadiran E-KBM sudah tidak ada!
            </div>
        @else
            {{-- Saat ini data kosong --}}
            <div class="text-center py-8 px-4 bg-slate-200/60 rounded-2xl text-xs text-slate-600 font-medium">
                Saat ini, data kehadiran E-KBM sudah tidak ada!
            </div>
        @endif
    </div>

    {{-- ============ TAB 2: PRESENSI HARIAN ============ --}}
    <div x-show="tab === 'harian'" x-cloak class="space-y-2.5">
        @forelse($riwayatHarian as $p)
        <div class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center font-bold shrink-0
                    {{ $p->status == 'Hadir' ? 'bg-emerald-100 text-emerald-700' :
                       ($p->status == 'Terlambat' ? 'bg-amber-100 text-amber-700' :
                       ($p->status == 'Alpa' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700')) }}">
                    <span class="text-xs leading-none font-black">{{ Carbon\Carbon::parse($p->tanggal)->format('d') }}</span>
                    <span class="text-[9px] uppercase tracking-tighter leading-none mt-0.5">{{ Carbon\Carbon::parse($p->tanggal)->locale('id')->isoFormat('MMM') }}</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-800">
                        {{ Carbon\Carbon::parse($p->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Masuk: <span class="font-semibold text-slate-600">{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) . ' WIB' : '-' }}</span>
                        • Pulang: <span class="font-semibold text-slate-600">{{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) . ' WIB' : '-' }}</span>
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-xl
                    {{ $p->status == 'Hadir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                       ($p->status == 'Terlambat' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                       ($p->status == 'Alpa' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-sky-50 text-sky-700 border border-sky-200')) }}">
                    {{ $p->status }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-10 bg-white rounded-3xl border border-dashed border-slate-200">
            <p class="text-xs text-slate-400">Tidak ada catatan presensi harian pada rentang tanggal ini.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
