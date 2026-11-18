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
        <div id="live-clock-guru" class="text-2xl sm:text-3xl font-black text-white tracking-wider drop-shadow-md">
            {{ \Carbon\Carbon::now()->format('g:i:s A') }}
        </div>
    </div>

    {{-- ======== KARTU PROFIL GURU & ABSENSI (GAYA EDLINKS DENGAN WARNA HIJAU EMERALD) ======== --}}
    @php
        $jamSekarang = (int) date('H');
        if ($jamSekarang < 11) {
            $sapaan = 'Selamat Pagi,';
        } elseif ($jamSekarang < 15) {
            $sapaan = 'Selamat Siang,';
        } elseif ($jamSekarang < 18) {
            $sapaan = 'Selamat Sore,';
        } else {
            $sapaan = 'Selamat Malam,';
        }
    @endphp

    <div class="bg-white rounded-[28px] sm:rounded-3xl p-5 shadow-xl shadow-emerald-950/10 border border-emerald-50">
        
        {{-- Baris Profil: Avatar, Sapaan, Nama, Badge & Lonceng Notifikasi --}}
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-center gap-3.5 min-w-0">
                {{-- Avatar Bulat dengan Cincin Hijau --}}
                <div class="relative shrink-0">
                    @if($guru->foto_guru)
                        <img src="{{ Storage::url($guru->foto_guru) }}" alt="{{ $guru->nama_lengkap }}" class="w-14 h-14 rounded-full object-cover border-2 border-emerald-500 shadow-md bg-white">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-600 border-2 border-emerald-500 shadow-md flex items-center justify-center text-white font-extrabold text-xl">
                            {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                </div>

                {{-- Sapaan & Nama Guru --}}
                <div class="min-w-0">
                    <p class="text-xs text-slate-400 font-semibold leading-tight">{{ $sapaan }}</p>
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 truncate leading-tight mt-0.5">{{ $guru->nama_lengkap }}</h3>
                    
                    {{-- Badges / Status Chips ala Edukasi --}}
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[11px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            NIP: {{ $guru->nip ?? '-' }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600">
                            ⭐ {{ $stat['kelas_diajar'] ?? 0 }} Kelas
                        </span>
                    </div>
                </div>
            </div>

            {{-- Lonceng Informasi --}}
            <a href="{{ route('guru.informasi.index') }}" class="w-10 h-10 rounded-2xl bg-emerald-50 hover:bg-emerald-100/80 text-emerald-700 flex items-center justify-center transition-all shrink-0 relative shadow-sm border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </a>
        </div>

        {{-- Progress / Info Jam Sekolah (Tepat di bawah profil seperti di gambar) --}}
        <div class="mb-4">
            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                <span>Jam Kerja Sekolah</span>
                <span class="text-emerald-700">{{ $pengaturanJam ? substr($pengaturanJam->jam_mulai,0,5) . ' - ' . substr($pengaturanJam->jam_pulang,0,5) . ' WIB' : 'Reguler' }}</span>
            </div>
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ $presensiHariIni && $presensiHariIni->jam_pulang ? '100%' : ($presensiHariIni && $presensiHariIni->jam_masuk ? '60%' : '15%') }}"></div>
            </div>
        </div>

        {{-- ======== PENGGANTI SEARCH BAR: CONTAINER ABSEN MASUK & ABSEN PULANG ======== --}}
        <div class="bg-slate-50/90 border border-slate-200/90 rounded-2xl p-1.5 flex items-center gap-1.5 shadow-inner">
            
            {{-- Tombol / Status Absen Masuk --}}
            @if($presensiHariIni && $presensiHariIni->jam_masuk)
                <div class="flex-1 py-2.5 px-3 rounded-xl bg-white border border-emerald-300 shadow-sm flex items-center justify-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="text-left leading-tight truncate">
                        <p class="text-[9px] text-slate-400 uppercase font-black">Masuk</p>
                        <p class="text-xs font-black text-emerald-700">{{ substr($presensiHariIni->jam_masuk,0,5) }} <span class="text-[10px] font-semibold text-emerald-600">({{ $presensiHariIni->status }})</span></p>
                    </div>
                </div>
            @elseif(isset($lockedPresensi) && $lockedPresensi)
                <button type="button" onclick="openModal('masuk')" class="flex-1 py-2.5 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 shadow-sm flex items-center justify-center gap-2 transition-all active:scale-95 text-left">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span class="text-xs font-extrabold truncate">Terkunci 🔒</span>
                </button>
            @else
                <button type="button" onclick="openModal('masuk')" class="flex-1 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span class="text-xs font-black tracking-wide">Absen Masuk</span>
                </button>
            @endif

            {{-- Tombol / Status Absen Pulang --}}
            @if($presensiHariIni && $presensiHariIni->jam_pulang)
                <div class="flex-1 py-2.5 px-3 rounded-xl bg-white border border-teal-300 shadow-sm flex items-center justify-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="text-left leading-tight truncate">
                        <p class="text-[9px] text-slate-400 uppercase font-black">Pulang</p>
                        <p class="text-xs font-black text-teal-700">{{ substr($presensiHariIni->jam_pulang,0,5) }} <span class="text-[10px] font-semibold text-teal-600">Selesai</span></p>
                    </div>
                </div>
            @else
                <button type="button" onclick="openModal('pulang')" class="flex-1 py-2.5 px-3 rounded-xl bg-white hover:bg-teal-50 text-slate-700 hover:text-teal-700 border border-slate-200 shadow-sm flex items-center justify-center gap-2 transition-all active:scale-95">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-xs font-black tracking-wide">Absen Pulang</span>
                </button>
            @endif

        </div>

    </div>

    {{-- ======== GRID 8 MENU BULAT MODERN (PERSIS SEPERTI GAMBAR DENGAN TEMA HIJAU EMERALD) ======== --}}
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xl shadow-slate-200/50">
        <div class="grid grid-cols-4 gap-y-5 gap-x-2 text-center">

            {{-- 1. Absen Mapel --}}
            <a href="{{ route('absensi-mapel.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-emerald-50 group-hover:bg-emerald-100 border border-emerald-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" alt="Absen Mapel" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Absen Mapel</span>
            </a>

            {{-- 2. Jadwal Mengajar --}}
            <a href="{{ route('guru.jadwal.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-teal-50 group-hover:bg-teal-100 border border-teal-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/3652/3652191.png" alt="Jadwal" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Jadwal</span>
            </a>

            {{-- 3. Scan Wajah --}}
            <a href="{{ route('guru.face-scan') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-cyan-50 group-hover:bg-cyan-100 border border-cyan-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/11502/11502540.png" alt="Scan Wajah" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Face Scan</span>
            </a>

            {{-- 4. Rekap Absen --}}
            <a href="{{ route('rekap-mapel.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-blue-50 group-hover:bg-blue-100 border border-blue-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/942/942751.png" alt="Rekap Absen" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Rekap</span>
            </a>

            {{-- 5. Izin & Cuti --}}
            <a href="{{ route('guru.izin.index', ['open' => 1]) }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-amber-50 group-hover:bg-amber-100 border border-amber-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/2921/2921222.png" alt="Izin & Cuti" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Izin / Cuti</span>
            </a>

            {{-- 6. Informasi & SP --}}
            <a href="{{ route('guru.informasi.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-rose-50 group-hover:bg-rose-100 border border-rose-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/2645/2645897.png" alt="Informasi" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Informasi</span>
            </a>

            {{-- 7. Profil & Sandi --}}
            <button type="button" onclick="openAccountModal()" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-slate-50 group-hover:bg-slate-100 border border-slate-200 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Akun Guru" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Profil</span>
            </button>

            {{-- 8. Absen Kegiatan Khusus (Siswa) --}}
            <a href="{{ route('guru.kegiatan.index') }}" class="flex flex-col items-center justify-start group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-violet-50 group-hover:bg-violet-100 border border-violet-100 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm p-3 relative">
                    <img src="https://cdn-icons-png.flaticon.com/512/3222/3222800.png" alt="Absen Kegiatan" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                    @if(isset($kegiatanSiswaHariIni) && $kegiatanSiswaHariIni->isNotEmpty())
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full animate-ping"></span>
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                    @endif
                </div>
                <span class="text-[11px] font-bold text-slate-800 mt-2 leading-tight">Kegiatan</span>
            </a>

        </div>
    </div>


    {{-- ======== AGENDA KEGIATAN KHUSUS SISWA (SCAN ABSEN OLEH GURU) ======== --}}
    @if(isset($kegiatanSiswaHariIni) && $kegiatanSiswaHariIni->isNotEmpty())
    <div class="bg-gradient-to-br from-emerald-700 via-teal-800 to-emerald-950 rounded-[28px] p-5 border border-emerald-500/40 shadow-xl shadow-emerald-950/20 text-white relative overflow-hidden">
        {{-- Background glow accents --}}
        <div class="absolute -top-10 -right-10 w-36 h-36 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between mb-3.5 relative z-10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </span>
                <div>
                    <h3 class="text-xs sm:text-sm font-black tracking-wide text-white">Kegiatan Khusus Siswa</h3>
                    <p class="text-[10px] text-emerald-100/90">Scan presensi kartu peserta kegiatan</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white border border-white/30">
                {{ $kegiatanSiswaHariIni->count() }} Kegiatan
            </span>
        </div>

        <div class="space-y-2.5 relative z-10">
            @foreach($kegiatanSiswaHariIni as $kg)
            <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-2xl p-3.5 border border-white/15 transition-all flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            #{{ $kg->kode_kegiatan }}
                        </span>
                        <h4 class="text-xs font-bold text-white truncate">{{ $kg->nama_kegiatan }}</h4>
                    </div>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-emerald-100">
                        <span class="font-semibold">
                            {{ substr($kg->jam_mulai,0,5) }} - {{ substr($kg->jam_selesai,0,5) }} WIB
                        </span>
                        <span>&bull;</span>
                        <span class="truncate text-[10px] text-emerald-200 font-medium">{{ $kg->sasaran_label }}</span>
                    </div>
                </div>

                {{-- Tombol Scan Kegiatan --}}
                <a href="{{ route('kegiatan-khusus.scan', $kg->id) }}"
                   class="shrink-0 px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 font-extrabold text-xs shadow-md shadow-emerald-950/20 flex items-center gap-1.5 active:scale-95 transition-all">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Absen</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ======== KARTU PENGUMUMAN / INFORMASI SEKOLAH ======== --}}
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-100 shadow-xl shadow-slate-200/50 flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 text-xs font-black text-slate-800 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Pengumuman &amp; Informasi</span>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded-full">Terbaru</span>
            </div>
            @if(isset($pengumumanTerbaru) && $pengumumanTerbaru)
                <a href="{{ route('guru.informasi.show', $pengumumanTerbaru->id) }}" class="group block">
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-1">
                        {{ $pengumumanTerbaru->judul }}
                    </h4>
                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5 leading-snug">
                        {{ Str::limit(strip_tags($pengumumanTerbaru->isi), 90) }}
                    </p>
                    <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold mt-1 group-hover:underline">
                        <span>Lihat detail info</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @else
                <p class="text-xs font-bold text-slate-700 mt-0.5">Belum ada pengumuman baru</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh pengumuman atau surat edaran sekolah akan ditampilkan di sini.</p>
                <a href="{{ route('guru.informasi.index') }}" class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold mt-1 hover:underline">
                    <span>Buka papan informasi</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>
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

{{-- Audio Suara Terimakasih --}}
<audio id="audio-terimakasih" preload="auto">
    <source src="{{ asset('assets/audio/terimakasih.mp3') }}" type="audio/mpeg">
    <source src="{{ asset('terimakasih.mp3') }}" type="audio/mpeg">
</audio>

{{-- ======== MODAL POPUP PEMBERITAHUAN SUKSES ABSENSI ======== --}}
<div id="modal-success-popup" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md animate-fade-in">
    <div class="bg-white rounded-3xl max-w-xs sm:max-w-sm w-full p-6 text-center shadow-2xl border border-emerald-100 transform transition-all scale-100 animate-scale-up">
        {{-- Ikon Centang Hijau Besar Beranimasi --}}
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-500 shadow-lg shadow-emerald-200/50">
            <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h3 id="popup-success-title" class="text-lg sm:text-xl font-extrabold text-slate-800">Absensi Berhasil!</h3>
        <p id="popup-success-desc" class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed font-medium">Terima kasih, data kehadiran Anda telah berhasil disimpan ke sistem.</p>

        {{-- Badge Jam & Status --}}
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50/80 border border-emerald-100 flex items-center justify-around">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Waktu</p>
                <p id="popup-success-time" class="text-sm font-black text-emerald-700 mt-0.5">--:--</p>
            </div>
            <div class="h-6 w-px bg-emerald-200/60"></div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Status</p>
                <p id="popup-success-status" class="text-sm font-black text-emerald-700 mt-0.5">Tercatat</p>
            </div>
        </div>

        <p class="text-[11px] text-slate-400 mt-4 italic font-medium">Memindahkan ke halaman utama...</p>
    </div>
</div>

{{-- ======== MODAL POPUP KHUSUS PRESENSI TERKUNCI ======== --}}
@if(isset($lockedPresensi) && $lockedPresensi)
@php
    $tglLockedStr = \Carbon\Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
@endphp
<div id="modal-locked-popup" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-rose-200 transform transition-all animate-scale-up relative">
        
        {{-- Tombol X Tutup --}}
        <button type="button" onclick="closeLockedPopup()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Ikon Gembok Merah --}}
        <div class="w-16 h-16 mx-auto mb-3.5 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shadow-md shadow-rose-200">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h3 class="text-lg font-black text-slate-900 leading-tight">Presensi Anda Terkunci</h3>
        
        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Anda tidak melakukan <strong>absen pulang</strong> pada hari <br><span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100 inline-block mt-1">{{ $tglLockedStr }}</span>.
        </p>

        <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
            Sistem mengunci absen masuk Anda hari ini sampai diizinkan / dibuka oleh admin.
        </p>

        {{-- Kotak Hubungi Admin --}}
        <div class="mt-4 p-3 bg-rose-50/80 border border-rose-200/80 rounded-2xl text-[11px] font-semibold text-rose-800 flex items-start gap-2 text-left">
            <span class="text-base shrink-0">💬</span>
            <span class="leading-snug">Silakan hubungi <strong>Administrator Sekolah</strong> untuk membuka kunci kehadiran Anda.</span>
        </div>

        {{-- Tombol Tutup --}}
        <button type="button" onclick="closeLockedPopup()" class="w-full mt-4 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-95">
            Mengerti & Tutup
        </button>
    </div>
</div>
@endif

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
const IS_LOCKED       = {{ (isset($lockedPresensi) && $lockedPresensi) ? 'true' : 'false' }};
const LOCKED_DATE     = @json(isset($lockedPresensi) && $lockedPresensi ? \Carbon\Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') : '');
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

function showLockedPopup() {
    const p = document.getElementById('modal-locked-popup');
    if (p) p.classList.remove('hidden');
}

function closeLockedPopup() {
    const p = document.getElementById('modal-locked-popup');
    if (p) p.classList.add('hidden');
}

// ===== BUKA MODAL KAMERA =====
async function openModal(tipe) {
    console.log('[openModal] Dipanggil dengan tipe:', tipe, '| HAS_FACE:', HAS_FACE, '| myDescriptor:', !!myDescriptor);
    
    // Cek apakah guru terkunci karena tidak absen pulang di hari sebelumnya
    if (tipe === 'masuk' && IS_LOCKED) {
        showLockedPopup();
        return;
    }

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
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN 
            },
            body: JSON.stringify({
                tipe: currentTipe,
                latitude: lat,
                longitude: lng
            })
        });

        let data;
        try {
            data = await res.json();
        } catch(jsonErr) {
            data = { success: false, message: 'Server merespons status ' + res.status + ' (' + res.statusText + ')' };
        }

        if (data.success) {
            // Putar audio terimakasih
            playAudioTerimakasih();

            // Tampilkan popup notifikasi sukses besar
            showSuccessPopup(data);

            // Tutup kamera scan langsung
            closeCamera();

            // Beri jeda 2.2 detik agar suara selesai & popup terbaca lalu refresh ke halaman awal
            setTimeout(() => {
                location.reload();
            }, 2200);
        } else {
            showFaceResult(data);
            setTimeout(() => { isProcessing = false; }, 3500);
        }
    } catch(e) {
        console.error('Absen fetch error:', e);
        showFaceResult({ success: false, message: e.message || 'Koneksi gagal, silakan coba lagi.' });
        setTimeout(() => { isProcessing = false; }, 3500);
    }
}

// ===== FUNGSI AUDIO TERIMAKASIH =====
function playAudioTerimakasih() {
    try {
        const audio = document.getElementById('audio-terimakasih');
        if (audio) {
            audio.currentTime = 0;
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(err => {
                    console.warn('Autoplay audio blocked or error:', err);
                });
            }
        }
    } catch(err) {
        console.warn('Error playing audio:', err);
    }
}

// ===== POPUP PEMBERITAHUAN SUKSES & REDIRECT KE AWAL =====
function showSuccessPopup(data) {
    const popup = document.getElementById('modal-success-popup');
    const title = document.getElementById('popup-success-title');
    const desc  = document.getElementById('popup-success-desc');
    const time  = document.getElementById('popup-success-time');
    const status= document.getElementById('popup-success-status');

    if (!popup) return;

    if (title) title.textContent = currentTipe === 'masuk' ? 'Absensi Masuk Berhasil!' : 'Absensi Pulang Berhasil!';
    if (desc)  desc.textContent  = data.message || 'Terima kasih, data kehadiran Anda telah berhasil disimpan.';
    if (time)  time.textContent  = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    if (status)status.textContent= data.status || 'Hadir';

    popup.classList.remove('hidden');
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


