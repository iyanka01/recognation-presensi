<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $appTitle = (isset($globalLembaga) && $globalLembaga && $globalLembaga->nama_yayasan)
            ? $globalLembaga->nama_yayasan
            : ((isset($globalLembaga) && $globalLembaga && $globalLembaga->nama_lembaga)
                ? $globalLembaga->nama_lembaga
                : 'Sistem Presensi');
    @endphp
    <title>@yield('title', 'Dashboard') - {{ $appTitle }}</title>
    @php
        $appFavicon = (isset($globalLembaga) && $globalLembaga->logo) ? Storage::url($globalLembaga->logo) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $appFavicon }}">
    <link rel="shortcut icon" href="{{ $appFavicon }}">
    <link rel="apple-touch-icon" href="{{ $appFavicon }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Theme Initializer (Prevent FOUC) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @stack('styles')
</head>
<body class="text-slate-800 dark:text-slate-100 antialiased font-sans">

    <!-- Sidebar & Main Container -->
    <div x-data="{ 
            sidebarOpen: true,
            darkMode: document.documentElement.classList.contains('dark'),
            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            }
        }" 
        class="flex h-screen overflow-hidden relative z-10 bg-slate-50 dark:bg-slate-950">
        
        <!-- ======================== SIDEBAR ======================== -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-[76px]'"
               class="sidebar-bg flex-shrink-0 transition-all duration-300 ease-in-out flex flex-col relative z-20">

            <!-- Sidebar Header / Logo -->
            <div class="h-[70px] flex items-center px-4 overflow-hidden border-b border-slate-200 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-3 min-w-0 w-full">
                    <!-- Logo -->
                    @if(isset($globalLembaga) && $globalLembaga && $globalLembaga->logo)
                        <img src="{{ Storage::url($globalLembaga->logo) }}" alt="Logo"
                             class="w-10 h-10 rounded-[7px] object-contain bg-white dark:bg-slate-800 p-1 shrink-0 border border-slate-200 dark:border-slate-700">
                    @else
                        <div class="w-10 h-10 bg-blue-600 rounded-[7px] flex items-center justify-center shrink-0 text-white font-bold text-lg shadow-sm">
                            M
                        </div>
                    @endif
                    <!-- Name -->
                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                        <p class="font-bold text-sm leading-tight text-slate-800 dark:text-slate-100 truncate">
                            {{ (isset($globalLembaga) && $globalLembaga && $globalLembaga->nama_yayasan) ? $globalLembaga->nama_yayasan : (isset($globalLembaga) && $globalLembaga ? $globalLembaga->nama_lembaga : 'Mayantara') }}
                        </p>
                        <p class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold tracking-wider uppercase mt-0.5">Sistem Presensi</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-hide">
            @php($isGuru = \Illuminate\Support\Facades\Auth::guard('guru')->check())

            @if($isGuru)
                {{-- Menu Khusus Guru --}}
                @include('layouts.partials.sidebar.guru.sidebar-guru')
            @else
                {{-- Menu Admin Modular per Kategori Bersih --}}
                @include('layouts.partials.sidebar.admin.utama')
                @include('layouts.partials.sidebar.admin.master')
                @include('layouts.partials.sidebar.admin.perizinan')
                @include('layouts.partials.sidebar.admin.laporan')
                @include('layouts.partials.sidebar.admin.pengaturan')
            @endif

            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-slate-200 dark:border-slate-800 shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 px-3 py-2.5 w-full rounded-[7px] transition-all duration-200 text-rose-600 dark:text-rose-400 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 font-semibold text-sm">
                        <i class="ti ti-logout text-lg shrink-0"></i>
                        <span x-show="sidebarOpen" class="text-sm font-semibold text-left">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ======================== MAIN CONTENT ======================== -->
        <div class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">

            <!-- Topbar -->
            <header class="topbar-clean h-[70px] flex items-center justify-between px-6 z-10 shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Hamburger -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="w-9 h-9 rounded-[7px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all">
                        <i class="ti ti-menu-2 text-lg"></i>
                    </button>

                    <!-- Page title chip -->
                    <div class="page-title-chip hidden sm:inline-flex">
                        <i class="ti ti-point-filled text-blue-600 text-xs"></i>
                        @yield('title', 'Dashboard')
                    </div>
                </div>

                <!-- User area -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Theme Toggle Button -->
                    <button @click="toggleDarkMode()"
                            type="button"
                            title="Ganti Mode Tampilan (Terang / Gelap)"
                            class="w-9 h-9 rounded-[7px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 flex items-center justify-center transition-all duration-200 shadow-sm cursor-pointer">
                        <i x-show="!darkMode" class="ti ti-moon text-lg"></i>
                        <i x-show="darkMode" class="ti ti-sun text-lg text-amber-400" style="display: none;"></i>
                    </button>

                    <!-- Date & Time (Bahasa Indonesia) -->
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">{{ now()->format('H:i') }} WIB</span>
                    </div>

                    <!-- Divider -->
                    <div class="hidden md:block w-px h-7 bg-slate-200 dark:bg-slate-800"></div>

                    <!-- User info + avatar with Interactive Dropdown (Setting & Keluar) -->
                    @php($topbarUser = \Illuminate\Support\Facades\Auth::guard('guru')->check() ? \Illuminate\Support\Facades\Auth::guard('guru')->user() : auth()->user())
                    @php($isGuruTopbar = \Illuminate\Support\Facades\Auth::guard('guru')->check())
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                                @click.away="userMenuOpen = false"
                                type="button"
                                class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all cursor-pointer group focus:outline-none">
                            <div class="hidden sm:block text-right">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $topbarUser->nama_lengkap ?? 'Admin' }}</p>
                                <p class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">{{ $isGuruTopbar ? 'Guru' : 'Administrator' }}</p>
                            </div>
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm uppercase shadow-sm group-hover:scale-105 transition-transform">
                                    {{ substr($topbarUser->nama_lengkap ?? 'A', 0, 1) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></span>
                            </div>
                            <i class="ti ti-chevron-down text-xs text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-transform duration-200"
                               :class="userMenuOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-black/40 border border-slate-100 dark:border-slate-800 p-2 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                             style="display: none;">
                            
                            <!-- Header Info -->
                            <div class="px-3 py-2.5">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $topbarUser->nama_lengkap ?? 'Administrator' }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $topbarUser->email ?? $topbarUser->username ?? 'admin@mayantara.local' }}</p>
                            </div>

                            <!-- Menu Options -->
                            <div class="py-1">
                                @if(!$isGuruTopbar)
                                    <a href="{{ route('pengaturan-lembaga.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <i class="ti ti-settings text-base text-slate-400"></i>
                                        <span>Pengaturan Lembaga</span>
                                    </a>
                                    <a href="{{ route('pengaturan-akun.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <i class="ti ti-user-cog text-base text-slate-400"></i>
                                        <span>Pengaturan Akun</span>
                                    </a>
                                @else
                                    <a href="{{ route('guru.profil.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <i class="ti ti-user text-base text-slate-400"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                @endif
                            </div>

                            <!-- Logout Option -->
                            <div class="pt-1">
                                <form action="{{ $isGuruTopbar ? route('guru.logout') : route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-left cursor-pointer">
                                        <i class="ti ti-logout text-base"></i>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
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

                <!-- Toast: Warning -->
                @if(session('warning'))
                    <div x-data="{ show: true }" x-show="show"
                         x-init="setTimeout(() => show = false, 6000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="toast-enter fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-white rounded-2xl shadow-xl shadow-black/10 border border-amber-100 px-4 py-3 pr-10 max-w-sm">
                        <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Peringatan</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ session('warning') }}</p>
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
