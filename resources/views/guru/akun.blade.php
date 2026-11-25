@extends('layouts.guru')

@section('title', 'Pengaturan Akun')
@section('back_url', route('dashboard'))

@section('content')
<div class="space-y-4">

    {{-- Error Banner --}}
    @if ($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs sm:text-sm text-rose-700 space-y-1.5 shadow-sm">
        <p class="font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Terdapat kesalahan pada input Anda:
        </p>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Banner Atas & Foto Avatar Profil Guru --}}
    <div class="relative bg-gradient-to-r from-emerald-800 via-teal-700 to-emerald-900 rounded-3xl pt-8 pb-16 px-4 text-center overflow-hidden shadow-lg shadow-emerald-900/20">
        {{-- Background Ornament --}}
        <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-teal-400/20 blur-xl pointer-events-none"></div>

        <h2 class="text-white font-extrabold text-base tracking-wide">Pengaturan Profil & Akun</h2>
        <p class="text-emerald-200 text-xs mt-0.5">Kelola foto profil, tanggal lahir, dan keamanan kata sandi Anda</p>
    </div>

    {{-- Lingkaran Avatar Mengambang di Tengah --}}
    <div class="relative -mt-16 flex flex-col items-center justify-center z-10">
        <div class="relative group">
            @if($guru->foto_guru)
                <img src="{{ Storage::url($guru->foto_guru) }}" alt="{{ $guru->nama_lengkap }}" 
                     class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-xl bg-white">
            @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 border-4 border-white shadow-xl flex items-center justify-center text-white font-black text-3xl">
                    {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                </div>
            @endif

            {{-- Tombol Ganti Foto (Badge Kamera) --}}
            <label for="input-foto-guru" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-lg border-2 border-white cursor-pointer transition-transform active:scale-95" title="Klik untuk ganti foto profil">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </label>

            {{-- Form Upload Foto Otomatis Submit saat Memilih File --}}
            <form id="form-foto-guru" action="{{ route('guru.akun.foto') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="foto_guru" id="input-foto-guru" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="document.getElementById('form-foto-guru').submit()">
            </form>
        </div>

        <h3 class="text-base font-extrabold text-slate-800 mt-2.5">{{ $guru->nama_lengkap }}</h3>
        <p class="text-xs text-slate-400 font-mono">NIP: {{ $guru->nip ?: '-' }} • {{ $guru->jabatan ?: 'Guru' }}</p>
        
        <div class="flex items-center gap-2 mt-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Guru Aktif
            </span>
            @if($guru->face_descriptor)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                    <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Wajah Terdaftar
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    ⚠️ Belum Absen Wajah
                </span>
            @endif
        </div>
    </div>

    {{-- KARTU 1: DATA PROFIL & TANGGAL LAHIR (BISA DIUBAH) --}}
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-slate-800">Data Profil & Pribadi</h4>
                <p class="text-xs text-slate-400">Ubah tanggal lahir, tempat lahir, dan nomor kontak Anda</p>
            </div>
        </div>

        <form action="{{ route('guru.akun.profil') }}" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            {{-- Info Readonly: NIP & Username --}}
            <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">NIP</span>
                    <span class="font-bold text-slate-700">{{ $guru->nip ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Username Login</span>
                    <span class="font-bold text-slate-700">{{ $guru->username ?: '-' }}</span>
                </div>
            </div>

            {{-- Input Tempat Lahir --}}
            <div>
                <label for="tempat_lahir" class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" 
                           value="{{ old('tempat_lahir', $guru->tempat_lahir) }}" 
                           placeholder="Kota kelahiran Anda (contoh: Jakarta)"
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                </div>
            </div>

            {{-- Input Tanggal Lahir (Permintaan Utama) --}}
            <div>
                <label for="tanggal_lahir" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Tanggal Lahir <span class="text-emerald-600 font-semibold">(Bisa Diedit)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                           value="{{ old('tanggal_lahir', $guru->tanggal_lahir ? \Carbon\Carbon::parse($guru->tanggal_lahir)->format('Y-m-d') : '') }}" 
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all font-medium text-slate-700">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Pilih tanggal lahir sesuai identitas resmi Anda.</p>
            </div>

            {{-- Input No HP / WhatsApp --}}
            <div>
                <label for="no_hp" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <input type="tel" name="no_hp" id="no_hp" 
                           value="{{ old('no_hp', $guru->no_hp) }}" 
                           placeholder="081234567890"
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                </div>
            </div>

            {{-- Tombol Simpan Profil --}}
            <button type="submit" 
                    class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan Profil</span>
            </button>
        </form>
    </div>

    {{-- KARTU 2: KEAMANAN & UBAH PASSWORD (INTEGRATED OPSI A) --}}
    <div id="keamanan" class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-slate-800">Keamanan & Ubah Password</h4>
                <p class="text-xs text-slate-400">Perbarui kata sandi akun login portal Anda</p>
            </div>
        </div>

        <form action="{{ route('guru.password.update') }}" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            {{-- Password Saat Ini --}}
            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" required
                           placeholder="Masukkan kata sandi lama Anda"
                           class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <button type="button" onclick="togglePass('current_password', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="6"
                           placeholder="Minimal 6 karakter"
                           class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <button type="button" onclick="togglePass('password', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6"
                           placeholder="Ketik ulang password baru Anda"
                           class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <button type="button" onclick="togglePass('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tombol Ubah Password --}}
            <button type="submit" 
                    class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-indigo-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Perbarui Kata Sandi</span>
            </button>
        </form>
    </div>

    {{-- KARTU 3: LOGOUT AKUN --}}
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-100 shadow-sm">
        <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun?')">
            @csrf
            <button type="submit" 
                    class="w-full p-3 rounded-2xl hover:bg-rose-50 text-rose-600 transition-all flex items-center justify-between text-left group">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="font-bold text-sm text-rose-700">Logout</h5>
                        <p class="text-xs text-rose-400">Keluar dari aplikasi portal guru</p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-rose-300 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
    </div>

</div>

<script>
function togglePass(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.classList.toggle('text-indigo-600', isPass);
    btn.classList.toggle('text-slate-400', !isPass);
}
</script>
@endsection
