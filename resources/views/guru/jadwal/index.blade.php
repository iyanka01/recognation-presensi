@extends('layouts.guru')

@section('title', 'Jadwal Mengajar')
@section('back_url', route('dashboard'))
@section('body_class', 'bg-slate-50 min-h-screen')
@section('title_class', 'text-slate-900')
@section('back_btn_class', 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 shadow-sm')

@section('content')
<div class="space-y-4 pb-6">

    {{-- ======== TAB PILIHAN HARI MINGGUAN (PERSIS GAYA ATAS GAMBAR REFERENSI) ======== --}}
    <div class="bg-white rounded-2xl p-2 border border-slate-200/90 shadow-sm overflow-x-auto no-scrollbar">
        <div class="flex items-center justify-between min-w-max gap-2 px-1">
            @foreach($hariList as $h)
            @php
                $isAktif = ($selectedHari === $h);
                $isToday = ($hariIni === $h);
                $count = count($jadwalPerHari[$h] ?? []);
            @endphp
            <a href="{{ route('guru.jadwal.index', ['hari' => $h]) }}"
               class="flex flex-col items-center justify-center px-4 py-2 rounded-xl transition-all duration-200 {{ $isAktif ? 'bg-slate-900 text-white font-black shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold' }}">
                <span class="text-xs">{{ $h }}</span>
                @if($isToday)
                    <span class="text-[9px] font-bold {{ $isAktif ? 'text-emerald-300' : 'text-emerald-600' }} leading-tight mt-0.5">Hari ini</span>
                @else
                    <span class="text-[9px] font-bold opacity-60 leading-tight mt-0.5">{{ $count }} Sesi</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    {{-- ======== TIMELINE JADWAL HARIAN ======== --}}
    <div class="mt-4">
        <div class="flex items-center justify-between mb-4 px-1">
            <h3 class="text-sm font-black text-slate-900 tracking-tight">
                Agenda Hari {{ $selectedHari }}
            </h3>
            <span class="text-xs font-black text-slate-500">
                {{ count($currentJadwals) }} Sesi Pelajaran
            </span>
        </div>

        {{-- Timeline Container --}}
        <div class="relative pl-16 space-y-4">
            
            {{-- Garis Timeline Vertikal di posisi left-[54px] --}}
            <div class="absolute left-[54px] top-4 bottom-4 w-0.5 bg-slate-300"></div>

            @forelse($currentJadwals as $index => $j)
                <div class="relative group">
                    
                    {{-- Timestamp Jam di sisi kiri bebas dari titik bullet --}}
                    <div class="absolute -left-16 top-3 w-11 text-right pr-1">
                        <span class="text-xs font-black text-slate-900 block leading-tight">
                            {{ substr($j->jam_mulai, 0, 5) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 block">
                            {{ substr($j->jam_selesai, 0, 5) }}
                        </span>
                    </div>

                    {{-- Lingkaran Bullet Timeline tepat di atas garis vertikal --}}
                    <div class="absolute -left-[14px] top-3.5 w-3.5 h-3.5 rounded-full border-2 {{ $j->can_absen ? 'bg-teal-600 border-white ring-4 ring-teal-200' : 'bg-white border-slate-400' }} transition-all"></div>

                    {{-- Card Jadwal Putih Solid --}}
                    <div class="bg-white rounded-2xl p-4 border {{ $j->can_absen ? 'border-teal-500 ring-2 ring-teal-500/20 shadow-md shadow-teal-500/10' : 'border-slate-200 shadow-sm' }} transition-all duration-200 hover:shadow-md">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-black text-slate-900 truncate">
                                        {{ $j->mataPelajaran?->nama_mapel ?? 'Mapel Terhapus' }}
                                    </h4>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-700 font-bold">
                                        {{ $j->mataPelajaran?->kode_mapel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 font-bold mt-1">
                                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }} &bull; {{ $j->total_siswa }} Siswa
                                </p>
                            </div>

                            {{-- Badge Durasi / Jam --}}
                            <div class="shrink-0 text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-800">
                                    {{ substr($j->jam_mulai,0,5) }} - {{ substr($j->jam_selesai,0,5) }}
                                </span>
                            </div>
                        </div>

                        {{-- Footer Info Sesi --}}
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-slate-700 truncate">
                                    👥 Total {{ $j->total_siswa }} Siswa Terdaftar
                                </span>
                            </div>

                            <div>
                                @if($j->can_absen)
                                    <span class="text-[10px] font-black text-teal-800 bg-teal-50 border border-teal-200 px-2.5 py-0.5 rounded-full">
                                        ● Sedang Berlangsung
                                    </span>
                                @elseif($selectedHari === $hariIni && $j->is_past)
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                        Selesai
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                        Terjadwal
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center ml-0 -pl-16">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Jadwal Mengajar</h4>
                    <p class="text-xs text-slate-400 mt-1">Anda tidak memiliki jadwal mengajar pada hari {{ $selectedHari }}.</p>
                </div>
            @endforelse

        </div>
    </div>

</div>
@endsection
