@extends('layouts.guru')

@section('title', 'Jadwal Mengajar')
@section('back_url', route('dashboard'))

@section('content')
<div class="space-y-5">

    <!-- Header & Info Ringkas -->
    <div class="bg-gradient-to-br from-sky-600 via-sky-700 to-blue-800 rounded-3xl p-5 text-white shadow-lg shadow-sky-200 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                    <svg class="w-3.5 h-3.5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Jadwal Mingguan
                </span>
                <span class="text-xs font-semibold text-sky-200">Hari ini: <strong>{{ $hariIni }}</strong></span>
            </div>
            <h1 class="text-xl font-black tracking-tight">Jadwal Mengajar Guru</h1>
            <p class="text-xs text-sky-100/90 mt-1">Daftar lengkap seluruh kelas dan mata pelajaran yang Anda ampu selama satu minggu penuh.</p>

            <!-- Mini Stats Grid -->
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/15">
                <div class="text-center">
                    <p class="text-lg font-black">{{ $totalJadwal }}</p>
                    <p class="text-[10px] text-sky-200 uppercase tracking-wider font-semibold">Total Sesi</p>
                </div>
                <div class="text-center border-x border-white/15">
                    <p class="text-lg font-black">{{ $kelasCount }}</p>
                    <p class="text-[10px] text-sky-200 uppercase tracking-wider font-semibold">Kelas Diajar</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-black">{{ $mapelCount }}</p>
                    <p class="text-[10px] text-sky-200 uppercase tracking-wider font-semibold">Mata Pelajaran</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Bar Pilihan Hari (Scrollable Horizontal) -->
    <div class="overflow-x-auto no-scrollbar -mx-1 sm:mx-0 px-1 pb-1">
        <div class="flex items-center gap-1.5 sm:gap-2 min-w-max">
            @foreach($hariList as $h)
            @php
                $isAktif = ($selectedHari === $h);
                $isToday = ($hariIni === $h);
                $count = count($jadwalPerHari[$h] ?? []);
            @endphp
            <a href="{{ route('guru.jadwal.index', ['hari' => $h]) }}"
               class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl text-[11px] sm:text-xs font-bold transition-all flex items-center gap-1.5 sm:gap-2 relative {{ $isAktif ? 'bg-sky-600 text-white shadow-md shadow-sky-300/50 scale-[1.02]' : 'bg-white/90 text-slate-600 border border-sky-100 hover:bg-sky-50' }}">
                <span>{{ $h }}</span>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black {{ $isAktif ? 'bg-white/20 text-white' : ($count > 0 ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-400') }}">
                    {{ $count }}
                </span>
                @if($isToday)
                <span class="w-1.5 h-1.5 rounded-full {{ $isAktif ? 'bg-amber-300' : 'bg-emerald-500' }}" title="Hari ini"></span>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    <!-- Judul List Hari Terpilih -->
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2">
            <h2 class="text-xs sm:text-sm font-extrabold text-slate-800">Hari {{ $selectedHari }}</h2>
            @if($selectedHari === $hariIni)
            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                Hari Ini
            </span>
            @endif
        </div>
        <span class="text-[11px] sm:text-xs font-medium text-slate-500">{{ count($currentJadwals) }} Jadwal Pelajaran</span>
    </div>

    <!-- Daftar Kartu Jadwal -->
    <div class="space-y-2.5 sm:space-y-3">
        @forelse($currentJadwals as $j)
        <div class="bg-white/95 rounded-2xl p-3 sm:p-4 shadow-sm border {{ $j->can_absen ? 'border-emerald-300 ring-2 ring-emerald-400/20' : 'border-sky-100' }} transition-all flex items-center gap-2.5 sm:gap-3.5">
            
            <!-- Jam Badge -->
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center shrink-0 shadow-md text-white {{ $j->can_absen ? 'bg-gradient-to-br from-emerald-500 to-green-600 shadow-emerald-200' : 'bg-gradient-to-br from-slate-700 to-slate-900 shadow-slate-200' }}">
                <span class="text-[8px] sm:text-[9px] font-extrabold uppercase tracking-wider opacity-80 leading-none mb-0.5">Waktu</span>
                <span class="text-[11px] sm:text-xs font-black leading-tight">{{ substr($j->jam_mulai,0,5) }}</span>
                <span class="text-[8px] sm:text-[9px] font-medium opacity-80 leading-none mt-0.5">– {{ substr($j->jam_selesai,0,5) }}</span>
            </div>

            <!-- Detail Mapel & Kelas -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <h3 class="font-extrabold text-slate-800 truncate text-xs sm:text-sm">{{ $j->mataPelajaran?->nama_mapel ?? '-' }}</h3>
                    @if($j->can_absen)
                    <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-100 text-emerald-800 animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Sedang Berlangsung
                    </span>
                    @endif
                </div>

                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 font-medium truncate">
                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }} &middot; {{ $j->total_siswa }} Siswa
                </p>

                @if($selectedHari === $hariIni)
                    @if($j->total_terisi > 0)
                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Absensi Terisi ({{ $j->total_terisi }}/{{ $j->total_siswa }})
                    </span>
                    @else
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">
                        Absensi Belum Diisi
                    </span>
                    @endif
                @endif
            </div>

            <!-- Tombol Aksi -->
            <div class="shrink-0">
                @if($selectedHari === $hariIni)
                    @if($j->can_absen)
                    <a href="{{ route('absensi-mapel.show', ['id' => $j->id]) }}"
                       class="inline-flex items-center gap-1 px-2.5 sm:px-3.5 py-1.5 sm:py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-[11px] sm:text-xs font-bold rounded-xl shadow-md shadow-emerald-200 transition-all whitespace-nowrap">
                        <span>{{ $j->total_terisi > 0 ? 'Edit' : 'Absen' }}</span>
                    </a>
                    @else
                    <span class="inline-block px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-100 text-slate-400 text-[11px] sm:text-xs font-bold rounded-xl cursor-default whitespace-nowrap">
                        {{ $j->is_future ? 'Belum Mulai' : 'Selesai' }}
                    </span>
                    @endif
                @else
                    <span class="inline-block px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 text-slate-400 text-[11px] sm:text-xs font-semibold rounded-xl whitespace-nowrap">
                        {{ $j->hari }}
                    </span>
                @endif
            </div>

        </div>
        @empty
        <div class="bg-white/80 rounded-2xl border border-dashed border-sky-200 p-10 text-center">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-sky-50 text-sky-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7h6m-6 4h2"/></svg>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Tidak ada jadwal mengajar</h3>
            <p class="text-xs text-slate-400 mt-1">Anda tidak memiliki jadwal mengajar pada hari {{ $selectedHari }}.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
