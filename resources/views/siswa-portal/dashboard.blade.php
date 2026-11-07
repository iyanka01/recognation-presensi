@extends('layouts.siswa')

@section('title', 'Beranda')

@section('content')
<div class="space-y-4">

    {{-- ======== PERINGATAN GANTI PASSWORD (JIKA BELUM DIUBAH) ======== --}}
    @if($needChangePassword)
    <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-2xl p-3.5 shadow-md flex items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="text-base">⚠️</span>
            <span>Password akun Anda masih default (<b>siswa123</b>). Silakan ubah demi keamanan.</span>
        </div>
        <a href="{{ route('siswa.password') }}" class="px-3 py-1.5 bg-white text-orange-600 font-bold rounded-xl shrink-0 shadow-sm">
            Ubah
        </a>
    </div>
    @endif

    {{-- ======== BANNER WAKTU DIGITAL REAL-TIME (JAM PERSIS SEPERTI GAMBAR) ======== --}}
    <div class="text-center pt-2 pb-1">
        <div id="live-clock" class="text-3xl sm:text-4xl font-black text-white tracking-wider drop-shadow-md">
            {{ \Carbon\Carbon::now()->format('g:i:s A') }}
        </div>
    </div>

    {{-- ======== KARTU BIRU PROFIL SISWA (SEPERTI GAMBAR) ======== --}}
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl p-4 shadow-lg shadow-sky-200/50 flex items-center gap-3.5">
        {{-- Avatar Bulat --}}
        <div class="relative shrink-0">
            @if($siswa->foto_siswa)
                <img src="{{ Storage::url($siswa->foto_siswa) }}" alt="{{ $siswa->nama_lengkap }}" class="w-12 h-12 rounded-full object-cover border-2 border-white/80 shadow-sm bg-white">
            @else
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 border-2 border-white/80 shadow-sm flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            @endif
        </div>

        {{-- Info Nama & Siswa --}}
        <div class="min-w-0 flex-1">
            <h3 class="text-base sm:text-lg font-black truncate leading-tight">{{ $siswa->nama_lengkap }}</h3>
            <p class="text-xs text-sky-100 font-medium truncate mt-0.5">Siswa: {{ $siswa->nama_lengkap }}</p>
        </div>
    </div>

    {{-- ======== GRID 8 MENU IKON UTAMA (PERSIS SEPERTI GAMBAR) ======== --}}
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xl shadow-slate-200/40">
        <div class="grid grid-cols-3 gap-y-6 gap-x-2 text-center">
            
            {{-- 1. Rekap Absensi --}}
            <a href="{{ route('siswa.presensi') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 group-hover:bg-sky-100 border border-sky-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/942/942751.png" alt="Rekap Absensi" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Rekap Absensi</span>
            </a>

            {{-- 2. Rekap E-KBM --}}
            <a href="{{ route('siswa.presensi') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 group-hover:bg-emerald-100 border border-emerald-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/1534/1534938.png" alt="Rekap E-KBM" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Rekap E-KBM</span>
            </a>

            {{-- 3. Izin --}}
            <a href="{{ route('siswa.izin') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 group-hover:bg-blue-100 border border-blue-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" alt="Izin" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Izin</span>
            </a>

            {{-- 4. Pelanggaran --}}
            <div class="flex flex-col items-center justify-start group cursor-pointer relative" onclick="alert('Tidak ada catatan pelanggaran tata tertib siswa.')">
                {{-- Badge Angka 0 Merah Seperti Gambar --}}
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 group-hover:bg-amber-100 border border-amber-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                        <img src="https://cdn-icons-png.flaticon.com/512/8205/8205423.png" alt="Pelanggaran" class="w-8 h-8 object-contain">
                    </div>
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-600 text-white font-extrabold text-[10px] rounded-full flex items-center justify-center border-2 border-white shadow">0</span>
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Pelanggaran</span>
            </div>

            {{-- 5. Sanksi Pelanggaran --}}
            <div class="flex flex-col items-center justify-start group cursor-pointer" onclick="alert('Tidak ada sanksi pelanggaran yang berlaku.')">
                <div class="w-14 h-14 rounded-2xl bg-cyan-50 group-hover:bg-cyan-100 border border-cyan-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Sanksi Pelanggaran" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Sanksi Pelanggaran</span>
            </div>

            {{-- 6. Informasi --}}
            <a href="#informasi-section" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-yellow-50 group-hover:bg-yellow-100 border border-yellow-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/2645/2645897.png" alt="Informasi" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Informasi</span>
            </a>

            {{-- 7. Profile --}}
            <a href="{{ route('siswa.akun') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 group-hover:bg-indigo-100 border border-indigo-100/80 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Profile" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Profile</span>
            </a>

            {{-- 8. Setting --}}
            <a href="{{ route('siswa.akun') }}" class="flex flex-col items-center justify-start group">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 group-hover:bg-slate-100 border border-slate-200 flex items-center justify-center transition-all duration-200 group-hover:scale-105 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/2040/2040504.png" alt="Setting" class="w-8 h-8 object-contain">
                </div>
                <span class="text-xs font-semibold text-slate-700 mt-2 leading-tight">Setting</span>
            </a>

        </div>
    </div>


    {{-- ======== PENGUMUMAN & INFORMASI SEKOLAH ======== --}}
    <div id="informasi-section" class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-3">
        <div class="flex items-center gap-2">
            <span class="text-base">📢</span>
            <h3 class="font-extrabold text-slate-800 text-sm">Informasi & Pengumuman Sekolah</h3>
        </div>

        @if($pengumuman->count() > 0)
        <div class="space-y-3">
            @foreach($pengumuman as $p)
            <div class="p-3.5 rounded-2xl bg-sky-50/60 border border-sky-100">
                <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1">
                    <span class="font-bold text-sky-700 uppercase tracking-wider">Pengumuman</span>
                    <span>{{ Carbon\Carbon::parse($p->created_at)->locale('id')->isoFormat('D MMM Y') }}</span>
                </div>
                <h4 class="font-bold text-xs sm:text-sm text-slate-800">{{ $p->judul }}</h4>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $p->isi }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4 text-xs text-slate-400">
            Belum ada pengumuman baru dari sekolah.
        </div>
        @endif
    </div>

</div>

{{-- Script Jam Digital Berjalan --}}
<script>
    function updateLiveClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 menjadi 12
        const strTime = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        const clockEl = document.getElementById('live-clock');
        if (clockEl) {
            clockEl.textContent = strTime;
        }
    }
    setInterval(updateLiveClock, 1000);
</script>
@endsection
