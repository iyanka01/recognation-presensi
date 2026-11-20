<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Presensi Resmi Berpindah Alamat - Layanan Terpadu Satu Pintu</title>
    
    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .font-mono-num {
            font-family: 'Space Grotesk', monospace;
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Animasi Karakter Tarik Tambang Berirama */
        @keyframes pullRhythm {
            0%, 100% {
                transform: translateX(0px) rotate(0deg);
            }
            35% {
                transform: translateX(-16px) rotate(-6deg) scaleX(0.96);
            }
            50% {
                transform: translateX(-19px) rotate(-8deg);
            }
            75% {
                transform: translateX(-6px) rotate(-2deg);
            }
        }

        .animate-puller {
            animation: pullRhythm 2.8s cubic-bezier(0.45, 0, 0.55, 1) infinite;
            transform-origin: bottom center;
        }

        /* Animasi Gerbang Mendekat Mengikuti Tarikan */
        @keyframes portalHeave {
            0%, 100% {
                transform: translateX(0px) scale(0.98);
            }
            35% {
                transform: translateX(-24px) scale(1.02);
            }
            50% {
                transform: translateX(-28px) scale(1.03);
            }
            75% {
                transform: translateX(-8px) scale(0.99);
            }
        }

        .animate-portal {
            animation: portalHeave 2.8s cubic-bezier(0.45, 0, 0.55, 1) infinite;
            transform-origin: center center;
        }

        /* Gelombang Tali Tambang Bercahaya */
        @keyframes ropeWave {
            0%, 100% {
                d: path("M 80,85 Q 160,95 240,85");
            }
            35% {
                d: path("M 80,85 Q 160,78 240,85");
            }
            50% {
                d: path("M 80,85 Q 160,75 240,85");
            }
            75% {
                d: path("M 80,85 Q 160,90 240,85");
            }
        }

        .animated-rope {
            animation: ropeWave 2.8s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }

        /* Partikel Berkilau (Sparkles) */
        @keyframes sparkleFloat {
            0% {
                opacity: 0;
                transform: translateY(0px) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: translateY(-20px) scale(1.2);
            }
            100% {
                opacity: 0;
                transform: translateY(-40px) scale(0.4);
            }
        }

        .sparkle-1 { animation: sparkleFloat 2.4s ease-in-out infinite; }
        .sparkle-2 { animation: sparkleFloat 2.8s ease-in-out 0.8s infinite; }
        .sparkle-3 { animation: sparkleFloat 2.1s ease-in-out 1.4s infinite; }
        .sparkle-4 { animation: sparkleFloat 3s ease-in-out 0.4s infinite; }

        /* Ambient Glow Pulse */
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }

        .animate-glow {
            animation: pulseGlow 5s ease-in-out infinite;
        }

        /* Progress Bar Countdown (12 detik) */
        @keyframes shrinkTimer {
            from { width: 100%; }
            to { width: 0%; }
        }

        .progress-bar-active {
            animation: shrinkTimer 12s linear forwards;
        }

        .progress-bar-paused {
            animation-play-state: paused !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center relative overflow-x-hidden p-4 sm:p-6 selection:bg-emerald-500 selection:text-white">

    <!-- Ambient Neon Glow Spheres -->
    <div class="fixed top-10 -left-28 w-96 h-96 bg-emerald-500/15 rounded-full blur-[100px] pointer-events-none animate-glow"></div>
    <div class="fixed bottom-10 -right-28 w-96 h-96 bg-teal-500/15 rounded-full blur-[100px] pointer-events-none animate-glow" style="animation-delay: 2.5s;"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-indigo-950/20 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Main Card Container -->
    <div class="relative z-10 w-full max-w-xl mx-auto py-4">
        <div class="glass-card rounded-[32px] p-6 sm:p-10 shadow-2xl shadow-black/80 relative overflow-hidden text-center">

            <!-- Top Pill Badge -->
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm font-semibold tracking-wide mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>🚀 Gerbang Layanan Terpadu Satu Pintu</span>
            </div>

            <!-- ======================================================= -->
            <!-- ILUSTRASI ANIMASI TARIK TAMBANG ELEGAN (SVG MURNI) -->
            <!-- ======================================================= -->
            <div class="relative w-full max-w-[340px] sm:max-w-[400px] h-[170px] sm:h-[190px] mx-auto mb-5 rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950/90 border border-slate-800/80 p-2 overflow-hidden flex items-center justify-center shadow-inner">
                
                <!-- Partikel Bintang Mengambang (Sparkles) -->
                <div class="absolute left-1/4 top-6 text-amber-300 text-xs sparkle-1">✦</div>
                <div class="absolute left-1/2 top-4 text-emerald-300 text-sm sparkle-2">✧</div>
                <div class="absolute right-1/4 top-8 text-teal-200 text-xs sparkle-3">★</div>
                <div class="absolute left-1/3 bottom-8 text-amber-200 text-[10px] sparkle-4">✦</div>

                <!-- SVG Animasi Interaktif -->
                <svg viewBox="0 0 320 150" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Gradient Tambang Bercahaya -->
                        <linearGradient id="ropeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#10b981" />
                            <stop offset="50%" stop-color="#f59e0b" />
                            <stop offset="100%" stop-color="#38bdf8" />
                        </linearGradient>

                        <!-- Glow Filter -->
                        <filter id="neonGlow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="3" result="blur" />
                            <feMerge>
                                <feMergeNode in="blur" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>

                        <!-- Gradient Gerbang Portal Satu Pintu -->
                        <linearGradient id="portalGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#34d399" />
                            <stop offset="50%" stop-color="#059669" />
                            <stop offset="100%" stop-color="#0f172a" />
                        </linearGradient>
                    </defs>

                    <!-- Garis Lantai / Panggung -->
                    <line x1="20" y1="128" x2="300" y2="128" stroke="#334155" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 6" />

                    <!-- TALI TAMBANG BERCAHAYA -->
                    <path class="animated-rope" d="M 80,85 Q 160,95 240,85" fill="none" stroke="url(#ropeGrad)" stroke-width="5" stroke-linecap="round" filter="url(#neonGlow)" />

                    <!-- ============================================== -->
                    <!-- SISI KIRI: KARAKTER MASKOT PENARIK TAMBANG    -->
                    <!-- ============================================== -->
                    <g class="animate-puller">
                        <!-- Bayangan di tanah -->
                        <ellipse cx="68" cy="128" rx="26" ry="5" fill="#020617" opacity="0.7" />

                        <!-- Tubuh Siluet Elegan Berdiri Kuda-Kuda Tarik -->
                        <!-- Kaki Belakang (Bertumpu Kuat) -->
                        <path d="M 52,108 L 42,126 L 34,126" stroke="#10b981" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <!-- Kaki Depan (Menahan) -->
                        <path d="M 68,105 L 76,126 L 84,126" stroke="#34d399" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" fill="none" />

                        <!-- Badan Condong ke Belakang (Leaning Back) -->
                        <path d="M 50,70 L 62,106" stroke="#059669" stroke-width="14" stroke-linecap="round" fill="none" />
                        <path d="M 50,70 L 62,106" stroke="#10b981" stroke-width="10" stroke-linecap="round" fill="none" />

                        <!-- Kepala Maskot & Helm/Topi Elegan -->
                        <circle cx="44" cy="52" r="13" fill="#10b981" />
                        <circle cx="44" cy="52" r="9" fill="#047857" />
                        <!-- Sorot Mata Fokus -->
                        <ellipse cx="48" cy="50" rx="2.5" ry="3.5" fill="#ffffff" />

                        <!-- Tangan Memegang Tambang dengan Erat -->
                        <path d="M 52,72 L 72,82 L 80,85" stroke="#f59e0b" stroke-width="6" stroke-linecap="round" fill="none" />
                        <circle cx="80" cy="85" r="4.5" fill="#f59e0b" filter="url(#neonGlow)" />
                        <!-- Tangan Kiri di Belakang -->
                        <path d="M 48,74 L 64,88 L 74,86" stroke="#d97706" stroke-width="5" stroke-linecap="round" fill="none" />

                        <!-- Efek Keringat / Energi Semangat -->
                        <path d="M 32,48 Q 28,42 34,40" stroke="#38bdf8" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.8" />
                    </g>

                    <!-- ============================================== -->
                    <!-- SISI KANAN: GERBANG SATU PINTU (TERTARIK MENDEKAT) -->
                    <!-- ============================================== -->
                    <g class="animate-portal" transform="translate(0, 0)">
                        <!-- Bayangan Gerbang -->
                        <ellipse cx="255" cy="128" rx="28" ry="6" fill="#020617" opacity="0.8" />

                        <!-- Aura Cahaya Gerbang -->
                        <circle cx="255" cy="78" r="38" fill="#10b981" opacity="0.18" filter="url(#neonGlow)" />

                        <!-- Kusen Gerbang Lengkung (Arch Portal) -->
                        <path d="M 230,126 L 230,75 A 25,25 0 0,1 280,75 L 280,126 Z" fill="url(#portalGrad)" stroke="#34d399" stroke-width="3" />
                        
                        <!-- Pintu Terbuka Sedikit (Menyambut / Satu Pintu) -->
                        <path d="M 235,124 L 235,77 A 20,20 0 0,1 270,70 L 255,124 Z" fill="#064e3b" stroke="#6ee7b7" stroke-width="1.5" />
                        
                        <!-- Cahaya Terang dari Dalam Pintu (Golden Light) -->
                        <path d="M 248,76 L 273,73 L 268,124 L 246,124 Z" fill="#fef08a" opacity="0.9" filter="url(#neonGlow)" />

                        <!-- Handle / Ikon Gembok Terbuka pada Pintu -->
                        <circle cx="242" cy="98" r="2.5" fill="#ffffff" />

                        <!-- Ikatan Tali Tambang pada Kusen Gerbang -->
                        <circle cx="230" cy="85" r="5" fill="#f59e0b" filter="url(#neonGlow)" />
                        <path d="M 226,81 L 234,89 M 226,89 L 234,81" stroke="#b45309" stroke-width="2" />
                    </g>
                </svg>

                <!-- Status Caption Dinamis -->
                <div class="absolute bottom-2 inset-x-0 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-slate-900/90 border border-slate-700/80 text-[11px] text-emerald-400 font-medium tracking-wide shadow-sm">
                        <i class="ti ti-loader-2 animate-spin text-xs"></i>
                        <span>Sedang menarik seluruh layanan ke dalam satu pintu...</span>
                    </span>
                </div>
            </div>

            <!-- Judul Utama -->
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug mb-2.5">
                Layanan Presensi Resmi Berpindah Alamat
            </h1>

            <!-- Redaksi Penjelasan Sesuai Permintaan -->
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-lg mx-auto mb-6 font-normal">
                Untuk mempermudah akses dan menghadirkan layanan presensi terpadu dalam <span class="text-emerald-400 font-semibold underline decoration-emerald-500/50 underline-offset-2">satu pintu</span>, sistem presensi kini telah resmi berpindah ke alamat baru.
            </p>

            <!-- Kotak Alamat Baru yang Elegan -->
            <div class="bg-slate-900/90 border border-emerald-500/30 rounded-2xl p-3.5 sm:p-4 mb-6 shadow-inner relative group">
                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1 flex items-center justify-center gap-1">
                    <i class="ti ti-link text-emerald-400"></i>
                    <span>Alamat Akses Resmi Satu Pintu:</span>
                </p>
                <a id="targetUrl" href="https://presensi.sabiilunnaja.web.id" class="text-emerald-400 hover:text-emerald-300 font-mono-num font-bold text-sm sm:text-base break-all transition-colors flex items-center justify-center gap-2 group-hover:underline">
                    <span>https://presensi.sabiilunnaja.web.id</span>
                    <i class="ti ti-external-link text-sm shrink-0 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                </a>
            </div>

            <!-- Tombol CTA Utama -->
            <a id="btnRedirect" href="https://presensi.sabiilunnaja.web.id" class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 mb-5 cursor-pointer">
                <span>Buka Sistem Presensi Baru Sekarang</span>
                <i class="ti ti-arrow-right text-base"></i>
            </a>

            <!-- Countdown Timer & Pause Control (12 Detik) -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 mb-5">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="flex items-center gap-1.5">
                        <i class="ti ti-clock text-emerald-400 text-sm"></i>
                        <span>Mengalihkan otomatis dalam:</span>
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono-num text-emerald-400 font-bold text-sm"><span id="countdown">12</span>s</span>
                        <!-- Tombol Pause / Play Countdown -->
                        <button id="btnToggleTimer" type="button" class="text-[10px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors" title="Jeda hitung mundur">
                            Jeda
                        </button>
                    </div>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-400 progress-bar-active"></div>
                </div>
            </div>

            <!-- Bookmark Note -->
            <div class="pt-4 border-t border-white/5 text-[11px] text-slate-400 leading-relaxed flex items-center justify-center gap-1.5">
                <i class="ti ti-bookmark text-emerald-400 text-xs"></i>
                <span>Harap simpan (bookmark) alamat web baru ini di browser HP Anda.</span>
            </div>

            <!-- Tombol Darurat Arsip Web Lama -->
            <div class="mt-3">
                <a href="{{ route('login', ['force' => 1]) }}" class="text-[11px] text-slate-500 hover:text-slate-400 transition-colors underline decoration-dotted">
                    Akses darurat arsip data lama
                </a>
            </div>

        </div>

        <!-- Footer -->
        <p class="text-center text-[11px] text-slate-500 mt-5">
            &copy; {{ date('Y') }} Sistem Presensi Sekolah &bull; Layanan Terpadu Satu Pintu
        </p>
    </div>

    <!-- Script Countdown & Controller (12 Detik) -->
    <script>
        const targetUrl = "https://presensi.sabiilunnaja.web.id";
        let seconds = 12;
        let isPaused = false;
        const countdownEl = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        const btnToggleTimer = document.getElementById('btnToggleTimer');

        const timer = setInterval(() => {
            if (isPaused) return;

            seconds--;
            if (countdownEl) {
                countdownEl.textContent = seconds;
            }
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = targetUrl;
            }
        }, 1000);

        // Fitur Jeda / Lanjut Hitung Mundur
        if (btnToggleTimer) {
            btnToggleTimer.addEventListener('click', () => {
                isPaused = !isPaused;
                if (isPaused) {
                    btnToggleTimer.textContent = "Lanjut";
                    btnToggleTimer.classList.add('bg-emerald-950', 'text-emerald-300', 'border-emerald-600');
                    if (progressBar) progressBar.classList.add('progress-bar-paused');
                } else {
                    btnToggleTimer.textContent = "Jeda";
                    btnToggleTimer.classList.remove('bg-emerald-950', 'text-emerald-300', 'border-emerald-600');
                    if (progressBar) progressBar.classList.remove('progress-bar-paused');
                }
            });
        }
    </script>
</body>
</html>
