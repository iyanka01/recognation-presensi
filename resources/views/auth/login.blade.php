<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .login-left {
            background: linear-gradient(135deg, #0369a1 0%, #075985 50%, #0c4a6e 100%);
        }
        .login-blob-1 { position: absolute; top: -80px; right: -80px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(56,189,248,0.25) 0%, transparent 70%); border-radius: 50%; }
        .login-blob-2 { position: absolute; bottom: -60px; left: -60px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(14,165,233,0.15) 0%, transparent 70%); border-radius: 50%; }
        .login-right { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
    </style>
</head>
<body class="h-screen flex overflow-hidden">
    
    <!-- Left Side: Branding -->
    <div class="hidden lg:flex w-1/2 login-left relative items-center justify-center overflow-hidden">
        <div class="login-blob-1"></div>
        <div class="login-blob-2"></div>
        
        <div class="z-10 text-center px-12">
            <!-- Logo icon -->
            @if(isset($globalLembaga) && $globalLembaga->logo)
                <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo" class="w-24 h-24 object-contain bg-white rounded-3xl p-2.5 mx-auto mb-8 shadow-2xl ring-4 ring-white/15">
            @else
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 mb-8 shadow-2xl shadow-black/20">
                    <svg class="w-12 h-12 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                    </svg>
                </div>
            @endif
            <h1 class="text-4xl font-extrabold text-white mb-4 tracking-tight">{{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</h1>
            <p class="text-sky-200 text-lg max-w-md mx-auto leading-relaxed">Sistem presensi cerdas berbasis QR Code untuk mengelola kehadiran siswa dengan mudah dan akurat.</p>
            
            <!-- Feature chips -->
            <div class="flex flex-wrap justify-center gap-2 mt-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 backdrop-blur border border-white/15 rounded-full text-xs font-semibold text-sky-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01"/></svg>
                    QR Scan
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 backdrop-blur border border-white/15 rounded-full text-xs font-semibold text-sky-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Otomatis
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 backdrop-blur border border-white/15 rounded-full text-xs font-semibold text-sky-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Notifikasi Telegram
                </span>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 relative login-right">
        <!-- Mobile Logo -->
        <div class="absolute top-8 left-8 lg:hidden flex items-center space-x-2">
            @if(isset($globalLembaga) && $globalLembaga->logo)
                <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo" class="w-9 h-9 object-contain bg-white rounded-xl p-1 shadow-md border border-sky-100">
            @else
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-50 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-300/40">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                    </svg>
                </div>
            @endif
            <span class="font-extrabold text-slate-800">{{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara' }}</span>
        </div>

        <div class="w-full max-w-md">
            <div class="mb-8 text-center lg:text-left">
                <h2 class="text-3xl font-extrabold text-slate-800 mb-2">Selamat Datang 👋</h2>
                <p class="text-slate-500 text-sm font-medium">Masuk untuk mengelola sistem presensi sekolah.</p>
            </div>

            @if (session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl shadow-sm text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl shadow-sm text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl shadow-sm">
                    <ul class="list-disc pl-4 text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ role: 'admin' }">
                @csrf

                <!-- Pilihan Role -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Masuk sebagai</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="admin" x-model="role" class="peer sr-only" checked>
                            <div class="flex items-center justify-center gap-1.5 py-2.5 px-2 rounded-xl border-2 border-sky-200 bg-white/80 transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:shadow-md shadow-sm hover:border-sky-300 text-center">
                                <svg class="w-4 h-4 text-slate-400 peer-checked:text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-xs sm:text-sm font-bold text-slate-700">Admin</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="guru" x-model="role" class="peer sr-only">
                            <div class="flex items-center justify-center gap-1.5 py-2.5 px-2 rounded-xl border-2 border-sky-200 bg-white/80 transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:shadow-md shadow-sm hover:border-sky-300 text-center">
                                <svg class="w-4 h-4 text-slate-400 peer-checked:text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-xs sm:text-sm font-bold text-slate-700">Guru</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="siswa" x-model="role" class="peer sr-only">
                            <div class="flex items-center justify-center gap-1.5 py-2.5 px-2 rounded-xl border-2 border-sky-200 bg-white/80 transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:shadow-md shadow-sm hover:border-sky-300 text-center">
                                <svg class="w-4 h-4 text-slate-400 peer-checked:text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <span class="text-xs sm:text-sm font-bold text-slate-700">Siswa</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-bold text-slate-700 mb-1.5">
                        <span x-text="role === 'admin' ? 'Username Admin' : (role === 'guru' ? 'NIP / Username Guru' : 'NIS / NISN Siswa')">Username</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" id="username" class="pl-11 block w-full bg-white/80 backdrop-blur-sm border-sky-200 rounded-xl shadow-sm focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 sm:text-sm py-3 border transition-all @error('username') border-rose-400 bg-rose-50 @enderror" :placeholder="role === 'admin' ? 'Masukkan username admin' : (role === 'guru' ? 'Masukkan NIP atau username' : 'Masukkan NIS atau NISN siswa')" required autofocus>
                    </div>
                    <p x-show="role === 'siswa'" class="text-xs text-sky-600 font-semibold mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Password awal bawaan siswa: <span class="bg-white border border-sky-200 px-1.5 py-0.5 rounded text-sky-700 font-mono">siswa123</span>
                    </p>
                </div>


                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" class="pl-11 block w-full bg-white/80 backdrop-blur-sm border-sky-200 rounded-xl shadow-sm focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 sm:text-sm py-3 border transition-all @error('password') border-rose-400 bg-rose-50 @enderror" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-sky-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600 font-medium">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-sky-300/40 text-sm font-bold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-all duration-200 hover:-translate-y-0.5">
                        Masuk Sekarang
                    </button>
                </div>

                <div class="pt-2 text-center">
                    <a href="{{ route('siswa.login') }}" class="inline-flex items-center justify-center w-full gap-2 text-xs font-bold text-sky-700 hover:text-sky-800 bg-sky-50 hover:bg-sky-100/80 px-4 py-2.5 rounded-xl border border-sky-200 transition-all">
                        <span>👨‍👩‍👧</span>
                        <span>Portal Siswa & Wali Murid (Klik di Sini)</span>
                    </a>
                </div>
            </form>
            
            <div class="mt-8 text-center">
                <p class="text-xs text-slate-400 font-medium">&copy; {{ date('Y') }} {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }} &mdash; Made with <span class="text-rose-400">❤️</span> by <span class="font-bold text-sky-600">iyank</span></p>
            </div>
        </div>
    </div>
    <script>
        // Segarkan halaman jika dibuka dari history/back button (bfcache) agar token CSRF selalu baru
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
