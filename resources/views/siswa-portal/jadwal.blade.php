@extends('layouts.siswa')

@section('title', 'Jadwal Pelajaran')
@section('back_url', route('siswa.dashboard'))

@section('content')
<div class="space-y-4">

    <div class="bg-gradient-to-r from-sky-600 to-indigo-600 text-white rounded-3xl p-5 shadow-sm">
        <h3 class="font-extrabold text-base">Jadwal Pelajaran Kelas {{ $siswa->kelas->nama_rombel ?? '' }}</h3>
        <p class="text-xs text-sky-100 mt-1">Jadwal kegiatan belajar mengajar mingguan</p>
    </div>

    @foreach($hariList as $hari)
    @php
        $jadwalHari = $jadwalMingguan->get($hari, collect());
    @endphp
    <div class="bg-white rounded-3xl p-5 border border-sky-100 shadow-sm">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $jadwalHari->count() > 0 ? 'bg-sky-500' : 'bg-slate-300' }}"></span>
                {{ $hari }}
            </h4>
            <span class="text-xs font-semibold text-slate-400">{{ $jadwalHari->count() }} Mata Pelajaran</span>
        </div>

        @if($jadwalHari->count() > 0)
        <div class="space-y-2">
            @foreach($jadwalHari as $j)
            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:border-sky-200 transition-all">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 font-extrabold text-xs flex items-center justify-center shrink-0">
                        {{ $j->jam_ke ?? $loop->iteration }}
                    </span>
                    <div class="truncate">
                        <p class="font-bold text-xs sm:text-sm text-slate-800 truncate">{{ $j->mataPelajaran->nama_mapel ?? 'Mapel' }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ $j->guru->nama_lengkap ?? '-' }}</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-mono font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-100">
                        {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs text-slate-400 py-3 text-center italic">Tidak ada jadwal pelajaran.</p>
        @endif
    </div>
    @endforeach

</div>
@endsection
