@extends('layouts.guru')

@section('title', 'Dashboard Guru')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
<div class="space-y-5 pb-6">

    {{-- Alert Keamanan jika guru masih menggunakan password default (guru123) --}}
    @if(\Illuminate\Support\Facades\Hash::check(\App\Http\Controllers\GuruController::PASSWORD_DEFAULT, $guru->password))
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-300/40 flex items-center justify-center shrink-0 text-amber-600 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-amber-900 text-sm">Peringatan Keamanan Akun</h4>
            <p class="text-xs text-amber-800/90 mt-0.5 leading-relaxed">
                Anda masih menggunakan password standar (<strong>guru123</strong>). Demi keamanan data presensi dan akun Anda, mohon segera ganti password akun Anda.
            </p>
            <div class="mt-2.5">
                <a href="{{ route('guru.password') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-200 transition-all active:scale-95">
                    <span>Ubah Password Sekarang</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ======== BANNER WAKTU DIGITAL REAL-TIME ======== --}}
    <div class="text-center pt-1 pb-1">
        <div id="live-clock-guru" class="text-3xl sm:text-4xl font-black text-white tracking-wider drop-shadow-md">
            {{ \Carbon\Carbon::now()->format('g:i:s A') }}
        </div>
    </div>

    {{-- ======== KARTU BIRU PROFIL GURU (SEPERTI PORTAL SISWA) ======== --}}
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl p-4 shadow-lg shadow-sky-200/50 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3.5 min-w-0">
            {{-- Avatar Bulat --}}
            <div class="relative shrink-0">
                @if($guru->foto_guru)
                    <img src="{{ Storage::url($guru->foto_guru) }}" alt="{{ $guru->nama_lengkap }}" class="w-12 h-12 rounded-full object-cover border-2 border-white/80 shadow-sm bg-white">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 border-2 border-white/80 shadow-sm flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Info Nama & NIP --}}
            <div class="min-w-0 flex-1">
                <h3 class="text-base sm:text-lg font-black truncate leading-tight">{{ $guru->nama_lengkap }}</h3>
                <p class="text-xs text-sky-100 font-medium truncate mt-0.5">Guru Pengajar • NIP: {{ $guru->nip ?? '-' }}</p>
            </div>
        </div>

        {{-- Badge Status Jam Sekolah --}}
        <div class="shrink-0 text-right hidden sm:block">
            <span class="inline-block px-2.5 py-1 bg-white/20 backdrop-blur rounded-xl text-[10px] font-bold text-white">
                {{ $pengaturanJam ? substr($pengaturanJam->jam_mulai,0,5) . ' - ' . substr($pengaturanJam->jam_pulang,0,5) : 'PKBM' }}
            </span>
        </div>
    </div>

    {{-- ======== 2 KARTU ABSENSI MANDIRI (MASUK & PULANG) ======== --}}
    <div class="grid grid-cols-2 gap-2.5">
        {{-- Jam Masuk --}}
        @if($presensiHariIni && $presensiHariIni->jam_masuk)
        <div class="bg-white rounded-2xl p-3 border border-emerald-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] text-slate-400 font-bold uppercase leading-none">Masuk</p>
                    <p class="text-sm font-black text-emerald-700 mt-0.5">{{ substr($presensiHariIni->jam_masuk,0,5) }}</p>
                    <p class="text-[9px] text-emerald-600 font-medium truncate">{{ $presensiHariIni->status }}</p>
                </div>
            </div>
            @if($presensiHariIni->latitude && $presensiHariIni->longitude)
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                <span class="text-slate-500 font-medium truncate">📍 {{ number_format($presensiHariIni->latitude, 4) }}, {{ number_format($presensiHariIni->longitude, 4) }}</span>
                <a href="https://www.google.com/maps?q={{ $presensiHariIni->latitude }},{{ $presensiHariIni->longitude }}" target="_blank" class="text-sky-600 hover:text-sky-700 font-bold shrink-0 ml-1">
                    Maps ↗
                </a>
            </div>
            @endif
        </div>
        @else
        <button type="button" onclick="openModal('masuk')" class="bg-white hover:bg-emerald-50/50 rounded-2xl p-3 border border-slate-200 shadow-sm flex items-center gap-2.5 transition-all active:scale-95 text-left">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] text-slate-400 font-bold uppercase leading-none">Jam Masuk</p>
                <p class="text-xs font-bold text-emerald-600 mt-0.5">Absen Sekarang →</p>
            </div>
        </button>
        @endif

        {{-- Jam Pulang --}}
        @if($presensiHariIni && $presensiHariIni->jam_pulang)
        <div class="bg-white rounded-2xl p-3 border border-amber-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] text-slate-400 font-bold uppercase leading-none">Pulang</p>
                    <p class="text-sm font-black text-amber-700 mt-0.5">{{ substr($presensiHariIni->jam_pulang,0,5) }}</p>
                    <p class="text-[9px] text-slate-400 font-medium truncate">Sudah Absen</p>
                </div>
            </div>
            @if($presensiHariIni->latitude && $presensiHariIni->longitude)
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                <span class="text-slate-500 font-medium truncate">📍 {{ number_format($presensiHariIni->latitude, 4) }}, {{ number_format($presensiHariIni->longitude, 4) }}</span>
                <a href="https://www.google.com/maps?q={{ $presensiHariIni->latitude }},{{ $presensiHariIni->longitude }}" target="_blank" class="text-sky-600 hover:text-sky-700 font-bold shrink-0 ml-1">
                    Maps ↗
                </a>
            </div>
            @endif
        </div>
        @else
        <button type="button" onclick="openModal('pulang')" class="bg-white hover:bg-amber-50/50 rounded-2xl p-3 border border-slate-200 shadow-sm flex items-center gap-2.5 transition-all active:scale-95 text-left">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] text-slate-400 font-bold uppercase leading-none">Jam Pulang</p>
                <p class="text-xs font-bold text-amber-600 mt-0.5">Absen Pulang →</p>
            </div>
        </button>
        @endif
    </div>

    {{-- ======== GRID 8 MENU IKON UTAMA GURU (IDENTIK 100% PERSIS SEPERTI SISWA) ======== --}}
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xl shadow-slate-300/30">
        <div class="grid grid-cols-3 gap-y-6 gap-x-2 text-center">

            {{-- 1. Absen Mapel --}}
            <a href="{{ route('absensi-mapel.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 group-hover:bg-sky-100 border border-sky-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" alt="Absen Mapel" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Absen Mapel</span>
            </a>

            {{-- 2. Jadwal --}}
            <a href="{{ route('guru.jadwal.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 group-hover:bg-amber-100 border border-amber-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/3652/3652191.png" alt="Jadwal" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Jadwal</span>
            </a>

            {{-- 3. Monitoring --}}
            <a href="{{ route('monitoring-kbm.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 group-hover:bg-emerald-100 border border-emerald-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3 relative">
                    <img src="https://cdn-icons-png.flaticon.com/512/1534/1534938.png" alt="Monitoring" class="w-8 h-8 object-contain">
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping"></span>
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Monitoring</span>
            </a>

            {{-- 4. Scan Wajah --}}
            <a href="{{ route('guru.face-scan') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 group-hover:bg-indigo-100 border border-indigo-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/11502/11502540.png" alt="Scan Wajah" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Scan Wajah</span>
            </a>

            {{-- 5. Rekap Absen --}}
            <a href="{{ route('rekap-mapel.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 group-hover:bg-purple-100 border border-purple-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/942/942751.png" alt="Rekap Absen" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Rekap</span>
            </a>

            {{-- 6. Izin & Cuti --}}
            <a href="{{ route('guru.izin.index', ['open' => 1]) }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 group-hover:bg-teal-100 border border-teal-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/2921/2921222.png" alt="Izin & Cuti" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Izin / Cuti</span>
            </a>

            {{-- 7. Informasi --}}
            <a href="{{ route('guru.informasi.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 group-hover:bg-rose-100 border border-rose-100/90 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/2645/2645897.png" alt="Informasi" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Informasi</span>
            </a>

            {{-- 8. Akun Guru --}}
            <button type="button" onclick="openAccountModal()" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 group-hover:bg-slate-100 border border-slate-200 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Akun Guru" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-bold text-slate-800 mt-2 leading-tight">Profil</span>
            </button>

        </div>
    </div>


    {{-- ======================== JADWAL HARI INI ======================== --}}
    <div>
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h2 class="text-xs sm:text-sm font-extrabold text-slate-800 uppercase tracking-wider">Jadwal Mengajar Hari Ini</h2>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-sm">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Hari {{ $hariIni }}
            </span>
        </div>

        @forelse($jadwalHariIni as $j)
        <div class="bg-white border {{ $j->can_absen ? 'border-emerald-400 ring-2 ring-emerald-400/20 shadow-emerald-100' : 'border-slate-200/90' }} rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3 flex items-center gap-3 sm:gap-4 transition-all">


            {{-- Jam & Hari Badge --}}
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl text-white flex flex-col items-center justify-center shrink-0 shadow-md {{ $j->can_absen ? 'bg-gradient-to-br from-emerald-500 to-green-600 shadow-emerald-200 ring-2 ring-emerald-300' : 'bg-gradient-to-br from-violet-500 to-purple-700 shadow-violet-200' }}">
                <span class="text-[8px] sm:text-[9px] font-extrabold uppercase tracking-wider opacity-90 leading-none mb-0.5">{{ $j->hari }}</span>
                <span class="text-[11px] sm:text-xs font-black leading-tight">{{ substr($j->jam_mulai,0,5) }}</span>
                <span class="text-[8px] sm:text-[9px] font-medium opacity-80 leading-none mt-0.5">– {{ substr($j->jam_selesai,0,5) }}</span>
            </div>

            {{-- Info Mapel, Kelas, Keterangan Hari & Status --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <h3 class="font-extrabold text-slate-800 truncate text-xs sm:text-sm">{{ $j->mataPelajaran?->nama_mapel ?? '-' }}</h3>
                    <span class="px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                        {{ $j->hari }}
                    </span>
                    @if($j->can_absen)
                    <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-100 text-emerald-800 animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                    @endif
                </div>

                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 truncate font-medium">
                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }} &middot; Jam {{ substr($j->jam_mulai,0,5) }} - {{ substr($j->jam_selesai,0,5) }}
                </p>

                @if($j->total_terisi > 0)
                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Terisi {{ $j->total_terisi }}/{{ $j->total_siswa }}
                </span>
                @else
                <span class="inline-flex mt-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">
                    Belum diisi ({{ $j->total_siswa }} siswa)
                </span>
                @endif
            </div>

            {{-- Tombol Absensi: Berubah hijau jika can_absen --}}
            <div class="shrink-0">
                @if($j->can_absen)
                <a href="{{ route('absensi-mapel.show', ['id' => $j->id]) }}"
                   class="inline-flex items-center gap-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-md shadow-emerald-300/50 hover:-translate-y-0.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $j->total_terisi > 0 ? 'Edit' : 'Absen' }}</span>
                </a>
                @else
                <button type="button"
                        onclick="document.getElementById('popup-dash-{{ $j->id }}').classList.remove('hidden')"
                        class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-200 hover:bg-slate-300 text-slate-500 text-[11px] sm:text-xs font-bold rounded-xl cursor-not-allowed transition-all whitespace-nowrap">
                    <span>{{ $j->is_future ? 'Belum Mulai' : ($j->is_past ? 'Selesai' : ($j->total_terisi > 0 ? 'Edit' : 'Absen')) }}</span>
                </button>

                {{-- Popup (Sangat responsif di HP, tidak terpotong navbar) --}}
                <div id="popup-dash-{{ $j->id }}" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
                    <div class="relative bg-white rounded-3xl shadow-2xl max-w-[320px] w-full p-5 text-center z-10 mx-auto animate-fade-in">
                        
                        {{-- Tombol X Close di Pojok Kanan Atas --}}
                        <button type="button" onclick="this.closest('[id^=popup-dash]').classList.add('hidden')"
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
                            <strong class="text-slate-700">{{ substr($j->jam_mulai,0,5) }} – {{ substr($j->jam_selesai,0,5) }}</strong>.
                        </p>
                        <div class="text-[11px] sm:text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-xl p-2.5 mb-3 leading-relaxed">
                            @if($j->is_future)
                                <span>Belum waktunya jam pelajaran. Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.</span>
                            @else
                                <span>Anda sudah melewati batas waktu absensi. Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.</span>
                            @endif
                        </div>
                        <button type="button"
                                onclick="this.closest('[id^=popup-dash]').classList.add('hidden')"
                                class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-md">
                            Tutup
                        </button>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @empty
        <div class="bg-white/80 border border-dashed border-sky-200 rounded-2xl p-10 text-center">
            <svg class="w-14 h-14 mx-auto text-sky-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="font-bold text-slate-600">Tidak ada jadwal mengajar pada Hari {{ $hariIni }}</h3>
            <p class="text-sm text-slate-400 mt-1">Anda tidak memiliki jadwal mengajar di hari {{ $hariIni }}.</p>
        </div>
        @endforelse
    </div>

</div>

{{-- ===== MODAL KAMERA FACE RECOGNITION ABSENSI ===== --}}
<div id="modal-face" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCamera()"></div>
    <div class="relative bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg z-10 shadow-2xl max-h-[92vh] flex flex-col overflow-hidden animate-fade-in">
        {{-- Handle --}}
        <div class="pt-3 pb-1 flex justify-center shrink-0">
            <div class="w-10 h-1.5 bg-slate-200 rounded-full"></div>
        </div>

        {{-- Header --}}
        <div class="px-5 pb-2.5 flex items-center justify-between shrink-0 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div id="face-modal-icon" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="face-modal-title" class="font-bold text-slate-800 text-sm truncate">Absensi via Wajah</h3>
                    <p id="face-modal-subtitle" class="text-slate-500 text-[11px] truncate">Hadapkan wajah ke kamera</p>
                </div>
            </div>
            <button onclick="closeCamera()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable Body Container (Sangat ramah layar HP) --}}
        <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-3 space-y-3">

            {{-- Loading AI --}}
            <div id="face-loading" class="text-center py-4">
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                    <div class="w-12 h-12 mx-auto mb-2.5 rounded-full bg-sky-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-sky-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Memuat AI Pengenalan Wajah</p>
                    <p class="text-xs text-slate-400 mt-0.5" id="face-load-status">Mohon tunggu...</p>
                    <div class="mt-3 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div id="face-load-bar" class="h-full bg-sky-500 rounded-full transition-all duration-500" style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Camera section --}}
            <div id="face-camera-section" class="hidden space-y-2.5">
                {{-- Container Video Kamera Responsive --}}
                <div class="relative rounded-2xl overflow-hidden bg-slate-900 mx-auto w-full max-w-[340px]" style="aspect-ratio: 4/3; max-height: 220px;">
                    <video id="face-video" autoplay muted playsinline class="w-full h-full object-cover" style="transform:scaleX(-1)"></video>
                    <canvas id="face-canvas" class="absolute inset-0 w-full h-full pointer-events-none" style="transform:scaleX(-1)"></canvas>
                    {{-- Oval guide --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div id="face-oval" class="border-4 border-white/50 rounded-full transition-all duration-300" style="width:130px; height:160px;"></div>
                    </div>
                    {{-- Status chip --}}
                    <div class="absolute bottom-2 left-0 right-0 flex justify-center px-2">
                        <span id="face-cam-status" class="px-3 py-1 bg-slate-900/80 backdrop-blur-sm rounded-full text-[11px] font-semibold text-white truncate max-w-full">
                            Arahkan wajah ke oval
                        </span>
                    </div>
                </div>

                {{-- Result box --}}
                <div id="face-result" class="hidden rounded-2xl p-3.5 text-center border">
                    <div id="face-result-icon" class="w-10 h-10 mx-auto mb-1.5 rounded-full flex items-center justify-center"></div>
                    <p id="face-result-name" class="font-bold text-sm leading-snug"></p>
                    <p id="face-result-msg" class="text-xs mt-0.5 leading-snug"></p>
                    <p id="face-result-time" class="text-[11px] font-bold mt-1"></p>
                </div>

                {{-- ======== PETA GPS & TITIK LOKASI PRESENSI ======== --}}
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" id="gps-guru-pulse"></span>
                            <h4 class="text-xs font-bold text-slate-700">Verifikasi Jarak & Radius GPS</h4>
                        </div>
                        <button type="button" onclick="refreshGuruGPS()" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh
                        </button>
                    </div>

                    {{-- Status Geofencing Card --}}
                    <div id="gps-guru-card" class="mb-2 p-2 rounded-xl border bg-slate-50 border-slate-200 transition-all">
                        <div class="flex items-center gap-2">
                            <div id="gps-guru-icon" class="w-6 h-6 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="gps-guru-title" class="text-xs font-bold text-slate-700 truncate">Mendeteksi koordinat GPS...</p>
                                <p id="gps-guru-desc" class="text-[10px] text-slate-500 truncate">Mohon aktifkan izin lokasi browser</p>
                            </div>
                        </div>
                    </div>

                    {{-- Leaflet Map Container (Tinggi adaptif, tidak memakan seluruh layar) --}}
                    <div class="rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100 relative" style="height: 120px;">
                        <div id="map-guru-absen" style="height: 100%; width: 100%; z-index: 1;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1 px-0.5">
                        <span class="flex items-center gap-1">🔵 Posisi Anda</span>
                        <span class="flex items-center gap-1">🏫 Sekolah</span>
                        <span class="flex items-center gap-1">🟢 Radius Absensi</span>
                    </div>
                </div>

                <p class="text-center text-[10.5px] text-slate-400 pt-1">
                    💡 Wajah akan dikenali otomatis — tidak perlu tekan tombol apapun
                </p>
            </div>

        </div>

        {{-- Footer Fixed Action Button --}}
        <div class="p-3 bg-slate-50 border-t border-slate-100 shrink-0">
            <button onclick="closeCamera()"
                    class="w-full py-2.5 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition-all shadow-sm active:scale-95">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="toast-notif" class="hidden fixed bottom-24 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-2xl shadow-xl text-white text-sm font-semibold"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
// ===== CONSTANTS =====
const CSRF_TOKEN      = '{{ csrf_token() }}';
const ABSEN_URL       = '{{ route("guru.absen-manual") }}';
const MODEL_URL       = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
const THRESHOLD       = 0.52;
const CURRENT_GURU_ID = '{{ auth()->guard("guru")->user()->id }}';
const CURRENT_GURU    = @json(auth()->guard('guru')->user()->nama_lengkap);
const HAS_FACE        = {{ auth()->guard('guru')->user()->face_descriptor ? 'true' : 'false' }};
const HAS_MASUK       = {{ ($presensiHariIni && $presensiHariIni->jam_masuk) ? 'true' : 'false' }};
const GURU_DESCRIPTOR = {!! auth()->guard('guru')->user()->face_descriptor ?: 'null' !!};

// Koordinat Sekolah dari Pengaturan Lembaga
const SCHOOL_LAT      = {{ $pengaturan && $pengaturan->latitude ? $pengaturan->latitude : 'null' }};
const SCHOOL_LNG      = {{ $pengaturan && $pengaturan->longitude ? $pengaturan->longitude : 'null' }};
const SCHOOL_RADIUS   = {{ $pengaturan && $pengaturan->radius_meter ? $pengaturan->radius_meter : 150 }};
const SCHOOL_HAS_GEO  = {{ ($pengaturan && $pengaturan->latitude && $pengaturan->longitude) ? 'true' : 'false' }};

let faceStream      = null;
let faceDetectTimer = null;
let currentTipe     = null;
let myDescriptor    = GURU_DESCRIPTOR ? new Float32Array(GURU_DESCRIPTOR) : null;
let modelsLoaded    = false;
let isProcessing    = false;
let lastSuccessAt   = 0;

// GPS & Map State
let guruMap         = null;
let guruMarker      = null;
let schoolMarker    = null;
let schoolCircle    = null;
let currentLat      = null;
let currentLng      = null;
let currentDist     = null;
let isOutsideRadius = false;
let rejectCooldown  = 0;

console.log('[Dashboard] Script dimuat. HAS_FACE:', HAS_FACE, 'GURU:', CURRENT_GURU);

// ===== BUKA MODAL KAMERA =====
async function openModal(tipe) {
    console.log('[openModal] Dipanggil dengan tipe:', tipe, '| HAS_FACE:', HAS_FACE, '| myDescriptor:', !!myDescriptor);
    currentTipe = tipe;

    const modal = document.getElementById('modal-face');
    const icon  = document.getElementById('face-modal-icon');
    const title = document.getElementById('face-modal-title');
    const sub   = document.getElementById('face-modal-subtitle');

    // Jika wajah belum terdaftar — tampilkan panel daftar wajah di dalam modal
    if (!HAS_FACE || !myDescriptor) {
        if (tipe === 'masuk') {
            icon.className = 'w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600';
            title.textContent = 'Wajah Belum Terdaftar';
        } else {
            icon.className = 'w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600';
            title.textContent = 'Wajah Belum Terdaftar';
        }
        sub.textContent = 'Silakan daftarkan wajah Anda terlebih dahulu';
        modal.classList.remove('hidden');

        // Sembunyikan loading & camera, tampilkan pesan di face-result
        document.getElementById('face-loading').classList.add('hidden');
        document.getElementById('face-camera-section').classList.add('hidden');

        const box  = document.getElementById('face-result');
        box.className = 'mt-3 rounded-2xl p-5 text-center border bg-rose-50 border-rose-200';
        box.innerHTML = `
            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-rose-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="font-bold text-rose-700 text-sm mb-1">Wajah Belum Terdaftar</p>
            <p class="text-xs text-rose-500 mb-4 leading-relaxed">Untuk melakukan absensi menggunakan verifikasi wajah, Anda perlu mendaftarkan wajah terlebih dahulu.</p>
            <a href="{{ route('guru.face-scan') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Daftar Wajah Sekarang
            </a>`;
        box.classList.remove('hidden');
        return;
    }

    // Cek urutan absensi masuk sebelum pulang
    if (tipe === 'pulang' && !HAS_MASUK) {
        modal.classList.remove('hidden');
        document.getElementById('face-loading').classList.add('hidden');
        document.getElementById('face-camera-section').classList.add('hidden');
        icon.className = 'w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600';
        title.textContent = 'Absen Masuk Belum Tercatat';
        sub.textContent = 'Selesaikan absensi masuk dulu';

        const box = document.getElementById('face-result');
        box.className = 'mt-3 rounded-2xl p-5 text-center border bg-amber-50 border-amber-200';
        box.innerHTML = `
            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="font-bold text-amber-700 text-sm mb-1">Absen Masuk Belum Tercatat</p>
            <p class="text-xs text-amber-600 leading-relaxed">Silakan tap <strong>Jam Masuk</strong> dan lakukan absensi masuk terlebih dahulu sebelum absen pulang.</p>`;
        box.classList.remove('hidden');
        return;
    }

    if (tipe === 'masuk') {
        icon.className  = 'w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600';
        title.textContent = 'Verifikasi Wajah - Absen Masuk';
    } else {
        icon.className  = 'w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600';
        title.textContent = 'Verifikasi Wajah - Absen Pulang';
    }
    sub.textContent = 'Halo ' + CURRENT_GURU + ' — hadapkan wajah Anda';

    document.getElementById('face-result').classList.add('hidden');
    isProcessing  = false;
    lastSuccessAt = 0;
    rejectCooldown = 0;

    modal.classList.remove('hidden');
    showLoading(true);

    try {
        // Muat model AI jika belum
        if (!modelsLoaded) {
            await loadModels();
            modelsLoaded = true;
        }

        // Mulai kamera & Peta GPS
        document.getElementById('face-load-status').textContent = 'Memulai kamera & GPS...';
        document.getElementById('face-load-bar').style.width = '95%';
        await Promise.all([startFaceCamera(), initGuruGPSMap()]);
        showLoading(false);
        setTimeout(() => { if (guruMap) guruMap.invalidateSize(); }, 350);
        startDetectLoop();

    } catch (err) {
        console.error('[openModal] Error:', err);
        document.getElementById('face-load-status').textContent = '❌ Gagal: ' + err.message;
        document.getElementById('face-load-bar').style.width = '100%';
        document.getElementById('face-load-bar').style.background = '#ef4444';
    }
}

// ===== GPS & MAPS LOGIC GURU =====
function calculateDistanceGuru(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

async function initGuruGPSMap() {
    const defaultCenter = SCHOOL_HAS_GEO ? [SCHOOL_LAT, SCHOOL_LNG] : [-6.2088, 106.8456];
    
    if (!guruMap) {
        guruMap = L.map('map-guru-absen', { zoomControl: false, attributionControl: false }).setView(defaultCenter, 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(guruMap);

        if (SCHOOL_HAS_GEO) {
            schoolMarker = L.marker([SCHOOL_LAT, SCHOOL_LNG]).addTo(guruMap)
                .bindPopup('<b>Lokasi Sekolah</b>');

            schoolCircle = L.circle([SCHOOL_LAT, SCHOOL_LNG], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.25,
                radius: SCHOOL_RADIUS
            }).addTo(guruMap);
        }
    } else {
        guruMap.setView(defaultCenter, 16);
    }

    refreshGuruGPS();
}

function refreshGuruGPS() {
    if (!navigator.geolocation) {
        updateGuruGPSUI('error', 'GPS Tidak Didukung', 'Browser Anda tidak mendukung Geolocation.');
        return;
    }

    updateGuruGPSUI('loading', 'Mencari sinyal GPS...', 'Mengambil koordinat lokasi Anda...');

    navigator.geolocation.getCurrentPosition(
        (pos) => handleGuruGPSPosition(pos),
        (err) => {
            let msg = 'Izin lokasi (GPS) ditolak atau tidak tersedia.';
            if (err.code === 1) msg = 'Izin lokasi ditolak. Harap izinkan GPS pada browser.';
            else if (err.code === 2) msg = 'Posisi GPS tidak ditemukan. Pastikan GPS aktif.';
            else if (err.code === 3) msg = 'Waktu pencarian GPS habis.';
            updateGuruGPSUI('error', 'GPS Tidak Terdeteksi', msg);
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 5000 }
    );
}

function handleGuruGPSPosition(pos) {
    currentLat = pos.coords.latitude;
    currentLng = pos.coords.longitude;

    if (!guruMap) return;

    if (!guruMarker) {
        const blueIcon = L.divIcon({
            className: 'custom-guru-marker',
            html: '<div style="background-color:#0284c7; width:16px; height:16px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 0 8px rgba(2,132,199,0.7);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        guruMarker = L.marker([currentLat, currentLng], { icon: blueIcon }).addTo(guruMap)
            .bindPopup('<b>Posisi Anda Saat Ini</b>');
    } else {
        guruMarker.setLatLng([currentLat, currentLng]);
    }

    if (SCHOOL_HAS_GEO) {
        const dist = calculateDistanceGuru(currentLat, currentLng, SCHOOL_LAT, SCHOOL_LNG);
        currentDist = Math.round(dist);

        if (dist <= SCHOOL_RADIUS) {
            isOutsideRadius = false;
            schoolCircle.setStyle({ color: '#10b981', fillColor: '#34d399', fillOpacity: 0.25 });
            updateGuruGPSUI('success', `✅ Di Dalam Radius Sekolah (${currentDist} m)`, `Batas radius sekolah: ${SCHOOL_RADIUS} meter. Absensi diizinkan.`);
        } else {
            isOutsideRadius = true;
            schoolCircle.setStyle({ color: '#f43f5e', fillColor: '#fb7185', fillOpacity: 0.35 });
            updateGuruGPSUI('danger', `❌ Di Luar Radius Sekolah (${currentDist} m)`, `Maksimal toleransi: ${SCHOOL_RADIUS} meter. Absensi DITOLAK!`);
        }

        const group = L.featureGroup([schoolMarker, guruMarker]);
        guruMap.fitBounds(group.getBounds().pad(0.3));
    } else {
        isOutsideRadius = false;
        guruMap.setView([currentLat, currentLng], 16);
        updateGuruGPSUI('info', 'Lokasi GPS Terdeteksi', 'Sekolah belum menetapkan batas radius koordinat.');
    }
}

function updateGuruGPSUI(type, title, desc) {
    const card  = document.getElementById('gps-guru-card');
    const icon  = document.getElementById('gps-guru-icon');
    const titleEl = document.getElementById('gps-guru-title');
    const descEl  = document.getElementById('gps-guru-desc');
    const pulse   = document.getElementById('gps-guru-pulse');
    if (!card || !icon || !titleEl || !descEl || !pulse) return;

    titleEl.textContent = title;
    descEl.textContent = desc;

    card.className = 'mb-2 p-2.5 rounded-xl border transition-all ';
    pulse.className = 'w-2 h-2 rounded-full ';

    if (type === 'success') {
        card.classList.add('bg-emerald-50', 'border-emerald-200');
        icon.className = 'w-6 h-6 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        pulse.classList.add('bg-emerald-500');
    } else if (type === 'danger') {
        card.classList.add('bg-rose-50', 'border-rose-200');
        icon.className = 'w-6 h-6 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        pulse.classList.add('bg-rose-500');
    } else if (type === 'error') {
        card.classList.add('bg-amber-50', 'border-amber-200');
        icon.className = 'w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        pulse.classList.add('bg-amber-500');
    } else {
        card.classList.add('bg-sky-50', 'border-sky-200');
        icon.className = 'w-6 h-6 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin text-sky-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        pulse.classList.add('bg-sky-500', 'animate-pulse');
    }
}

// ===== MUAT MODEL AI =====
async function loadModels() {
    const steps = [
        { label: 'Memuat model deteksi wajah...', pct: 30, fn: () => faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL) },
        { label: 'Memuat model landmark...', pct: 60, fn: () => faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL) },
        { label: 'Memuat model pengenalan...', pct: 90, fn: () => faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL) },
    ];
    for (const s of steps) {
        document.getElementById('face-load-status').textContent = s.label;
        document.getElementById('face-load-bar').style.width = s.pct + '%';
        await s.fn();
    }
}

// ===== KAMERA =====
async function startFaceCamera() {
    const video  = document.getElementById('face-video');
    const canvas = document.getElementById('face-canvas');
    faceStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
    });
    video.srcObject = faceStream;
    await new Promise(r => video.addEventListener('loadeddata', r, { once: true }));
    faceapi.matchDimensions(canvas, { width: video.videoWidth, height: video.videoHeight });
}

function showLoading(show) {
    document.getElementById('face-loading').classList.toggle('hidden', !show);
    document.getElementById('face-camera-section').classList.toggle('hidden', show);
}

// ===== LOOP DETEKSI & VERIFIKASI =====
async function startDetectLoop() {
    const video    = document.getElementById('face-video');
    const oval     = document.getElementById('face-oval');
    const statusEl = document.getElementById('face-cam-status');

    if (!faceStream || !video || video.paused || video.ended) return;

    try {
        const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptors();

        const now = Date.now();

        if (detections.length > 0) {
            oval.style.borderColor = 'rgba(250,204,21,0.8)';

            if (!isProcessing) {
                if (!myDescriptor) {
                    oval.style.borderColor = 'rgba(239,68,68,0.8)';
                    statusEl.innerHTML = '<span style="color:#fca5a5">❌ Wajah belum terdaftar di sistem</span>';
                } else {
                    const q    = detections[0].descriptor;
                    const dist = faceapi.euclideanDistance(q, myDescriptor);

                    if (dist < THRESHOLD) {
                        if (now - lastSuccessAt > 4000) {
                            lastSuccessAt = now;
                            oval.style.borderColor = 'rgba(34,197,94,0.95)';
                            statusEl.innerHTML = '<span style="color:#86efac">✓ Wajah cocok: ' + CURRENT_GURU + '</span>';
                            await doAbsen();
                        }
                    } else {
                        oval.style.borderColor = 'rgba(239,68,68,0.9)';
                        if (now - rejectCooldown > 1500) {
                            rejectCooldown = now;
                            statusEl.innerHTML = '<span style="color:#fca5a5">❌ Wajah tidak sesuai dengan akun Anda</span>';
                        }
                    }
                }
            }
        } else {
            oval.style.borderColor = 'rgba(255,255,255,0.4)';
            if (!isProcessing) {
                statusEl.textContent = 'Arahkan wajah Anda ke dalam oval';
            }
        }
    } catch (err) {
        console.warn('Detect error:', err);
    }

    if (faceStream) {
        faceDetectTimer = setTimeout(startDetectLoop, 350);
    }
}

// ===== PROSES ABSENSI =====
async function doAbsen() {
    if (isProcessing) return;
    isProcessing = true;

    // Ambil koordinat GPS (gunakan currentLat/currentLng yang sudah aktif di peta atau fallback)
    let lat = currentLat;
    let lng = currentLng;
    if (!lat || !lng) {
        if (navigator.geolocation) {
            try {
                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 4000, enableHighAccuracy: true });
                });
                lat = pos.coords.latitude;
                lng = pos.coords.longitude;
            } catch (e) {
                console.warn('GPS dashboard error:', e);
            }
        }
    }

    try {
        const res  = await fetch(ABSEN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({
                tipe: currentTipe,
                latitude: lat,
                longitude: lng
            })
        });
        const data = await res.json();
        showFaceResult(data);

        if (data.success) {
            setTimeout(() => {
                closeCamera();
                setTimeout(() => location.reload(), 400);
            }, 2500);
        } else {
            setTimeout(() => { isProcessing = false; }, 3500);
        }
    } catch(e) {
        showFaceResult({ success: false, message: 'Koneksi gagal, silakan coba lagi.' });
        setTimeout(() => { isProcessing = false; }, 3500);
    }
}

// ===== TAMPILKAN HASIL =====
function showFaceResult(data) {
    const box  = document.getElementById('face-result');
    const icon = document.getElementById('face-result-icon');
    const name = document.getElementById('face-result-name');
    const msg  = document.getElementById('face-result-msg');
    const time = document.getElementById('face-result-time');

    box.className = 'mt-3 rounded-2xl p-4 text-center border';

    if (data.success) {
        box.classList.add('bg-emerald-50', 'border-emerald-200');
        icon.className  = 'w-12 h-12 mx-auto mb-2 rounded-full bg-emerald-100 flex items-center justify-center';
        icon.innerHTML  = '<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        name.className  = 'font-bold text-emerald-700 text-sm';
        name.textContent = currentTipe === 'masuk' ? '✅ Absensi Masuk Berhasil!' : '🚪 Absensi Pulang Berhasil!';
        msg.className   = 'text-xs text-emerald-600 mt-1 font-medium';
        msg.textContent = data.message;
        time.className  = 'text-xs font-bold text-emerald-500 mt-1';
        time.textContent = new Date().toLocaleTimeString('id-ID');
    } else {
        box.classList.add('bg-rose-50', 'border-rose-200');
        icon.className  = 'w-12 h-12 mx-auto mb-2 rounded-full bg-rose-100 flex items-center justify-center';
        icon.innerHTML  = '<svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        name.className   = 'font-bold text-rose-700 text-sm';
        name.textContent = '❌ Absensi Ditolak';
        msg.className    = 'text-xs text-rose-600 mt-1 font-medium';
        msg.textContent  = data.message;
        time.textContent = '';
    }
    box.classList.remove('hidden');
}

// ===== TUTUP KAMERA =====
function closeCamera() {
    if (faceDetectTimer) { clearTimeout(faceDetectTimer); faceDetectTimer = null; }
    if (faceStream)       { faceStream.getTracks().forEach(t => t.stop()); faceStream = null; }
    document.getElementById('modal-face').classList.add('hidden');
    document.getElementById('face-video').srcObject = null;
    isProcessing = false;
}

function showToast(type, message) {
    const toast = document.getElementById('toast-notif');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-2xl shadow-xl text-white text-sm font-semibold ' + (type === 'success' ? 'bg-emerald-600' : 'bg-rose-600');
    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

// Live Clock Real-time Guru
function updateGuruClock() {
    const el = document.getElementById('live-clock-guru');
    if (!el) return;
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    el.textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
}
setInterval(updateGuruClock, 1000);
</script>
@endpush


