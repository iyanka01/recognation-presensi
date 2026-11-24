@extends('layouts.guru')

@section('title', 'Absen Kegiatan Khusus')
@section('back_url', route('dashboard'))

@section('content')
<div class="space-y-4 pb-8">

    <!-- Hero Card Modern (Tema Emerald Elegan Senada Portal Guru) -->
    <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-900 rounded-[28px] p-5 text-white shadow-xl shadow-emerald-950/20 relative overflow-hidden border border-emerald-500/30">
        <!-- Background Ornaments -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-emerald-400/20 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-13 h-13 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 flex items-center justify-center text-white shrink-0 shadow-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-sm mb-1 uppercase tracking-wider">
                        Terminal Guru
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-white tracking-tight leading-tight">Presensi Kegiatan Siswa</h2>
                    <p class="text-xs text-emerald-100/90 mt-0.5 truncate">Pindai kartu barcode QR siswa untuk kegiatan khusus</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Title & Counter -->
    <div class="flex items-center justify-between px-1.5 pt-1">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Agenda Kegiatan Siswa</h3>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
            {{ $kegiatans->count() }} Kegiatan
        </span>
    </div>

    <!-- Card List Kegiatan -->
    <div class="space-y-3.5">
        @forelse($kegiatans as $k)
        <div class="bg-white rounded-[26px] p-4 sm:p-5 border border-emerald-50 shadow-xl shadow-emerald-950/5 flex flex-col gap-3.5 transition-all hover:border-emerald-200 hover:shadow-emerald-900/10">
            
            <!-- Top Badges & Status -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-amber-50 text-amber-700 border border-amber-200/80 shadow-xs">
                        #{{ $k->kode_kegiatan }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $k->status }}
                    </span>
                </div>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100">
                    {{ $k->mode_absen }}
                </span>
            </div>

            <!-- Nama Kegiatan -->
            <div>
                <h4 class="text-base font-black text-slate-900 tracking-tight leading-snug">
                    {{ $k->nama_kegiatan }}
                </h4>
            </div>

            <!-- Detail List: Tanggal, Waktu & Sasaran Kelas Riil -->
            <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-100 space-y-2">
                
                {{-- Tanggal / Hari --}}
                <div class="flex items-center gap-2.5 text-xs text-slate-700 font-medium">
                    <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @if($k->tipe_kegiatan === 'rutin')
                        <span class="font-bold text-emerald-800">Rutin: {{ $k->hari_label }}</span>
                    @else
                        <span>{{ $k->tanggal ? $k->tanggal->isoFormat('dddd, D MMMM Y') : '-' }}</span>
                    @endif
                </div>

                {{-- Jam Pelaksanaan --}}
                <div class="flex items-center gap-2.5 text-xs text-slate-700 font-medium">
                    <div class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span>{{ substr($k->jam_mulai,0,5) }} &ndash; {{ substr($k->jam_selesai,0,5) }} WIB</span>
                </div>

                {{-- Sasaran Kelas Riil (Bukan 'Pilih Kelas') --}}
                <div class="flex items-start gap-2.5 text-xs text-slate-800 font-medium">
                    <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-slate-400 text-[11px] block">Sasaran Peserta:</span>
                        <span class="font-extrabold text-emerald-800 text-xs">
                            {{ $k->sasaran_label }}
                        </span>
                    </div>
                </div>

                {{-- Catatan / Keterangan --}}
                @if($k->keterangan)
                <div class="pt-1.5 border-t border-slate-200/60 text-[11px] text-slate-500 italic">
                    "{{ $k->keterangan }}"
                </div>
                @endif
            </div>

            <!-- Footer Action: Total Kehadiran & Tombol Buka Kamera Scanner -->
            <div class="pt-1 flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kehadiran</span>
                    <span class="text-base font-black text-emerald-700">{{ $k->total_hadir ?? 0 }} <span class="text-xs font-semibold text-slate-500">Siswa</span></span>
                </div>

                <a href="{{ route('kegiatan-khusus.scan', $k->id) }}"
                   class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center gap-2 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Buka Scanner Kamera</span>
                </a>
            </div>

        </div>
        @empty
        <div class="bg-white rounded-3xl p-8 border border-emerald-50 shadow-xl shadow-emerald-950/5 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-3 border border-emerald-100">
                <i class="ti ti-calendar-off"></i>
            </div>
            <h4 class="text-sm font-black text-slate-800">Tidak Ada Kegiatan Siswa</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                Kegiatan khusus dengan sasaran siswa yang dibuat oleh operator/admin akan ditampilkan di sini.
            </p>
        </div>
        @endforelse
    </div>

</div>
@endsection
