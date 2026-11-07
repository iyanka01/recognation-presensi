<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mayantara Presensi') - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        /* === Custom Scrollbar === */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* === Sidebar Gradient === */
        .sidebar-bg {
            background: linear-gradient(180deg, #0369a1 0%, #075985 40%, #0c4a6e 100%);
        }

        /* === Active Nav Item === */
        .nav-active {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.10);
        }

        /* === Page Body Background === */
        body { background-color: #f0f7ff; }

        /* === Topbar glassmorphism === */
        .topbar-glass {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(186,230,253,0.6);
        }

        /* === Page Content Area === */
        .main-content {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0f9ff 100%);
        }

        /* === Decorative blob === */
        .page-blob-1 {
            position: fixed; top: -120px; right: -120px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(56,189,248,0.12) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none; z-index: 0;
        }
        .page-blob-2 {
            position: fixed; bottom: -100px; left: 100px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(14,165,233,0.08) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none; z-index: 0;
        }

        /* === Toast notification === */
        .toast-enter { animation: toast-in 0.35s cubic-bezier(.22,.68,0,1.2) forwards; }
        @keyframes toast-in {
            from { opacity: 0; transform: translateY(16px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* === Section labels === */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: rgba(186,230,253,0.7);
            text-transform: uppercase;
        }

        /* === Topbar breadcrumb page title === */
        .page-title-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            border: 1px solid rgba(56,189,248,0.3);
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 12px; font-weight: 700;
            color: #0369a1;
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-800 antialiased font-sans">

    <!-- Background decorative blobs -->
    <div class="page-blob-1"></div>
    <div class="page-blob-2"></div>

    <!-- Sidebar & Main Container -->
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden relative z-10">
        
        <!-- ======================== SIDEBAR ======================== -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-[72px]'"
               class="sidebar-bg text-white flex-shrink-0 transition-all duration-300 ease-in-out flex flex-col relative z-20 shadow-2xl shadow-sky-900/30">

            <!-- Sidebar Header / Logo -->
            <div class="h-[70px] flex items-center px-4 overflow-hidden border-b border-white/10 shrink-0">
                <div class="flex items-center gap-3 min-w-0 w-full">
                    <!-- Logo -->
                    @if(isset($globalLembaga) && $globalLembaga && $globalLembaga->logo)
                        <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo"
                             class="w-10 h-10 rounded-xl object-contain bg-white/90 p-1 shrink-0 shadow-lg shadow-black/20 ring-2 ring-white/30">
                    @else
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 shadow-lg ring-2 ring-white/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                            </svg>
                        </div>
                    @endif
                    <!-- Name -->
                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                        <p class="font-extrabold text-sm leading-tight text-white truncate">
                            {{ isset($globalLembaga) && $globalLembaga ? $globalLembaga->nama_lembaga : 'Mayantara' }}
                        </p>
                        <p class="text-[10px] text-sky-200/80 font-medium tracking-widest uppercase mt-0.5">Sistem Presensi</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5 scrollbar-hide">
            @php($isGuru = \Illuminate\Support\Facades\Auth::guard('guru')->check())

            @if($isGuru)
                <!-- ==================== MENU GURU ==================== -->
                <div class="pb-1.5 pt-2 px-2 nav-section-label" x-show="sidebarOpen">Menu Guru</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Dashboard Guru</span>
                </a>

                <a href="{{ route('absensi-mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('absensi-mapel.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Absensi Mata Pelajaran</span>
                </a>

                <a href="{{ route('guru.jadwal.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.jadwal.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm5-5h4"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Jadwal Mengajar</span>
                </a>

                <a href="{{ route('monitoring-kbm.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('monitoring-kbm.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Monitoring KBM Live</span>
                </a>

                <a href="{{ route('rekap-mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('rekap-mapel.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Absen Mapel</span>
                </a>

                <a href="{{ route('guru.izin.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.izin.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengajuan Izin</span>
                </a>

                <a href="{{ route('guru.informasi.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.informasi.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Informasi Sekolah</span>
                </a>
            @else
                <!-- ==================== MENU ADMIN ==================== -->

                <!-- Main -->
                <div class="pb-1.5 pt-2 px-2 nav-section-label" x-show="sidebarOpen">Utama</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Dashboard</span>
                </a>

                <a href="{{ route('presensi.scan') }}" target="_blank"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('presensi.scan') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Scan QR Presensi</span>
                    <span x-show="sidebarOpen" class="ml-auto text-[9px] font-bold bg-white/20 text-white px-1.5 py-0.5 rounded-md tracking-wide">LIVE</span>
                </a>

                <a href="{{ route('presensi.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('presensi.index') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Log Presensi</span>
                </a>

                <a href="{{ route('jam-absensi.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('jam-absensi.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Jam Absensi</span>
                </a>

                <!-- Data Master -->
                <div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Data Master</div>

                <a href="{{ route('siswa.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('siswa.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Data Siswa</span>
                </a>

                <a href="{{ route('kelas.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('kelas.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Data Kelas</span>
                </a>

                <a href="{{ route('guru.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Data Guru</span>
                </a>

                <a href="{{ route('mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('mapel.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Mata Pelajaran</span>
                </a>

                <a href="{{ route('jadwal.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('jadwal.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm5-5h4"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Jadwal Pelajaran</span>
                </a>

                <a href="{{ route('cetak-kartu.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('cetak-kartu.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Cetak Kartu ID</span>
                </a>

                <a href="{{ route('hari-libur.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('hari-libur.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Hari Libur</span>
                </a>

                <a href="{{ route('izin.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('izin.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Input Perizinan</span>
                </a>

                <a href="{{ route('pengumuman.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengumuman.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengumuman</span>
                </a>

                <!-- Laporan -->
                <div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Laporan &amp; Monitoring</div>

                <a href="{{ route('monitoring-kbm.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('monitoring-kbm.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Monitoring KBM Live</span>
                </a>

                <a href="{{ route('laporan.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Laporan Presensi</span>
                </a>

                <a href="{{ route('rekap-mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('rekap-mapel.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Absen Mapel</span>
                </a>

                <a href="{{ route('rekap-jam-guru.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('rekap-jam-guru.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Jam Mengajar</span>
                </a>

                <!-- Pengaturan -->
                <div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Pengaturan</div>

                <a href="{{ route('pengaturan-lembaga.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengaturan-lembaga.*') || request()->routeIs('pengaturan-akun.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengaturan</span>
                </a>

                <a href="{{ route('backup.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('backup.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Backup &amp; Restore</span>
                </a>
            @endif

            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-white/10 shrink-0">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="nav-item flex items-center gap-3 px-3 py-2.5 w-full rounded-xl transition-all duration-200 text-rose-300 hover:text-white hover:bg-rose-500/30">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span x-show="sidebarOpen" class="text-sm font-semibold text-left">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ======================== MAIN CONTENT ======================== -->
        <div class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">

            <!-- Topbar -->
            <header class="topbar-glass h-[70px] flex items-center justify-between px-5 z-10 shrink-0 shadow-sm shadow-sky-100/50">
                <div class="flex items-center gap-4">
                    <!-- Hamburger -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="w-9 h-9 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 flex items-center justify-center transition-all duration-200 hover:shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page title chip -->
                    <div class="page-title-chip hidden sm:inline-flex">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        @yield('title', 'Dashboard')
                    </div>
                </div>

                <!-- User area -->
                <div class="flex items-center gap-3">
                    <!-- Date & Time -->
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-xs font-bold text-sky-700">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ now()->format('H:i') }} WIB</span>
                    </div>

                    <!-- Divider -->
                    <div class="hidden md:block w-px h-8 bg-sky-200"></div>

                    <!-- User info + avatar -->
                    @php($topbarUser = \Illuminate\Support\Facades\Auth::guard('guru')->check() ? \Illuminate\Support\Facades\Auth::guard('guru')->user() : auth()->user())
                    @php($isGuruTopbar = \Illuminate\Support\Facades\Auth::guard('guru')->check())
                    <div class="flex items-center gap-2.5">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-bold text-slate-700 leading-tight">{{ $topbarUser->nama_lengkap ?? 'Admin' }}</p>
                            <p class="text-[10px] font-semibold text-sky-500 uppercase tracking-widest">{{ $isGuruTopbar ? 'Guru' : 'Administrator' }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-sky-300/40 uppercase ring-2 ring-white">
                            {{ substr($topbarUser->nama_lengkap ?? 'A', 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="main-content flex-1 overflow-y-auto p-5 lg:p-6 relative">

                <!-- Toast: Success -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show"
                         x-init="setTimeout(() => show = false, 4000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="toast-enter fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-white rounded-2xl shadow-xl shadow-black/10 border border-emerald-100 px-4 py-3 pr-10 max-w-sm">
                        <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Berhasil!</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false"
                                class="absolute top-2 right-2 w-6 h-6 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Toast: Error -->
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         x-init="setTimeout(() => show = false, 4000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="toast-enter fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-white rounded-2xl shadow-xl shadow-black/10 border border-rose-100 px-4 py-3 pr-10 max-w-sm">
                        <div class="w-9 h-9 bg-rose-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Gagal!</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false"
                                class="absolute top-2 right-2 w-6 h-6 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('modals')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('telegramModal', (initialChatId = '') => ({
                open: false,
                loading: false,
                updates: [],
                selectedId: '',
                chatId: initialChatId,
                openModal() {
                    this.open = true;
                    this.fetchUpdates();
                },
                fetchUpdates() {
                    this.loading = true;
                    fetch('{{ route("telegram.getUpdates") }}')
                        .then(res => res.json())
                        .then(data => {
                            this.loading = false;
                            if (data.success && data.data.length > 0) {
                                this.chatId = data.data[0].chat_id;
                                this.resultMessage = 'Berhasil mendapatkan data terbaru!';
                            } else {
                                this.resultMessage = 'Belum ada pesan baru di Telegram Bot.';
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            this.resultMessage = 'Terjadi kesalahan jaringan.';
                        });
                },
                applySelection() {
                    if (this.chatId) { this.open = false; }
                }
            }));

            Alpine.data('telegramSyncBulk', (entityType) => ({
                syncModalOpen: false,
                syncLoading: false,
                syncResultMessage: '',
                openSyncModal() {
                    this.syncModalOpen = true;
                    this.syncResultMessage = '';
                },
                sync() {
                    this.syncLoading = true;
                    this.syncResultMessage = '';
                    fetch('{{ route("telegram.syncBulk") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ type: entityType })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.syncLoading = false;
                        if(data.success) {
                            this.syncResultMessage = data.message;
                            setTimeout(() => location.reload(), 2000);
                        }
                    })
                    .catch(err => {
                        this.syncLoading = false;
                        this.syncResultMessage = 'Terjadi kesalahan jaringan.';
                    });
                }
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>
