@extends('layouts.guru')

@section('title', 'Absensi Mapel')

@section('content')
<div class="space-y-6">

    <!-- Header + Filter Tanggal -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800">Absensi Mata Pelajaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pilih jadwal mengajar Anda lalu isi kehadiran siswa.</p>
        </div>
        <form method="GET" action="{{ route('absensi-mapel.index') }}" class="flex gap-2 w-full sm:w-auto">
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                   class="flex-1 sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
            <button type="submit" class="px-4 py-2 sm:py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-sm shrink-0">Tampilkan</button>
        </form>
    </div>

    <!-- Info Hari -->
    <div class="bg-gradient-to-r from-sky-600 to-blue-700 rounded-2xl shadow-md shadow-sky-200 p-4 sm:p-5 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-sky-100">{{ $hari }}</p>
                <p class="font-bold text-sm sm:text-base">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</p>
            </div>
        </div>
        <span class="inline-flex px-2.5 sm:px-3 py-1 sm:py-1.5 bg-white/15 backdrop-blur rounded-full text-[10px] sm:text-xs font-bold border border-white/20">
            {{ $jadwals->count() }} Jadwal
        </span>
    </div>

    <!-- Daftar Jadwal -->
    @forelse($jadwals as $j)
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 text-white flex flex-col items-center justify-center shrink-0 shadow-md shadow-violet-300/40">
                <span class="text-[8px] sm:text-[10px] font-medium opacity-80 leading-none">JAM</span>
                <span class="text-xs sm:text-sm font-extrabold leading-tight">{{ substr($j->jam_mulai, 0, 5) }}</span>
                <span class="text-[8px] sm:text-[10px] font-medium opacity-80 leading-none">{{ substr($j->jam_selesai, 0, 5) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <h3 class="font-black text-sm sm:text-base text-slate-800 truncate">{{ $j->mataPelajaran?->nama_mapel ?? 'Mapel Terhapus' }}
                        <span class="ml-1 text-[10px] sm:text-xs font-mono font-semibold text-slate-400 align-middle">{{ $j->mataPelajaran?->kode_mapel }}</span>
                    </h3>
                    <span class="px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                        {{ $j->hari }}
                    </span>
                    @if($j->can_absen)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5 truncate font-medium">
                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }} &middot; Jam {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} &middot; {{ $j->total_siswa }} siswa
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2.5 sm:gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
            @if($j->total_terisi > 0)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Terisi ({{ $j->total_terisi }}/{{ $j->total_siswa }})
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                Belum diisi
            </span>
            @endif

            @if($j->can_absen)
            <a href="{{ route('absensi-mapel.show', ['id' => $j->id, 'tanggal' => $tanggal]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-md shadow-emerald-300/40 hover:-translate-y-0.5 whitespace-nowrap active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>{{ $j->total_terisi > 0 ? 'Edit' : 'Input' }}</span>
            </a>
            @else
            <button type="button"
                    onclick="document.getElementById('popup-expired-{{ $j->id }}').classList.remove('hidden')"
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-slate-200 text-slate-500 text-xs sm:text-sm font-bold rounded-xl transition-all shadow-sm whitespace-nowrap cursor-not-allowed">
                {{ $j->total_terisi > 0 ? 'Edit' : 'Input' }}
            </button>

            {{-- Popup Keterangan (Sangat responsif di HP, tidak terpotong navbar) --}}
            <div id="popup-expired-{{ $j->id }}" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl max-w-[320px] w-full p-5 text-center z-10 mx-auto animate-fade-in">
                    
                    {{-- Tombol X Close di Pojok Kanan Atas --}}
                    <button type="button" onclick="this.closest('#popup-expired-{{ $j->id }}').classList.add('hidden')"
                            class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="w-12 h-12 mx-auto mt-1 mb-2.5 rounded-full {{ $j->is_future ? 'bg-sky-100 text-sky-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center">
                        @if($j->is_future)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @endif
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-800 mb-1">
                        {{ $j->is_future ? 'Belum Waktunya Absensi' : 'Tidak Dapat Mengakses Absensi' }}
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 mb-2 leading-snug">
                        Jam pelajaran <strong>{{ $j->mataPelajaran?->nama_mapel }}</strong> adalah
                        <strong class="text-slate-700">{{ substr($j->jam_mulai, 0, 5) }} – {{ substr($j->jam_selesai, 0, 5) }}</strong>.
                    </p>
                    <div class="text-[11px] sm:text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-xl p-2.5 mb-3 leading-relaxed">
                        @if($j->is_future)
                            <span>Belum waktunya jam pelajaran. Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.</span>
                        @else
                            <span>Anda sudah melewati batas waktu absensi. Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.</span>
                        @endif
                    </div>
                    <button type="button"
                            onclick="this.closest('#popup-expired-{{ $j->id }}').classList.add('hidden')"
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-md">
                        Tutup
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
    @empty
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-dashed border-sky-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-sky-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7h6m-6 4h2"/></svg>
        <h3 class="font-bold text-slate-700 text-lg">Tidak ada jadwal mengajar</h3>
        <p class="text-sm text-slate-500 mt-1">Anda tidak memiliki jadwal pelajaran pada hari {{ $hari }}{{ in_array($hari, []) ? '' : '' }}.</p>
    </div>
    @endforelse
</div>
@endsection
