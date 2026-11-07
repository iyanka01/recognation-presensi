<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Portal Siswa & Wali Murid') – Mayantara Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #2e1065; /* Deep Indigo / Purple sesuai gambar */
            overscroll-behavior: none;
        }
        .app-bg-gradient {
            background: linear-gradient(180deg, #3730a3 0%, #312e81 35%, #f1f5f9 35%, #f8fafc 100%);
            min-height: 100vh;
        }
        .safe-bottom { padding-bottom: calc(env(safe-area-inset-bottom, 16px) + 85px); }
        .page-content { animation: fadeUp 0.25s ease-out; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-800 antialiased app-bg-gradient selection:bg-indigo-500 selection:text-white">

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="max-w-md mx-auto px-4 pt-4 safe-bottom page-content">
        @hasSection('back_url')
        <div class="flex items-center gap-2 mb-3">
            <a href="@yield('back_url')" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span class="text-white font-bold text-sm">@yield('title')</span>
        </div>
        @endif

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs sm:text-sm font-semibold shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="flex-1">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-xs sm:text-sm font-semibold shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="flex-1">{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- ============ BOTTOM NAVIGATION BAR (5 TOMBOL DENGAN BULATAN KAMERA DI TENGAH PERSIS SEPERTI GAMBAR) ============ --}}
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="max-w-md mx-auto flex items-center justify-around h-16 px-1 relative">
            
            {{-- 1. Home --}}
            <a href="{{ route('siswa.dashboard') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('siswa.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] tracking-tight">Home</span>
            </a>

            {{-- 2. Rekap Absensi --}}
            <a href="{{ route('siswa.presensi') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('siswa.presensi') ? 'text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-[10px] tracking-tight text-center leading-none">Rekap Absensi</span>
            </a>

            {{-- 3. TOMBOL TENGAH: BULATAN SCAN KAMERA SHUTTER (PERSIS SEPERTI GAMBAR) --}}
            <div class="relative flex-1 flex justify-center items-center">
                <a href="{{ route('guru.face-scan') }}" 
                   style="width: 56px; height: 56px; margin-top: -30px; background: linear-gradient(135deg, #3730a3, #4338ca); box-shadow: 0 8px 20px rgba(55, 48, 163, 0.4); border: 4px solid #ffffff;" 
                   class="rounded-full text-white flex items-center justify-center transition-transform active:scale-90 z-50 hover:opacity-95" 
                   title="Scan Presensi / Kamera">
                    {{-- Ikon Shutter Kamera Iris (Persis Seperti di Gambar) --}}
                    <svg style="width: 24px; height: 24px; color: #ffffff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="14.31" y1="8" x2="20.05" y2="17.94"></line>
                        <line x1="9.69" y1="8" x2="21.17" y2="8"></line>
                        <line x1="7.38" y1="12" x2="13.12" y2="2.06"></line>
                        <line x1="9.69" y1="16" x2="3.95" y2="6.06"></line>
                        <line x1="14.31" y1="16" x2="2.83" y2="16"></line>
                        <line x1="16.62" y1="12" x2="10.88" y2="21.94"></line>
                    </svg>
                </a>
            </div>


            {{-- 4. Kartu Nama --}}
            <a href="{{ route('siswa.kartu-nama') }}" target="_blank" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('siswa.kartu-nama') ? 'text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
                <span class="text-[10px] tracking-tight text-center leading-none">Kartu Nama</span>
            </a>

            {{-- 5. Akun --}}
            <a href="{{ route('siswa.akun') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('siswa.akun') ? 'text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-[10px] tracking-tight">Akun</span>
            </a>

        </div>
    </nav>

    @stack('scripts')
</body>
</html>
