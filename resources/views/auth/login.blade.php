<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ (isset($globalLembaga) && $globalLembaga->nama_yayasan) ? $globalLembaga->nama_yayasan : (isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi') }}</title>
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
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Presensi">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
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
                    {{ (isset($globalLembaga) && $globalLembaga->nama_yayasan) ? $globalLembaga->nama_yayasan : (isset($globalLembaga) && $globalLembaga->nama_lembaga ? $globalLembaga->nama_lembaga : 'Mayantara Presensi') }}
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
                    <input type="hidden" name="device_id" id="login_device_id" value="">
                    <input type="hidden" name="device_name" id="login_device_name" value="">

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

    <!-- ==================== PWA INSTALL PROMPT / BANNER ==================== -->
    <!-- Android Install Banner -->
    <div id="pwa-android-banner" class="fixed bottom-4 inset-x-4 max-w-md mx-auto bg-slate-900/95 text-white p-4 rounded-2xl shadow-2xl border border-teal-500/30 z-50 backdrop-blur-md hidden transition-all duration-300">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-teal-600/20 border border-teal-500/40 p-2 flex items-center justify-center shrink-0">
                <i class="ti ti-download text-teal-400 text-2xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs font-bold text-white">Pasang Aplikasi Presensi</h4>
                <p class="text-[11px] text-slate-300 mt-0.5 leading-snug">Akses lebih cepat & terkunci langsung di layar utama HP Anda.</p>
            </div>
            <button id="btn-pwa-dismiss" type="button" class="text-slate-400 hover:text-white p-1">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>
        <div class="mt-3 flex gap-2">
            <button id="btn-pwa-install" type="button" class="flex-1 py-2 px-3 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="ti ti-device-mobile"></i>
                <span>Tambahkan ke Layar Utama</span>
            </button>
        </div>
    </div>

    <!-- iPhone (iOS) Install Instructions Modal -->
    <div id="pwa-ios-banner" class="fixed bottom-4 inset-x-4 max-w-md mx-auto bg-slate-900/95 text-white p-4 rounded-2xl shadow-2xl border border-teal-500/30 z-50 backdrop-blur-md hidden transition-all duration-300">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-white/10 p-2 flex items-center justify-center text-teal-400 shrink-0">
                    <i class="ti ti-brand-apple text-xl"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-white">Pasang di iPhone (Layar Utama)</h4>
                    <p class="text-[10px] text-slate-300">Jadikan aplikasi di layar iPhone Anda</p>
                </div>
            </div>
            <button id="btn-ios-dismiss" type="button" class="text-slate-400 hover:text-white p-1">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>
        <div class="mt-3 bg-slate-800/80 rounded-xl p-2.5 text-[11px] text-slate-200 space-y-1.5 border border-slate-700/60">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-teal-500 text-[10px] font-bold flex items-center justify-center text-slate-950 shrink-0">1</span>
                <span>Ketuk ikon <b>Bagikan (Share)</b> <i class="ti ti-share-2 text-teal-300 font-bold"></i> di menu bawah Safari.</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-teal-500 text-[10px] font-bold flex items-center justify-center text-slate-950 shrink-0">2</span>
                <span>Pilih <b>"Tambahkan ke Layar Utama" (Add to Home Screen)</b>.</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-teal-500 text-[10px] font-bold flex items-center justify-center text-slate-950 shrink-0">3</span>
                <span>Ketuk <b>"Tambah" (Add)</b> di pojok kanan atas.</span>
            </div>
        </div>
    </div>

    <!-- Script PWA & Device Fingerprint -->
    <script>
        // 1. Device ID Fingerprint Generation & Persistence
        function getOrCreateDeviceId() {
            let deviceId = localStorage.getItem('presensi_device_uuid');
            if (!deviceId) {
                // Generate UUID v4 random unik
                if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                    deviceId = crypto.randomUUID();
                } else {
                    deviceId = 'dev_' + Date.now() + '_' + Math.random().toString(36).substring(2, 15);
                }
                localStorage.setItem('presensi_device_uuid', deviceId);
            }
            return deviceId;
        }

        // 2. Human-Readable Device Name Detection
        function detectDeviceName() {
            const ua = navigator.userAgent || '';
            let os = 'Unknown Device';
            let browser = 'Browser';

            if (/iPhone|iPad|iPod/i.test(ua)) {
                os = 'Apple iPhone/iOS';
            } else if (/Android/i.test(ua)) {
                const match = ua.match(/Android\s([0-9\.]+);.*?([A-Za-z0-9\s_\-]+)\sBuild/i);
                os = match && match[2] ? ('Android (' + match[2].trim() + ')') : 'Android Phone';
            } else if (/Windows NT/i.test(ua)) {
                os = 'Windows PC';
            } else if (/Macintosh|Mac OS X/i.test(ua)) {
                os = 'MacBook / MacOS';
            } else if (/Linux/i.test(ua)) {
                os = 'Linux PC';
            }

            if (/Chrome/i.test(ua) && !/Edg/i.test(ua)) browser = 'Chrome';
            else if (/Safari/i.test(ua) && !/Chrome/i.test(ua)) browser = 'Safari';
            else if (/Firefox/i.test(ua)) browser = 'Firefox';
            else if (/Edg/i.test(ua)) browser = 'Edge';

            return os + ' - ' + browser;
        }

        // Terapkan ke form saat halaman siap
        document.addEventListener('DOMContentLoaded', function() {
            const devIdEl = document.getElementById('login_device_id');
            const devNameEl = document.getElementById('login_device_name');
            if (devIdEl) devIdEl.value = getOrCreateDeviceId();
            if (devNameEl) devNameEl.value = detectDeviceName();
        });

        // 3. Register Service Worker untuk PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration note:', err);
                });
            });
        }

        // 4. PWA Installation Prompt Logic
        let deferredPrompt;
        const pwaAndroidBanner = document.getElementById('pwa-android-banner');
        const btnPwaInstall = document.getElementById('btn-pwa-install');
        const btnPwaDismiss = document.getElementById('btn-pwa-dismiss');
        const pwaIosBanner = document.getElementById('pwa-ios-banner');
        const btnIosDismiss = document.getElementById('btn-ios-dismiss');

        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        const isIos = /iPhone|iPad|iPod/i.test(navigator.userAgent) && !window.MSStream;

        // Android / Chromium
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (!sessionStorage.getItem('pwa_prompt_dismissed') && !isStandalone) {
                if (pwaAndroidBanner) pwaAndroidBanner.classList.remove('hidden');
            }
        });

        if (btnPwaInstall) {
            btnPwaInstall.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    if (pwaAndroidBanner) pwaAndroidBanner.classList.add('hidden');
                }
            });
        }

        if (btnPwaDismiss) {
            btnPwaDismiss.addEventListener('click', () => {
                sessionStorage.setItem('pwa_prompt_dismissed', '1');
                if (pwaAndroidBanner) pwaAndroidBanner.classList.add('hidden');
            });
        }

        // iOS Safari Instructions Banner
        if (isIos && !isStandalone && !sessionStorage.getItem('pwa_ios_dismissed')) {
            setTimeout(() => {
                if (pwaIosBanner) pwaIosBanner.classList.remove('hidden');
            }, 1500);
        }

        if (btnIosDismiss) {
            btnIosDismiss.addEventListener('click', () => {
                sessionStorage.setItem('pwa_ios_dismissed', '1');
                if (pwaIosBanner) pwaIosBanner.classList.add('hidden');
            });
        }

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
