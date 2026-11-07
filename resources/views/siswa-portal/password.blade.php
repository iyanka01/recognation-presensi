@extends('layouts.siswa')

@section('title', 'Ubah Kata Sandi')
@section('back_url', route('siswa.dashboard'))

@section('content')
<div class="space-y-4">

    <div class="bg-gradient-to-r from-sky-600 to-blue-700 text-white rounded-3xl p-5 shadow-sm">
        <h3 class="font-extrabold text-base">Pengaturan Kata Sandi</h3>
        <p class="text-xs text-sky-100 mt-1">Ubah kata sandi akun untuk menjaga keamanan data kehadiran siswa</p>
    </div>

    @if(!$siswa->is_password_changed)
    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs flex items-start gap-2.5">
        <span class="text-base shrink-0">⚠️</span>
        <div>
            <p class="font-bold">Akun Anda masih menggunakan kata sandi bawaan (siswa123).</p>
            <p class="text-[11px] text-amber-700 mt-0.5">Sangat disarankan untuk segera mengubahnya menjadi kata sandi baru yang mudah Anda ingat.</p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-3xl p-6 border border-sky-100 shadow-sm">
        <form action="{{ route('siswa.password.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Password Lama --}}
            <div class="space-y-1.5">
                <label for="current_password" class="block text-xs font-bold text-slate-700">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
                <input type="password" name="current_password" id="current_password" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400"
                       placeholder="Masukkan kata sandi saat ini (misal: siswa123)">
                @error('current_password')
                    <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400"
                       placeholder="Minimal 6 karakter">
                @error('password')
                    <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400"
                       placeholder="Ketik ulang kata sandi baru">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-sky-200 active:scale-[0.99] flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Kata Sandi Baru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
