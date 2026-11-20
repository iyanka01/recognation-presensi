<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Presensi Resmi Berpindah Alamat - Satu Pintu</title>
    
    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Floating Animation */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.08); }
        }

        .animate-float {
            animation: floatSlow 5s ease-in-out infinite;
        }

        .animate-glow {
            animation: pulseGlow 4s ease-in-out infinite;
        }

        /* Glassmorphism background */
        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Progress Bar Animation */
        @keyframes shrinkProgress {
            from { width: 100%; }
            to { width: 0%; }
        }

        .progress-shrink {
            animation: shrinkProgress 6s linear forwards;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center relative overflow-x-hidden p-4 sm:p-6 selection:bg-emerald-500 selection:text-white">

    <!-- Ambient Glow Backgrounds -->
    <div class="fixed top-1/4 -left-32 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none animate-glow"></div>
    <div class="fixed bottom-1/4 -right-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none animate-glow" style="animation-delay: 2s;"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Main Card Container -->
    <div class="relative z-10 w-full max-w-xl mx-auto">
        <div class="glass-card rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/60 relative overflow-hidden text-center">

            <!-- Top Corner Accent -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-emerald-500/20 rounded-full blur-xl pointer-events-none"></div>

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm font-semibold tracking-wide mb-6 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>🚀 Layanan Terpadu Satu Pintu</span>
            </div>

            <!-- Animated Icon / Portal Graphic -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 relative">
                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-teal-400 rounded-3xl blur-lg opacity-40 animate-pulse"></div>
                <div class="relative w-full h-full bg-gradient-to-tr from-slate-900 to-slate-800 border border-white/10 rounded-3xl flex items-center justify-center shadow-inner animate-float">
                    <i class="ti ti-door-enter text-4xl sm:text-5xl text-emerald-400"></i>
                </div>
            </div>

            <!-- Judul Utama -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug mb-3">
                Layanan Presensi Resmi Berpindah Alamat
            </h1>

            <!-- Redaksi Penjelasan Sesuai Permintaan -->
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-lg mx-auto mb-8 font-normal">
                Untuk mempermudah akses dan menghadirkan layanan presensi terpadu dalam <span class="text-emerald-400 font-bold">satu pintu</span>, sistem presensi kini telah resmi berpindah ke alamat baru.
            </p>

            <!-- Box URL Baru yang Menonjol -->
            <div class="bg-slate-900/80 border border-emerald-500/30 rounded-2xl p-4 sm:p-5 mb-8 shadow-inner relative group">
                <p class="text-[11px] uppercase tracking-widest text-slate-400 font-bold mb-1.5 flex items-center justify-center gap-1.5">
                    <i class="ti ti-link text-emerald-400"></i>
                    <span>Alamat Akses Resmi Baru:</span>
                </p>
                <a id="targetUrl" href="https://presensi.sabiilunnaja.web.id" class="text-emerald-400 hover:text-emerald-300 font-mono font-bold text-sm sm:text-lg break-all transition-colors flex items-center justify-center gap-2 group-hover:underline">
                    <span>https://presensi.sabiilunnaja.web.id</span>
                    <i class="ti ti-external-link text-base shrink-0 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                </a>
            </div>

            <!-- Tombol CTA Utama -->
            <a id="btnRedirect" href="https://presensi.sabiilunnaja.web.id" class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm sm:text-base rounded-2xl shadow-lg shadow-emerald-500/25 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2.5 mb-6 cursor-pointer">
                <span>Buka Sistem Presensi Baru Sekarang</span>
                <i class="ti ti-arrow-right text-lg"></i>
            </a>

            <!-- Countdown Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Mengalihkan otomatis dalam:</span>
                    <span class="font-mono text-emerald-400 font-bold text-sm"><span id="countdown">6</span> detik</span>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 progress-shrink"></div>
                </div>
            </div>

            <!-- Note / Petunjuk Simpan Link -->
            <div class="mt-8 pt-6 border-t border-white/5 text-xs text-slate-400 leading-relaxed flex items-center justify-center gap-1.5">
                <i class="ti ti-bookmark text-emerald-400 text-sm"></i>
                <span>Harap simpan (bookmark) alamat web baru ini di browser Anda.</span>
            </div>

            <!-- Tombol Darurat Akses Web Lama (Untuk Admin) -->
            <div class="mt-4">
                <a href="{{ route('login', ['force' => 1]) }}" class="text-[11px] text-slate-500 hover:text-slate-400 transition-colors underline decoration-dotted">
                    Akses darurat arsip data lama
                </a>
            </div>

        </div>

        <!-- Footer Copyright -->
        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Sistem Presensi Sekolah &bull; Layanan Terpadu Satu Pintu
        </p>
    </div>

    <!-- Countdown & Auto Redirect Script -->
    <script>
        const targetUrl = "https://presensi.sabiilunnaja.web.id";
        let seconds = 6;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            if (countdownEl) {
                countdownEl.textContent = seconds;
            }
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = targetUrl;
            }
        }, 1000);
    </script>
</body>
</html>
