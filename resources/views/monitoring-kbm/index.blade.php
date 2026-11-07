@extends(Auth::guard('guru')->check() ? 'layouts.guru' : 'layouts.app')

@section('title', 'Monitoring KBM Realtime')
@if(Auth::guard('guru')->check())
@section('back_url', route('dashboard'))
@endif

@section('content')
<div class="space-y-6">

    <!-- Header + Filter -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 sm:gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live Monitoring
                </span>
                <span class="text-xs font-bold text-slate-500">Hari {{ $hari }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 mt-1">Monitoring KBM &amp; Absensi Kelas</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pantau aktivitas pembelajaran kelas, kehadiran guru, dan pengisian absensi siswa secara langsung.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('monitoring-kbm.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-2 w-full lg:w-auto">
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                   class="w-full sm:w-auto px-3 py-2 bg-white border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
            
            <select name="kelas_id" class="w-full sm:w-auto px-3 py-2 bg-white border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>Kelas {{ $k->tingkat }} {{ $k->nama_rombel }}</option>
                @endforeach
            </select>

            <select name="guru_id" class="w-full sm:w-auto px-3 py-2 bg-white border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
                <option value="">Semua Guru</option>
                @foreach($guruList as $g)
                <option value="{{ $g->id }}" {{ $filterGuru == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }}</option>
                @endforeach
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition-all text-center">
                Filter
            </button>
        </form>
    </div>

    <!-- Quick Stats Live -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
        <div class="bg-white/90 border border-sky-100 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm flex items-center gap-2.5 sm:gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center font-black shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm5-5h4"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider truncate">Total Sesi</p>
                <p class="text-lg sm:text-xl font-black text-slate-800">{{ $jadwals->count() }}</p>
            </div>
        </div>

        <div class="bg-white/90 border border-emerald-100 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm flex items-center gap-2.5 sm:gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-black shrink-0">
                <span class="relative flex h-3.5 w-3.5 sm:h-4 sm:w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 sm:h-4 sm:w-4 bg-emerald-500"></span>
                </span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-emerald-600 font-bold uppercase tracking-wider truncate">Sedang KBM</p>
                <p class="text-lg sm:text-xl font-black text-emerald-700">{{ $liveCount }}</p>
            </div>
        </div>

        <div class="bg-white/90 border border-teal-100 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm flex items-center gap-2.5 sm:gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center font-black shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-teal-600 font-bold uppercase tracking-wider truncate">Sudah Diisi</p>
                <p class="text-lg sm:text-xl font-black text-teal-700">{{ $diisiCount }}</p>
            </div>
        </div>

        <div class="bg-white/90 border border-amber-100 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm flex items-center gap-2.5 sm:gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-black shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-amber-600 font-bold uppercase tracking-wider truncate">Belum Diisi</p>
                <p class="text-lg sm:text-xl font-black text-amber-700">{{ $belumDiisiCount }}</p>
            </div>
        </div>
    </div>

    <!-- Cards Grid Monitoring -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($jadwals as $j)
        @php
            $isCardActive = $j->is_live;
        @endphp
        <div class="bg-white/95 rounded-2xl p-5 shadow-sm border transition-all flex flex-col justify-between {{ $isCardActive ? 'border-emerald-300 ring-2 ring-emerald-400/20 shadow-md shadow-emerald-100' : 'border-sky-100' }}">
            <div>
                <!-- Status Header -->
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold {{ $isCardActive ? 'bg-emerald-100 text-emerald-800 animate-pulse' : ($j->is_upcoming ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') }}">
                        @if($isCardActive)
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Sedang Berlangsung
                        @elseif($j->is_upcoming)
                        Belum Mulai
                        @else
                        Selesai
                        @endif
                    </span>
                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                        {{ substr($j->jam_mulai,0,5) }} – {{ substr($j->jam_selesai,0,5) }}
                    </span>
                </div>

                <!-- Info Mapel & Kelas -->
                <h3 class="font-black text-slate-800 text-base leading-tight">
                    {{ $j->mataPelajaran?->nama_mapel ?? '-' }}
                </h3>
                <p class="text-xs font-bold text-sky-600 mt-0.5">
                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }}
                    <span class="text-slate-400 font-normal">({{ $j->total_siswa }} Siswa)</span>
                </p>

                <!-- Info Guru Pengajar -->
                <div class="flex items-center gap-2.5 mt-3 pt-3 border-t border-slate-100">
                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 text-xs shrink-0">
                        {{ substr($j->guru?->nama_lengkap ?? 'G', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-700 truncate">{{ $j->guru?->nama_lengkap ?? 'Guru Pengajar' }}</p>
                        <p class="text-[10px] text-slate-400">NIP: {{ $j->guru?->nip ?? '-' }}</p>
                    </div>
                </div>

                <!-- Status Pengisian Absensi -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    @if($j->is_filled)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-700 flex items-center gap-1">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Absensi Terisi
                            </span>
                            <span class="font-bold text-slate-600">{{ $j->total_terisi }}/{{ $j->total_siswa }} Siswa</span>
                        </div>
                        <!-- Mini Breakdown Badges -->
                        <div class="grid grid-cols-4 gap-1 text-center text-[10px] font-bold">
                            <div class="bg-emerald-50 text-emerald-700 py-1 rounded-md">H: {{ $j->hadir_count }}</div>
                            <div class="bg-sky-50 text-sky-700 py-1 rounded-md">I: {{ $j->izin_count }}</div>
                            <div class="bg-amber-50 text-amber-700 py-1 rounded-md">S: {{ $j->sakit_count }}</div>
                            <div class="bg-rose-50 text-rose-700 py-1 rounded-md">A: {{ $j->alpa_count }}</div>
                        </div>
                        @if($j->jurnal)
                        <div class="bg-slate-50 rounded-xl p-2.5 text-[11px] text-slate-600 mt-2">
                            <span class="font-bold text-slate-700 block mb-0.5">Materi:</span>
                            <span class="line-clamp-2">{{ $j->jurnal->materi }}</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs font-bold text-amber-700">Guru belum menginput presensi</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Button for Teacher if owner and active -->
            @if(Auth::guard('guru')->check() && Auth::guard('guru')->user()->id === $j->guru_id && $j->is_live)
            <div class="mt-4 pt-2">
                <a href="{{ route('absensi-mapel.show', ['id' => $j->id]) }}"
                   class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 shadow-md shadow-emerald-200 transition-all">
                    <span>Mulai Absen Sekarang</span>
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full bg-white/80 rounded-2xl border border-dashed border-sky-200 p-12 text-center">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-sky-50 text-sky-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7h6m-6 4h2"/></svg>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Tidak ada jadwal KBM</h3>
            <p class="text-xs text-slate-400 mt-1">Tidak ada jadwal kegiatan belajar mengajar pada hari {{ $hari }} untuk filter ini.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
