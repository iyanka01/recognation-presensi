<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Siswa & Wali Murid – Mayantara Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen text-slate-800 antialiased">

    <div class="w-full max-w-md">
        {{-- Card Container --}}
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-xl shadow-sky-100/70 border border-sky-100 p-6 sm:p-8">
            {{-- Header / Logo --}}
            <div class="text-center mb-6">
                @if(isset($lembaga) && $lembaga && $lembaga->logo)
                    <img src="{{ Storage::url($lembaga->logo) }}" alt="Logo" class="w-16 h-16 rounded-2xl mx-auto object-contain bg-white border border-sky-100 p-1 shadow-sm mb-3">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 flex items-center justify-center text-white mx-auto shadow-md shadow-sky-200 mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                @endif
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Portal Siswa & Wali Murid</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">{{ $lembaga->nama_lembaga ?? 'Sistem Informasi Presensi Sekolah' }}</p>
            </div>

            {{-- Info Alert Bawaan --}}
            <div class="mb-5 p-3.5 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-sky-900">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Informasi Akses Masuk:
                </div>
                <p>• <b>Username:</b> Masukkan <b>NIS</b> atau <b>NISN</b> siswa</p>
                <p>• <b>Password Awal:</b> Gunakan <code class="px-1.5 py-0.5 bg-white border border-sky-200 rounded text-sky-700 font-mono font-bold">siswa123</code></p>
            </div>

            {{-- Flash Message --}}
            @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                {{ session('success') }}
            </div>
            @endif

            {{-- Form Login --}}
            <form action="{{ route('siswa.login.post') }}" method="POST" class="space-y-4">
                @csrf

                {{-- NIS/NISN --}}
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-bold text-slate-700">Nomor Induk Siswa (NIS / NISN)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all font-mono"
                               placeholder="Contoh: 10101 atau 0081234567">
                    </div>
                    @error('username')
                        <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" name="password" id="password" required
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all"
                               placeholder="Masukkan kata sandi">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-sky-200 active:scale-[0.99] flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Masuk ke Portal Siswa
                </button>
            </form>

            {{-- Link to Teacher / Admin Login --}}
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">
                    Bukan siswa atau wali murid?
                    <a href="{{ route('login') }}" class="text-sky-600 font-semibold hover:underline">Masuk sebagai Guru / Admin</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pass = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pass.type === 'password') {
                pass.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
            } else {
                pass.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>
