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
                {{-- Menu Khusus Guru --}}
                @include('layouts.partials.sidebar.menu-guru')
            @else
                {{-- Menu Admin Modular per Kategori --}}
                @include('layouts.partials.sidebar.menu-utama')
                @include('layouts.partials.sidebar.menu-master')
                @include('layouts.partials.sidebar.menu-laporan')
                @include('layouts.partials.sidebar.menu-pengaturan')
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
