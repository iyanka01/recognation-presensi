<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Guru Panel') – Mayantara</title>
    @php
        $appFavicon = (isset($globalLembaga) && $globalLembaga->logo) ? Storage::url($globalLembaga->logo) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $appFavicon }}">
    <link rel="shortcut icon" href="{{ $appFavicon }}">
    <link rel="apple-touch-icon" href="{{ $appFavicon }}">
    {{-- Menggunakan Google Font Plus Jakarta Sans & Inter untuk tipografi yang tajam, tegas & solid --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #064e3b; /* Deep Emerald Green */
            overscroll-behavior: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .guru-bg-gradient {
            background: linear-gradient(180deg, #059669 0%, #047857 32%, #f1f5f9 32%, #f8fafc 100%);
            min-height: 100vh;
        }
        .safe-bottom { padding-bottom: calc(env(safe-area-inset-bottom, 16px) + 85px); }
        .page-content { animation: fadeUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-900 antialiased @yield('body_class', 'guru-bg-gradient') selection:bg-emerald-600 selection:text-white" style="@yield('body_style')">

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="max-w-md mx-auto px-4 pt-4 safe-bottom page-content">
        @hasSection('back_url')
        <div class="flex items-center gap-3 mb-3.5">
            <a href="@yield('back_url')" class="w-8 h-8 rounded-xl @yield('back_btn_class', 'bg-white/20 hover:bg-white/30 text-white') flex items-center justify-center transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span class="@yield('title_class', 'text-white') font-black text-base tracking-wide">@yield('title')</span>
        </div>
        @endif

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-300 rounded-2xl text-xs sm:text-sm text-emerald-900 font-bold flex items-center gap-2.5 shadow-sm animate-fade-in">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-300 rounded-2xl text-xs sm:text-sm text-rose-900 font-bold flex items-center gap-2.5 shadow-sm animate-fade-in">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- ============ BOTTOM NAVIGATION BAR GURU ============ --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 shadow-[0_-4px_25px_rgba(0,0,0,0.08)]">
        <div class="max-w-md mx-auto flex items-center justify-around h-16 px-1 relative">

            {{-- 1. Home --}}
            <a href="{{ route('dashboard') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('dashboard') ? 'text-emerald-700 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-semibold' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] tracking-tight">Home</span>
            </a>

            {{-- 2. Absensi Mapel --}}
            <a href="{{ route('absensi-mapel.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('absensi-mapel.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-semibold' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-[10px] tracking-tight text-center leading-none">Absensi</span>
            </a>

            {{-- 3. TOMBOL TENGAH: BULATAN SCAN KAMERA SHUTTER MELAYANG --}}
            <div class="relative flex-1 flex justify-center items-center">
                <a href="{{ route('guru.face-scan') }}" 
                   style="width: 56px; height: 56px; margin-top: -30px; background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 10px 22px rgba(5, 150, 105, 0.45); border: 4px solid #ffffff;" 
                   class="rounded-full text-white flex items-center justify-center transition-transform active:scale-90 z-50 hover:opacity-95" 
                   title="Scan Presensi / Kamera">
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

            {{-- 4. Rekap Mapel --}}
            <a href="{{ route('rekap-mapel.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('rekap-mapel.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-semibold' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-[10px] tracking-tight text-center leading-none">Rekap</span>
            </a>

            {{-- 5. Akun --}}
            <a href="{{ route('guru.akun') }}" class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('guru.akun*') || request()->routeIs('guru.password*') ? 'text-emerald-700 font-extrabold' : 'text-slate-400 hover:text-emerald-700 font-semibold' }} transition-colors">
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
