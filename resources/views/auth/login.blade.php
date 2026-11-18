<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .iqra-pattern {
            background-color: #0f766e;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1.5px, transparent 0), radial-gradient(rgba(255, 255, 255, 0.08) 1.5px, #0d9488 0);
            background-size: 24px 24px;
            background-position: 0 0, 12px 12px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 flex flex-col justify-between selection:bg-teal-500 selection:text-white"
      x-data="{ 
          role: '{{ old('role', 'guru') }}', 
          showPass: false 
      }">

    <!-- Top Area with Islamic Geometric Curves Header (Style 1 - IQRA Style) -->
    <div class="relative w-full">
        <!-- Curved Background Header -->
        <div class="iqra-pattern text-white pt-10 pb-28 px-4 text-center relative overflow-hidden rounded-b-[40px] md:rounded-b-[56px] shadow-lg shadow-teal-900/10">
            <!-- Decorative Light Rings -->
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Content Header -->
            <div class="relative z-10 max-w-xl mx-auto flex flex-col items-center">
                <!-- Institution Logo -->
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-3xl bg-white p-2.5 shadow-xl shadow-teal-950/20 ring-4 ring-white/20 mb-4 flex items-center justify-center transition-transform hover:scale-105 duration-300">
                    @if(isset($globalLembaga) && $globalLembaga->logo)
                        <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 flex items-center justify-center text-white">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Institution Name & Subtitle -->
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white drop-shadow-sm">
                    {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}
                </h1>
                <p class="text-teal-100 text-xs md:text-sm font-medium mt-1 max-w-sm drop-shadow-sm">
                    Portal Presensi & Informasi Akademik Terpadu
                </p>
            </div>
        </div>

        <!-- Floating Card Container -->
        <div class="max-w-md mx-auto px-4 -mt-20 relative z-20 pb-8">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/70 border border-slate-100 p-6 md:p-8">

                <!-- Alert Messages -->
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

                <!-- Modern Tab Switcher: Guru vs Siswa -->
                <div class="bg-slate-100 p-1.5 rounded-2xl flex items-center mb-6">
                    <button type="button" 
                            @click="role = 'guru'" 
                            :class="role === 'guru' ? 'bg-white text-teal-700 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200">
                        <svg class="w-4 h-4" :class="role === 'guru' ? 'text-teal-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Guru / PTK</span>
                    </button>
                    <button type="button" 
                            @click="role = 'siswa'" 
                            :class="role === 'siswa' ? 'bg-white text-teal-700 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200">
                        <svg class="w-4 h-4" :class="role === 'siswa' ? 'text-teal-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                        <span>Siswa / Santri</span>
                    </button>
                </div>

                <!-- Form Login -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Hidden Role Input synced with Alpine.js -->
                    <input type="hidden" name="role" :value="role">

                    <!-- Username / NIP / NIS -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">
                            <span x-text="role === 'guru' ? 'NIP atau Username Guru' : 'NIS / NISN Siswa'">Nomor Identitas</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   value="{{ old('username') }}"
                                   class="pl-10 block w-full bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs md:text-sm py-3 transition-all"
                                   :placeholder="role === 'guru' ? 'Masukkan NIP atau username Anda' : 'Masukkan nomor NIS atau NISN'" 
                                   required 
                                   autofocus>
                        </div>
                        <p x-show="role === 'siswa'" class="text-[11px] text-teal-700 font-semibold mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Password bawaan siswa: <span class="font-mono bg-teal-50 border border-teal-200 text-teal-800 px-1 py-0.5 rounded">siswa123</span></span>
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" 
                                   name="password" 
                                   id="password" 
                                   class="pl-10 pr-10 block w-full bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs md:text-sm py-3 transition-all" 
                                   placeholder="••••••••" 
                                   required>
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
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

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span class="ml-2 font-medium">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-teal-700/25 transition-all duration-200 hover:shadow-teal-700/40 hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <span>Masuk Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Footer Copyright -->
    <footer class="text-center py-6 px-4 text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} {{ isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi' }}</p>
    </footer>

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
