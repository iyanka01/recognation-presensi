<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner: {{ $kegiatan->nama_kegiatan }} - {{ $pengaturan->nama_lembaga ?? 'Mayantara Presensi' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            padding: 0 !important;
            background: #000 !important;
        }
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
        }
        #reader__scan_region {
            width: 100% !important;
            min-height: 100% !important;
        }
        #reader__dashboard {
            display: none !important;
        }
        @keyframes scanSweep {
            0% { transform: translateY(-100%); opacity: 0; }
            25% { opacity: 0.9; }
            75% { opacity: 0.9; }
            100% { transform: translateY(100%); opacity: 0; }
        }
        .animate-scan-sweep {
            animation: scanSweep 2.2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased font-sans h-screen h-[100dvh] flex flex-col overflow-hidden select-none" x-data="kegiatanScanner()">

    <!-- Header Terminal Responsive Mobile & Desktop -->
    <header class="bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-3.5 sm:px-6 py-2.5 flex items-center justify-between shrink-0 z-30 shadow-md">
        <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
            <button onclick="window.history.back();" type="button" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center transition-all shrink-0 active:scale-95" title="Kembali">
                <i class="ti ti-arrow-left text-lg"></i>
            </button>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-mono font-black bg-amber-400/20 text-amber-300 border border-amber-400/30 shrink-0">
                        #{{ $kegiatan->kode_kegiatan }}
                    </span>
                    <h1 class="text-xs sm:text-sm md:text-base font-black text-white truncate tracking-tight">{{ $kegiatan->nama_kegiatan }}</h1>
                </div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium truncate mt-0.5">
                    {{ $kegiatan->sasaran_label }} &bull; {{ $kegiatan->jam_range }} WIB
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            {{-- Tombol Ganti Kamera Depan / Belakang --}}
            <button x-show="cameras.length > 1" @click="switchCamera()" type="button" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition-all text-xs font-bold flex items-center gap-1.5 active:scale-95 cursor-pointer">
                <i class="ti ti-switch-horizontal text-base text-emerald-400"></i>
                <span class="hidden sm:inline">Ganti Kamera</span>
            </button>

            {{-- Tombol Buka/Tutup Log Riwayat pada Mobile --}}
            <button @click="showLogDrawer = !showLogDrawer" type="button" class="lg:hidden px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition-all text-xs font-bold flex items-center gap-1 active:scale-95 cursor-pointer relative">
                <i class="ti ti-history text-base text-blue-400"></i>
                <span class="text-[10px] font-black bg-emerald-500 text-slate-950 px-1.5 py-0.2 rounded-full" x-text="totalHadir">{{ $totalHadir }}</span>
            </button>

            {{-- Tombol Tutup Scanner --}}
            <button onclick="window.close(); window.history.back();" class="p-1.5 sm:px-3 sm:py-1.5 bg-slate-800 hover:bg-rose-900/60 text-slate-200 hover:text-rose-200 border border-slate-700 rounded-xl transition-all text-xs font-bold flex items-center gap-1 active:scale-95 cursor-pointer">
                <i class="ti ti-x text-base"></i>
                <span class="hidden sm:inline">Tutup</span>
            </button>
        </div>
    </header>

    <!-- Main Content Area: Responsive Flex Layout -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

        <!-- Kiri / Tengah: Area Kamera Mobile-Friendly -->
        <div class="flex-1 relative bg-black flex flex-col justify-between items-center overflow-hidden p-2 sm:p-4">

            <!-- Container Kamera (Mengisi Ruang Tersedia) -->
            <div class="w-full flex-1 relative rounded-2xl sm:rounded-3xl overflow-hidden bg-black shadow-2xl flex items-center justify-center border border-slate-800/80">
                
                {{-- Element HTML5-QRCode Scanner --}}
                <div id="reader" class="w-full h-full relative bg-black flex items-center justify-center"></div>

                {{-- Indikator Laser Halus Menyapu Kamera (Tanpa Kotak) --}}
                <div x-show="isCameraActive" class="absolute inset-0 pointer-events-none overflow-hidden z-10">
                    <div class="w-full h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-scan-sweep shadow-[0_0_15px_rgba(52,211,153,0.9)]"></div>
                </div>

                {{-- Status Loading Kamera --}}
                <div x-show="!isCameraActive" class="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 text-center bg-slate-950">
                    <div class="w-12 h-12 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <p class="text-xs sm:text-sm font-bold text-slate-200">Menghubungkan sensor kamera...</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs">Izinkan akses kamera di browser Anda.</p>
                </div>

                {{-- Floating Pill Hint di atas Kamera --}}
                <div class="absolute top-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-slate-900/80 text-emerald-300 backdrop-blur-md border border-emerald-500/30 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Area Scanner Penuh &bull; Cepat &amp; Bebas Antre
                    </span>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- POPUP TOAST TERIMA KASIH (MUNCUL DI BAWAH KAMERA, NON-BLOCKING) -->
            <!-- ============================================================== -->
            <div class="w-full max-w-lg mt-2 shrink-0 min-h-[82px] z-20">
                <template x-if="toast.show">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="w-full p-3 sm:p-3.5 rounded-2xl shadow-2xl border flex items-center gap-3 backdrop-blur-md"
                         :class="toast.status === 'success' 
                            ? 'bg-emerald-950/95 border-emerald-500/60 shadow-emerald-950/50' 
                            : 'bg-rose-950/95 border-rose-500/60 shadow-rose-950/50'">

                        <!-- Icon Status -->
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold shadow-md"
                             :class="toast.status === 'success' 
                                ? 'bg-emerald-500 text-white shadow-emerald-500/30' 
                                : 'bg-rose-500 text-white shadow-rose-500/30'">
                            <i :class="toast.status === 'success' ? 'ti ti-check' : 'ti ti-alert-triangle'"></i>
                        </div>

                        <!-- Konten Pop-up: Nama & Ucapan Terima Kasih -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full"
                                      :class="toast.status === 'success' ? 'bg-emerald-500/30 text-emerald-300' : 'bg-rose-500/30 text-rose-300'"
                                      x-text="toast.status === 'success' ? 'Terima Kasih!' : 'Perhatian!'"></span>
                                <span class="text-[9px] text-slate-400 font-mono" x-text="toast.waktu"></span>
                            </div>

                            <h3 class="text-xs sm:text-sm font-black text-white truncate mt-0.5 tracking-tight" x-text="toast.nama || 'Peserta Kegiatan'"></h3>
                            <p class="text-[11px] font-semibold truncate"
                               :class="toast.status === 'success' ? 'text-emerald-300' : 'text-rose-300'"
                               x-text="toast.pesan"></p>
                        </div>

                        <!-- Badge Status -->
                        <div class="text-right shrink-0">
                            <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-black shadow-xs"
                                  :class="toast.badge === 'Terlambat' 
                                    ? 'bg-amber-400 text-slate-950' 
                                    : (toast.status === 'success' ? 'bg-emerald-400 text-slate-950' : 'bg-rose-400 text-white')"
                                  x-text="toast.badge || 'Tercatat'"></span>
                        </div>
                    </div>
                </template>

                {{-- Idle State --}}
                <template x-if="!toast.show">
                    <div class="w-full p-3 rounded-2xl bg-slate-900/60 border border-slate-800 flex items-center justify-center gap-2 text-slate-400 text-xs shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-[11px] font-medium">Scanner standby &bull; Hadapkan kartu ke kamera...</span>
                    </div>
                </template>
            </div>

        </div>

        <!-- Kanan / Drawer Mobile: Live Activity Feed -->
        <div :class="showLogDrawer ? 'fixed inset-x-0 bottom-0 top-16 z-40 bg-slate-900 flex' : 'hidden lg:flex'"
             class="w-full lg:w-96 bg-slate-900 border-t lg:border-t-0 lg:border-l border-slate-800 flex-col shrink-0">
            
            <!-- Summary Stats Card -->
            <div class="p-3.5 sm:p-4 border-b border-slate-800 bg-slate-950/70 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Kehadiran</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-2xl font-black text-white tracking-tight" x-text="totalHadir">{{ $totalHadir }}</span>
                        <span class="text-xs font-bold text-emerald-400">Siswa Masuk</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showLogDrawer = false" type="button" class="lg:hidden p-1.5 rounded-lg bg-slate-800 text-slate-300">
                        <i class="ti ti-x text-base"></i>
                    </button>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i class="ti ti-users-group text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Feed Header -->
            <div class="px-4 py-2 border-b border-slate-800 flex items-center justify-between bg-slate-900">
                <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ti ti-history text-sm text-blue-400"></i>
                    Aktivitas Terbaru
                </span>
                <span class="text-[10px] text-slate-500 font-mono">Real-time</span>
            </div>

            <!-- Feed List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <template x-for="(log, idx) in activityLogs" :key="idx">
                    <div class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700/70 flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-[11px] shrink-0 border border-emerald-500/30"
                             x-text="log.nama ? log.nama.charAt(0) : 'S'">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate" x-text="log.nama"></p>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5" x-text="log.detail"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-mono font-semibold text-slate-300 block" x-text="log.waktu"></span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded"
                                  :class="log.status === 'Terlambat' ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300'"
                                  x-text="log.status"></span>
                        </div>
                    </div>
                </template>

                <div x-show="activityLogs.length === 0" class="py-8 text-center text-slate-500 text-xs">
                    <i class="ti ti-clock text-2xl mb-1 text-slate-600 block"></i>
                    Belum ada siswa yang discan pada sesi ini.
                </div>
            </div>
        </div>
    </main>

    <!-- Beep Audio Synth & Script -->
    <script>
        function playSound(type) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                if (type === 'success') {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.setValueAtTime(1174.66, ctx.currentTime + 0.08);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.2);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {
                console.log(e);
            }
        }

        function kegiatanScanner() {
            return {
                isCameraActive: false,
                isScanning: false,
                html5QrCode: null,
                cameras: [],
                currentCameraIndex: 0,
                totalHadir: {{ $totalHadir }},
                activityLogs: [],
                showLogDrawer: false,
                
                toast: {
                    show: false,
                    status: 'success',
                    nama: '',
                    pesan: '',
                    badge: '',
                    waktu: '',
                    timer: null
                },

                init() {
                    this.initCameras();
                },

                initCameras() {
                    Html5Qrcode.getCameras().then(devices => {
                        this.cameras = devices || [];
                        if (this.cameras.length > 0) {
                            // Prioritaskan kamera belakang di HP
                            let backCameraIndex = this.cameras.findIndex(d => 
                                d.label.toLowerCase().includes('back') || 
                                d.label.toLowerCase().includes('rear') || 
                                d.label.toLowerCase().includes('environment')
                            );
                            this.currentCameraIndex = backCameraIndex !== -1 ? backCameraIndex : 0;
                            this.startCamera(this.cameras[this.currentCameraIndex].id);
                        } else {
                            this.showToast('error', 'Kamera Tidak Terdeteksi', 'Pastikan perangkat memiliki kamera aktif.', 'Gagal');
                        }
                    }).catch(err => {
                        console.error("Camera error:", err);
                        this.showToast('error', 'Izin Ditolak', 'Izinkan akses kamera di browser Anda.', 'Gagal');
                    });
                },

                startCamera(cameraId) {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().then(() => {
                            this._runScanner(cameraId);
                        }).catch(() => {
                            this._runScanner(cameraId);
                        });
                    } else {
                        this.html5QrCode = new Html5Qrcode("reader");
                        this._runScanner(cameraId);
                    }
                },

                _runScanner(cameraId) {
                    // Mobile responsive scanning: full area reading tanpa qrbox
                    const config = {
                        fps: 20,
                        disableFlip: false
                    };

                    this.html5QrCode.start(
                        cameraId,
                        config,
                        (decodedText) => this.onScanSuccess(decodedText),
                        (errorMessage) => {}
                    ).then(() => {
                        this.isCameraActive = true;
                    }).catch(err => {
                        console.error("Start camera error:", err);
                    });
                },

                switchCamera() {
                    if (this.cameras.length > 1) {
                        this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
                        this.isCameraActive = false;
                        this.startCamera(this.cameras[this.currentCameraIndex].id);
                    }
                },

                showToast(status, nama, pesan, badge) {
                    if (this.toast.timer) {
                        clearTimeout(this.toast.timer);
                    }

                    const now = new Date();
                    const waktuStr = String(now.getHours()).padStart(2, '0') + ':' + 
                                     String(now.getMinutes()).padStart(2, '0') + ':' + 
                                     String(now.getSeconds()).padStart(2, '0');

                    this.toast = {
                        show: true,
                        status: status,
                        nama: nama,
                        pesan: pesan,
                        badge: badge,
                        waktu: waktuStr,
                        timer: setTimeout(() => {
                            this.toast.show = false;
                        }, 2200)
                    };
                },

                onScanSuccess(decodedText) {
                    if (this.isScanning) return;
                    this.isScanning = true;

                    fetch('{{ route("kegiatan-khusus.scan.process", $kegiatan->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ qr_code: decodedText })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            playSound('success');
                            this.totalHadir++;

                            const userName = data.user ? data.user.nama : 'Siswa';
                            const userStatus = data.user ? data.user.status : 'Hadir';
                            const detailInfo = data.user ? data.user.detail : '';

                            this.showToast('success', userName, data.message || 'Presensi berhasil dicatat.', userStatus);

                            this.activityLogs.unshift({
                                nama: userName,
                                detail: detailInfo,
                                status: userStatus,
                                waktu: data.user ? data.user.waktu : 'Barusan'
                            });
                        } else {
                            playSound('error');
                            const userName = data.user ? data.user.nama : 'Pemberitahuan';
                            this.showToast('error', userName, data.message || 'Presensi gagal.', 'Ditolak');
                        }

                        // Cooldown singkat 0.8 detik
                        setTimeout(() => {
                            this.isScanning = false;
                        }, 800);
                    })
                    .catch(err => {
                        playSound('error');
                        this.showToast('error', 'Error Jaringan', 'Gagal menghubungi server.', 'Gagal');
                        setTimeout(() => {
                            this.isScanning = false;
                        }, 1000);
                    });
                }
            };
        }
    </script>
</body>
</html>
