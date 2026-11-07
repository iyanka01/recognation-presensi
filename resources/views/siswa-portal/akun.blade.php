@extends('layouts.siswa')

@section('title', 'Pengaturan')
@section('back_url', route('siswa.dashboard'))

@section('content')
<div class="space-y-4">

    {{-- Banner Atas & Foto Avatar Profil (Persis Desain Gambar) --}}
    <div class="relative bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-700 rounded-3xl pt-8 pb-16 px-4 text-center overflow-hidden shadow-lg shadow-indigo-100">
        {{-- Background Pattern Hiasan --}}
        <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-purple-400/20 blur-xl pointer-events-none"></div>

        <h2 class="text-white font-extrabold text-base tracking-wide">Pengaturan Akun</h2>
        <p class="text-indigo-200 text-xs mt-0.5">Kelola data profil dan keamanan akun siswa</p>
    </div>

    {{-- Lingkaran Avatar Mengambang di Tengah --}}
    <div class="relative -mt-16 flex flex-col items-center justify-center z-10">
        <div class="relative group">
            @if($siswa->foto_siswa)
                <img src="{{ Storage::url($siswa->foto_siswa) }}" alt="{{ $siswa->nama_lengkap }}" 
                     class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-xl bg-white">
            @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 border-4 border-white shadow-xl flex items-center justify-center text-white">
                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            @endif

            {{-- Tombol Ganti Foto / Kamera (Kamera Biru/Ungu Kecil Seperti Gambar) --}}
            <label for="input-foto" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-lg border-2 border-white cursor-pointer transition-transform active:scale-95" title="Ganti Foto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </label>
            <form id="form-foto" action="{{ route('siswa.foto.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="foto_siswa" id="input-foto" accept="image/*" onchange="document.getElementById('form-foto').submit()">
            </form>
        </div>

        <h3 class="text-base font-extrabold text-slate-800 mt-2.5">{{ $siswa->nama_lengkap }}</h3>
        <p class="text-xs text-slate-400 font-mono">NIS: {{ $siswa->nis }} • {{ $siswa->kelas->nama_rombel ?? 'Siswa' }}</p>
    </div>

    {{-- KOTAK LIST MENU PENGATURAN (PERSIS SEPERTI GAMBAR) --}}
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-100 shadow-sm space-y-1">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2 py-1 mb-1">Pengaturan</h4>

        {{-- 1. Profile --}}
        <div class="p-3 rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h5 class="font-bold text-sm text-slate-800">Profile</h5>
                <p class="text-xs text-slate-400 truncate">Nama: {{ $siswa->nama_lengkap }} ({{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }})</p>
            </div>
        </div>

        <div class="border-t border-slate-100 my-1"></div>

        {{-- 2. Keamanan (Ubah Password) --}}
        <a href="{{ route('siswa.password') }}" class="p-3 rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-2xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h5 class="font-bold text-sm text-slate-800 group-hover:text-indigo-600 transition-colors">Keamanan</h5>
                <p class="text-xs text-slate-400">Ubah Password Akun Siswa</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <div class="border-t border-slate-100 my-1"></div>

        {{-- 3. Tampilan --}}
        <div class="p-3 rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h5 class="font-bold text-sm text-slate-800">Tampilan</h5>
                <p class="text-xs text-slate-400">Tema Default: Modern Light Sky</p>
            </div>
        </div>

        <div class="border-t border-slate-100 my-1"></div>

        {{-- 4. Logout --}}
        <form action="{{ route('siswa.logout') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')" 
                    class="w-full p-3 rounded-2xl hover:bg-rose-50 transition-all flex items-center gap-3.5 text-left group">
                <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h5 class="font-bold text-sm text-rose-600">Logout</h5>
                    <p class="text-xs text-rose-400">Keluar dari aplikasi</p>
                </div>
                <svg class="w-4 h-4 text-rose-300 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
    </div>

</div>
@endsection
