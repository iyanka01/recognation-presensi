@extends('layouts.guru')

@section('title', 'Ubah Password')
@section('back_url', route('dashboard'))

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    {{-- Alert jika masih pakai default password --}}
    @if($isDefaultPassword)
    <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center shrink-0 text-amber-600 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h4 class="font-bold text-amber-900 text-sm">Peringatan Keamanan</h4>
            <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                Akun Anda saat ini masih menggunakan password standar (<strong>guru123</strong>). Demi keamanan data presensi dan akun Anda, buatlah password baru yang kuat.
            </p>
        </div>
    </div>
    @endif

    {{-- Alert jika ada error validasi --}}
    @if ($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs sm:text-sm text-rose-700 space-y-1.5 shadow-sm">
        <p class="font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Gagal Memperbarui Password:
        </p>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Card Form --}}
    <div class="bg-white rounded-3xl border border-sky-100 shadow-sm overflow-hidden p-5 sm:p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-800">Keamanan Akun Guru</h2>
                <p class="text-xs text-slate-400">Perbarui kata sandi login portal Anda</p>
            </div>
        </div>

        <form action="{{ route('guru.password.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Info Akun --}}
            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Nama Guru:</span>
                    <span class="font-bold text-slate-700">{{ $guru->nama_lengkap }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">NIP / Username:</span>
                    <span class="font-bold text-sky-700">{{ $guru->username ?: ($guru->nip ?: '-') }}</span>
                </div>
            </div>

            {{-- Input Password Saat Ini --}}
            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Password Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           required
                           placeholder="Masukkan password saat ini (misal: guru123)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border @error('current_password') border-rose-300 bg-rose-50/50 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:bg-white transition-all pr-10">
                    <button type="button" onclick="togglePassword('current_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-rose-600 text-[11px] font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Password Baru --}}
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Password Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           minlength="6"
                           placeholder="Minimal 6 karakter"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border @error('password') border-rose-300 bg-rose-50/50 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:bg-white transition-all pr-10">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-rose-600 text-[11px] font-medium mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-slate-400 mt-1">Gunakan minimal 6 karakter kombinasi huruf dan angka.</p>
            </div>

            {{-- Input Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Konfirmasi Password Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required
                           minlength="6"
                           placeholder="Ketik ulang password baru"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:bg-white transition-all pr-10">
                    <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-3 flex flex-col sm:flex-row gap-2.5">
                <button type="submit" 
                        class="w-full sm:flex-1 py-3 px-4 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Password Baru</span>
                </button>
                <a href="{{ route('dashboard') }}" 
                   class="w-full sm:w-auto py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition-all text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}
</script>
@endsection
