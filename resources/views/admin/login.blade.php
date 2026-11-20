<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</title>
    @php
        $appFavicon = (isset($globalLembaga) && $globalLembaga->logo) ? Storage::url($globalLembaga->logo) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $appFavicon }}">
    <link rel="shortcut icon" href="{{ $appFavicon }}">
    <link rel="apple-touch-icon" href="{{ $appFavicon }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef5fc 0%, #e3edf7 50%, #d8e5f3 100%);
        }
        .illustration-card {
            background: linear-gradient(145deg, #eaf2fb 0%, #dce9f8 100%);
        }
        .glass-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-10 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white relative overflow-x-hidden"
      x-data="{ showPass: false, pageLoaded: false }"
      x-init="setTimeout(() => { pageLoaded = true }, 1500)">

    <!-- INTRO SPLASH / WELCOME ANIMATION (Beberapa Detik) -->
    <div x-show="!pageLoaded"
         x-transition:leave="transition ease-in-out duration-700 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-105 pointer-events-none"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white select-none">
        
        <!-- Ambient Glowing Orbs -->
        <div class="absolute w-72 h-72 rounded-full bg-emerald-500/20 blur-3xl animate-pulse pointer-events-none"></div>
        <div class="absolute w-96 h-96 rounded-full bg-teal-500/15 blur-3xl pointer-events-none -top-10 -right-10"></div>

        <!-- Logo & Brand Showcase -->
        <div class="relative z-10 flex flex-col items-center text-center px-4">
            @php
                $activeLembagaSplash = $globalLembaga ?? $lembaga ?? \App\Models\PengaturanLembaga::first();
            @endphp

            <div class="relative mb-5">
                <!-- Pulsing Outer Ring -->
                <div class="absolute -inset-2 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 opacity-40 blur-md animate-pulse"></div>
                
                @if($activeLembagaSplash && $activeLembagaSplash->logo)
                    <img src="{{ Storage::url($activeLembagaSplash->logo) }}" 
                         alt="Logo" 
                         class="relative w-20 h-20 sm:w-24 sm:h-24 object-contain rounded-2xl bg-white p-2.5 shadow-2xl shadow-emerald-500/30 border border-white/20 animate-bounce" 
                         style="animation-duration: 2s;">
                @else
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-2xl shadow-emerald-500/30 border border-white/20 animate-bounce" style="animation-duration: 2s;">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Title & Subtitle -->
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white mb-1">
                {{ $activeLembagaSplash && $activeLembagaSplash->nama_yayasan ? $activeLembagaSplash->nama_yayasan : ($activeLembagaSplash && $activeLembagaSplash->nama_lembaga ? $activeLembagaSplash->nama_lembaga : 'Panel Administrator') }}
            </h2>
            <p class="text-xs sm:text-sm font-semibold text-emerald-400 tracking-wider uppercase mb-6 flex items-center gap-1.5">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Memuat Panel Administrasi...</span>
            </p>

            <!-- Modern Animated Progress Bar -->
            <div class="w-48 sm:w-60 h-1.5 bg-slate-800 rounded-full overflow-hidden border border-white/10 shadow-inner">
                <div class="h-full bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-500 rounded-full animate-[progress_1.4s_ease-in-out_infinite]"
                     style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Outer Card Modal Window Container -->
    <div class="w-full max-w-5xl bg-white rounded-[32px] md:rounded-[40px] shadow-2xl shadow-emerald-900/10 border border-white/60 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[640px] transition-all duration-700 transform"
         :class="pageLoaded ? 'opacity-100 scale-100 translate-y-0' : 'opacity-0 scale-95 translate-y-4'">

        <!-- LEFT SIDE: Modern Illustration & System Highlights (5 Cols) -->
        <div class="lg:col-span-6 illustration-card p-8 md:p-12 flex flex-col justify-between relative overflow-hidden">
            
            <!-- Background Decorative Soft Circles & Shapes -->
            <div class="absolute -top-16 -left-16 w-56 h-56 rounded-full bg-blue-300/30 blur-2xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full bg-indigo-300/25 blur-3xl pointer-events-none"></div>
            
            <!-- Top Tagline & Header -->
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 text-xs font-bold mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>Panel Kendali Administrator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-snug">
                    Kelola data presensi & operasional <span class="text-emerald-600">sekolah secara terpusat!</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">
                    Akses penuh master data siswa, guru, rekapitulasi jam, pengaturan lembaga, hingga laporan absensi.
                </p>
            </div>

            <!-- Modern Vector 3D-Style Illustration Composition -->
            <div class="my-8 py-4 relative z-10 flex items-center justify-center">
                <div class="relative w-full max-w-sm">

                    <!-- Floating 3D Badge 1: Real-time Scan -->
                    <div class="absolute -top-4 -left-2 glass-box rounded-2xl p-3 shadow-lg shadow-blue-900/5 flex items-center gap-2.5 animate-bounce" style="animation-duration: 4s;">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-md shadow-orange-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Presensi</p>
                            <p class="text-xs font-bold text-slate-800">QR Realtime</p>
                        </div>
                    </div>

                    <!-- Floating 3D Badge 2: Rekap Cepat -->
                    <div class="absolute -bottom-3 -right-2 glass-box rounded-2xl p-3 shadow-lg shadow-blue-900/5 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Laporan</p>
                            <p class="text-xs font-bold text-slate-800">Otomatisasi</p>
                        </div>
                    </div>

                    <!-- Vector Illustration Direct (Menyatu tanpa card putih) -->
                    <div class="relative mx-auto w-64 sm:w-72 flex items-center justify-center">
                        <img src="{{ asset('vector-oke.png') }}" alt="Ilustrasi Admin" class="w-full h-auto max-h-72 object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500">
                    </div>
                </div>
            </div>

            <!-- Bottom Institution Footer -->
            <div class="relative z-10 flex items-center gap-3 pt-2">
                @if(isset($globalLembaga) && $globalLembaga->logo)
                    <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo" class="w-8 h-8 object-contain rounded-lg bg-white p-1 shadow-sm border border-slate-200">
                @endif
                <span class="text-xs font-bold text-slate-700">
                    {{ isset($globalLembaga) && $globalLembaga->nama_yayasan ? $globalLembaga->nama_yayasan : (isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Sistem Presensi') }}
                </span>
            </div>
        </div>

        <!-- RIGHT SIDE: Clean Login Form (6 Cols) -->
        <div class="lg:col-span-6 p-8 sm:p-12 lg:p-14 flex flex-col justify-between bg-white relative">

            <!-- Top Header & Brand -->
            <div>
                <!-- Brand Logo & Header -->
                <div class="flex items-center justify-between mb-8">
                    @php
                        $activeLembaga = $globalLembaga ?? $lembaga ?? \App\Models\PengaturanLembaga::first();
                    @endphp
                    <div class="flex items-center gap-3 min-w-0">
                        @if($activeLembaga && $activeLembaga->logo)
                            <img src="{{ Storage::url($activeLembaga->logo) }}" alt="Logo Lembaga" class="w-10 h-10 object-contain rounded-xl bg-slate-50 p-1 border border-slate-200 shadow-sm shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/30 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <span class="text-base sm:text-lg font-black tracking-tight text-slate-800 truncate block">
                                {{ $activeLembaga && $activeLembaga->nama_yayasan ? $activeLembaga->nama_yayasan : ($activeLembaga && $activeLembaga->nama_lembaga ? $activeLembaga->nama_lembaga : 'Panel Administrator') }}
                            </span>
                            <span class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider block">
                                Administrator Portal
                            </span>
                        </div>
                    </div>

                    <a href="{{ url('/') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-1 shrink-0 ml-2">
                        <span class="hidden sm:inline">Portal Guru &amp; Siswa</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <!-- Greetings Title -->
                <div class="mb-7">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Hai, selamat datang kembali</h2>
                    <p class="text-xs text-slate-500 mt-1">Silakan masukkan username dan kata sandi administrator Anda.</p>
                </div>

                <!-- Alert Feedback -->
                @if (session('error'))
                    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-semibold flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Fields -->
                <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Username Field -->
                    <div>
                        <div class="relative">
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   value="{{ old('username') }}" 
                                   class="block w-full px-4 py-3.5 rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm transition-all"
                                   placeholder="Contoh: admin atau username operator" 
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" 
                                   name="password" 
                                   id="password" 
                                   class="block w-full pl-4 pr-11 py-3.5 rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm transition-all" 
                                   placeholder="Masukkan kata sandi kamu" 
                                   required>
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <svg x-show="!showPass" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPass" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-1">
                        <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 text-center">
                            Masuk
                        </button>
                    </div>

                    <!-- Remember Device -->
                    <div class="flex items-center pt-2">
                        <label class="flex items-center text-slate-600 cursor-pointer text-xs">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 font-medium">Ingat perangkat ini</span>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Footer Policy / Note -->
            <div class="pt-8 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                <p>Akses terbatas hanya untuk staf dan administrator yang berwenang.</p>
                <p class="mt-1 font-medium">&copy; {{ date('Y') }} {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</p>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
