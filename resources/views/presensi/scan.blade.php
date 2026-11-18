<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($pengaturan) && $pengaturan->nama_lembaga ? $pengaturan->nama_lembaga : 'Mayantara Terminal' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom mirror styling for front camera video */
        .mirror-video video {
            transform: scaleX(-1) !important;
        }
        
        #reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 14px !important;
        }
        #reader__scan_region {
            width: 100% !important;
            min-height: 100% !important;
        }
        #reader__dashboard {
            display: none !important;
        }

        /* Full sweep scanning line keyframes */
        @keyframes scanSweep {
            0% { transform: translateY(-100%); opacity: 0; }
            30% { opacity: 0.8; }
            70% { opacity: 0.8; }
            100% { transform: translateY(100%); opacity: 0; }
        }
        
        .animate-scan-sweep {
            animation: scanSweep 2.5s ease-in-out infinite;
        }

        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans h-screen flex flex-col overflow-hidden" x-data="qrScanner()">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0 z-20 relative shadow-sm">
        <div class="flex items-center gap-4">
            @if(isset($pengaturan) && $pengaturan->logo)
                <img src="{{ Storage::url($pengaturan->logo) }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-[7px] p-1 shadow-sm border border-gray-200">
            @else
                <div class="w-12 h-12 rounded-[7px] bg-blue-600 flex items-center justify-center text-white shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
            @endif
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ isset($pengaturan) && $pengaturan->nama_lembaga ? $pengaturan->nama_lembaga : 'Mayantara Terminal' }}</h1>
                <p class="text-xs text-slate-500 font-semibold tracking-wide uppercase mt-0.5">Terminal Kehadiran Otomatis</p>
            </div>
        </div>
        <button onclick="window.close()" class="px-5 py-2.5 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-gray-200 hover:border-rose-200 rounded-[7px] transition-all font-semibold flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            Tutup Scanner
        </button>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">
        
        <!-- Video Section (Left/Top) -->
        <div class="flex-1 lg:flex-[3] relative bg-slate-200/50 flex flex-col justify-center items-center overflow-hidden border-b lg:border-b-0 lg:border-r border-gray-200 p-4 lg:p-8">
            
            <!-- Target Box Wrapper (Full Area Camera) -->
            <div class="relative w-full max-w-2xl aspect-[4/3] rounded-2xl overflow-hidden shadow-2xl ring-2 ring-slate-800 bg-slate-900 flex flex-col">
                <div id="reader" class="w-full h-full flex-1 relative bg-black flex items-center justify-center"></div>
                
                <!-- Full Sweep Laser (No Framing Box) -->
                <div x-show="isCameraActive" class="absolute inset-0 pointer-events-none overflow-hidden z-10">
                    <div class="w-full h-1 bg-gradient-to-r from-transparent via-blue-400 to-transparent animate-scan-sweep shadow-[0_0_15px_rgba(59,130,246,0.9)]"></div>
                </div>

                <!-- Camera Loading / Error State -->
                <div x-show="!isCameraActive" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-6 text-center z-20">
                    <template x-if="isLoadingCameras">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-sm font-semibold text-blue-200">Mengakses perangkat kamera...</p>
                        </div>
                    </template>
                    <template x-if="!isLoadingCameras && errorMessage">
                        <div class="flex flex-col items-center max-w-md p-4">
                            <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-[7px] flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2">Akses Kamera Gagal</h4>
                            <p class="text-xs text-slate-400 mb-6 leading-relaxed" x-text="errorMessage"></p>
                            <button @click="startScanner()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] shadow-sm transition-all">
                                Coba Hubungkan Kembali
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Hint below camera -->
            <p class="text-xs text-slate-500 font-medium mt-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Arahkan QR Code Kartu ke seluruh area kamera &bull; Presensi cepat tanpa antre
            </p>

            <!-- ============================================================== -->
            <!-- POPUP TOAST TERIMA KASIH (MUNCUL DI BAWAH KAMERA, NON-BLOCKING) -->
            <!-- ============================================================== -->
            <div class="w-full max-w-2xl mt-2.5 min-h-[88px]">
                <template x-if="scanToast.show">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="w-full p-4 rounded-2xl shadow-xl border flex items-center gap-3.5 backdrop-blur-md"
                         :class="scanToast.status === 'success' 
                            ? 'bg-emerald-950/90 border-emerald-500/50 shadow-emerald-950/30' 
                            : 'bg-rose-950/90 border-rose-500/50 shadow-rose-950/30'">

                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-white shadow-md text-xl"
                             :class="scanToast.status === 'success' ? 'bg-emerald-500 shadow-emerald-500/30' : 'bg-rose-500 shadow-rose-500/30'">
                            <template x-if="scanToast.status === 'success'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                            <template x-if="scanToast.status !== 'success'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full"
                                      :class="scanToast.status === 'success' ? 'bg-emerald-500/30 text-emerald-300' : 'bg-rose-500/30 text-rose-300'"
                                      x-text="scanToast.status === 'success' ? 'Terima Kasih!' : 'Perhatian!'"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="scanToast.waktu"></span>
                            </div>
                            <h3 class="text-sm sm:text-base font-black text-white truncate mt-0.5" x-text="scanToast.name || 'Siswa / Guru'"></h3>
                            <p class="text-xs font-semibold"
                               :class="scanToast.status === 'success' ? 'text-emerald-300' : 'text-rose-300'"
                               x-text="scanToast.message"></p>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black shadow-sm"
                                  :class="scanToast.type === 'Terlambat' 
                                    ? 'bg-amber-500 text-slate-950' 
                                    : (scanToast.status === 'success' ? 'bg-emerald-400 text-slate-950' : 'bg-rose-400 text-white')"
                                  x-text="scanToast.badge || (scanToast.status === 'success' ? 'Berhasil' : 'Ditolak')"></span>
                        </div>
                    </div>
                </template>

                <template x-if="!scanToast.show">
                    <div class="w-full p-4 rounded-2xl bg-white/70 border border-slate-200/80 flex items-center justify-center gap-2.5 text-slate-600 text-xs shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="font-medium">Scanner siap. Hadapkan QR Code ke kamera untuk mencatat presensi...</span>
                    </div>
                </template>
            </div>
            
            <!-- Camera Selector & Controls -->
            <div x-show="cameras.length > 0" class="mt-3 flex items-center gap-2 max-w-sm w-full px-2">
                <div class="bg-white rounded-[7px] border border-gray-200 p-2 shadow-sm flex items-center gap-2 w-full">
                    <div class="w-8 h-8 rounded-[7px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 00-2 2z"></path></svg>
                    </div>
                    <select x-model="selectedCameraId" @change="switchCamera(selectedCameraId)" 
                            class="flex-1 text-xs font-semibold text-slate-700 bg-transparent border-0 focus:ring-0 cursor-pointer outline-none min-w-0">
                        <template x-for="device in cameras" :key="device.id">
                            <option :value="device.id" x-text="device.label" :selected="device.id === selectedCameraId"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- Logs Section (Right/Bottom) -->
        <div class="flex-1 lg:flex-[2] bg-white flex flex-col relative z-10 max-h-[40vh] lg:max-h-none border-l border-gray-100">
            <div class="p-5 border-b border-gray-200 bg-white shrink-0 flex items-center justify-between sticky top-0 z-20">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-[7px] bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Aktivitas Terkini
                </h3>
                <span class="px-3 py-1 rounded-full bg-blue-50 text-xs font-semibold text-blue-600" x-text="totalEntries + ' Entri Hari Ini'"></span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 lg:p-5 space-y-3 scroll-smooth bg-gray-50/50">
                <template x-for="(log, index) in recentLogs" :key="index">
                    <div class="flex items-center gap-4 p-4 rounded-[7px] bg-white border transition-all hover:shadow-sm"
                         :class="{
                            'border-l-4 border-l-emerald-500 border-t-gray-100 border-r-gray-100 border-b-gray-100': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                            'border-l-4 border-l-amber-500 border-t-gray-100 border-r-gray-100 border-b-gray-100': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                            'border-l-4 border-l-rose-500 border-t-gray-100 border-r-gray-100 border-b-gray-100': log.success && log.type === 'pulang',
                            'border-l-4 border-l-gray-400 border-t-gray-100 border-r-gray-100 border-b-gray-100': !log.success
                         }">
                        
                        <div class="w-12 h-12 rounded-[7px] flex items-center justify-center shrink-0"
                             :class="{
                                'bg-emerald-50 text-emerald-600': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                                'bg-amber-50 text-amber-600': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                                'bg-rose-50 text-rose-600': log.success && log.type === 'pulang',
                                'bg-gray-100 text-gray-500': !log.success
                             }">
                             <svg x-show="log.success && log.type === 'masuk' && log.status !== 'Terlambat'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                             <svg x-show="log.success && log.type === 'masuk' && log.status === 'Terlambat'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             <svg x-show="log.success && log.type === 'pulang'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                             <svg x-show="!log.success" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-base font-semibold text-slate-800 truncate" x-text="log.name"></p>
                            <p class="text-sm mt-0.5 truncate"
                               :class="{
                                  'text-emerald-600': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                                  'text-amber-600': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                                  'text-rose-600': log.success && log.type === 'pulang',
                                  'text-gray-500': !log.success
                                }" x-text="log.message"></p>
                        </div>
                        
                        <div class="text-xs font-semibold text-slate-500 shrink-0 text-right bg-gray-50 px-3 py-1.5 rounded-[7px]">
                            <span x-text="log.time"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- HTML5 QR Code Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('qrScanner', () => ({
                scanStatus: null,
                scanMessage: '',
                isScanning: true,
                html5QrCode: null,
                isCameraActive: false,
                isLoadingCameras: false,
                cameras: [],
                selectedCameraId: '',
                errorMessage: '',
                recentLogs: [],
                totalEntries: {{ $totalHariIni ?? 0 }},

                scanToast: {
                    show: false,
                    status: 'success',
                    name: '',
                    message: '',
                    badge: '',
                    type: '',
                    waktu: '',
                    timer: null
                },

                showToast(status, name, message, badge, type) {
                    if (this.scanToast.timer) {
                        clearTimeout(this.scanToast.timer);
                    }
                    const now = new Date();
                    const waktuStr = String(now.getHours()).padStart(2, '0') + ':' + 
                                     String(now.getMinutes()).padStart(2, '0') + ':' + 
                                     String(now.getSeconds()).padStart(2, '0');

                    this.scanToast = {
                        show: true,
                        status: status,
                        name: name,
                        message: message,
                        badge: badge,
                        type: type,
                        waktu: waktuStr,
                        timer: setTimeout(() => {
                            this.scanToast.show = false;
                        }, 2200)
                    };
                },

                init() {
                    this.startScanner();
                    
                    // Cleanup camera on page hide/beforeunload
                    window.addEventListener('beforeunload', () => {
                        this.stopCamera();
                    });
                },

                playBeep(isSuccess) {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioCtx.createOscillator();
                        const gainNode = audioCtx.createGain();
                        
                        oscillator.connect(gainNode);
                        gainNode.connect(audioCtx.destination);
                        
                        if (isSuccess) {
                            oscillator.type = 'sine';
                            oscillator.frequency.value = 1000;
                            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
                            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                            oscillator.start(audioCtx.currentTime);
                            oscillator.stop(audioCtx.currentTime + 0.15);
                        } else {
                            oscillator.type = 'sawtooth';
                            oscillator.frequency.value = 300;
                            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
                            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
                            oscillator.start(audioCtx.currentTime);
                            oscillator.stop(audioCtx.currentTime + 0.4);
                        }
                    } catch(e) { console.log('Audio error:', e); }
                },

                startScanner() {
                    this.isLoadingCameras = true;
                    this.errorMessage = '';
                    
                    Html5Qrcode.getCameras().then(devices => {
                        this.cameras = devices;
                        this.isLoadingCameras = false;
                        if (devices && devices.length > 0) {
                            const frontCamera = devices.find(device => 
                                device.label.toLowerCase().includes('front') || 
                                device.label.toLowerCase().includes('facing user') ||
                                device.label.toLowerCase().includes('selfie')
                            );
                            this.selectedCameraId = frontCamera ? frontCamera.id : devices[0].id;
                            this.startCamera(this.selectedCameraId);
                        } else {
                            this.errorMessage = "Tidak ada perangkat kamera yang terdeteksi. Hubungkan webcam jika menggunakan komputer desktop.";
                        }
                    }).catch(err => {
                        this.isLoadingCameras = false;
                        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                            this.errorMessage = "Kamera tidak dapat diakses di situs web tidak aman (HTTP). Harap gunakan sambungan aman HTTPS atau buka lewat localhost.";
                        } else {
                            this.errorMessage = "Izin kamera ditolak atau kamera sedang digunakan oleh aplikasi lain. Silakan periksa pengaturan izin kamera peramban Anda.";
                        }
                    });
                },

                startCamera(cameraId) {
                    if (this.html5QrCode) {
                        this.stopCamera().then(() => {
                            this._doStartCamera(cameraId);
                        });
                    } else {
                        this.html5QrCode = new Html5Qrcode("reader");
                        this._doStartCamera(cameraId);
                    }
                },

                _doStartCamera(cameraId) {
                    this.isLoadingCameras = true;
                    this.errorMessage = '';
                    
                    const cameraObj = this.cameras.find(c => c.id === cameraId);
                    const isFront = cameraObj && (
                        cameraObj.label.toLowerCase().includes('front') || 
                        cameraObj.label.toLowerCase().includes('facing user') ||
                        cameraObj.label.toLowerCase().includes('selfie')
                    );
                    
                    const readerElement = document.getElementById('reader');
                    if (isFront) {
                        readerElement.classList.add('mirror-video');
                    } else {
                        readerElement.classList.remove('mirror-video');
                    }

                    // FULL AREA SCAN TANPA QRBOX AGAR BISA MEMBACA DI SELURUH AREA KAMERA
                    this.html5QrCode.start(
                        cameraId,
                        {
                            fps: 20,
                            aspectRatio: 1.333333,
                            disableFlip: false
                        },
                        (decodedText, decodedResult) => {
                            if (!this.isScanning) return;
                            this.isScanning = false;
                            this.processScan(decodedText);
                        },
                        (errorMessage) => {
                            // Verbose scanning failures ignored
                        }
                    )
                    .then(() => {
                        this.isCameraActive = true;
                        this.isLoadingCameras = false;
                    })
                    .catch(err => {
                        this.isCameraActive = false;
                        this.isLoadingCameras = false;
                        this.errorMessage = "Gagal memulai kamera: " + err;
                    });
                },

                stopCamera() {
                    if (this.html5QrCode && this.isCameraActive) {
                        return this.html5QrCode.stop().then(() => {
                            this.isCameraActive = false;
                        });
                    }
                    return Promise.resolve();
                },

                switchCamera(cameraId) {
                    this.startCamera(cameraId);
                },

                processScan(qrCode) {
                    fetch('{{ route('presensi.process') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ qr_code: qrCode })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const now = new Date();
                        const timeString = String(now.getHours()).padStart(2, '0') + ':' + 
                                           String(now.getMinutes()).padStart(2, '0') + ':' + 
                                           String(now.getSeconds()).padStart(2, '0');
                        
                        this.recentLogs.unshift({
                            success: data.success,
                            name: data.user_name || 'Sistem',
                            message: data.message,
                            type: data.type || 'info',
                            status: data.status || '',
                            time: timeString
                        });
                        
                        if (this.recentLogs.length > 50) {
                            this.recentLogs.pop();
                        }

                        if (data.success) {
                            this.totalEntries++;
                            this.playBeep(true);
                            this.showToast('success', data.user_name || 'Siswa / Guru', data.message, data.status || (data.type === 'pulang' ? 'Pulang' : 'Hadir'), data.status);
                        } else {
                            this.playBeep(false);
                            this.showToast('error', data.user_name || 'Peringatan', data.message, 'Ditolak', 'error');
                        }
                        
                        // Cooldown singkat 0.8 detik agar tidak antre panjang
                        setTimeout(() => {
                            this.isScanning = true;
                        }, 800);
                    })
                    .catch(error => {
                        const now = new Date();
                        const timeString = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                        
                        this.recentLogs.unshift({
                            success: false,
                            name: 'Sistem Error',
                            message: 'Terjadi kesalahan jaringan/server.',
                            type: 'error',
                            status: '',
                            time: timeString
                        });
                        
                        if (this.recentLogs.length > 50) {
                            this.recentLogs.pop();
                        }

                        this.playBeep(false);
                        this.showToast('error', 'Error Jaringan', 'Terjadi kesalahan jaringan/server.', 'Gagal', 'error');
                        
                        setTimeout(() => {
                            this.isScanning = true;
                        }, 1000);
                    });
                }
            }));
        });
    </script>

</body>
</html>
