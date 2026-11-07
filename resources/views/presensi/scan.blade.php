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
        
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 1.5rem !important;
        }

        /* Laser scanning line keyframes */
        @keyframes laser {
            0% { top: 20%; opacity: 0.3; }
            50% { top: 80%; opacity: 1; }
            100% { top: 20%; opacity: 0.3; }
        }
        
        .animate-laser {
            animation: laser 3.5s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-sky-50 text-slate-800 antialiased font-sans h-screen flex flex-col overflow-hidden" x-data="qrScanner()">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-sky-100 px-6 py-4 flex items-center justify-between shrink-0 z-20 relative shadow-sm">
        <div class="flex items-center gap-4">
            @if(isset($pengaturan) && $pengaturan->logo)
                <img src="{{ Storage::url($pengaturan->logo) }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-lg p-1 shadow-sm border border-sky-100">
            @else
                <div class="w-12 h-12 rounded-2xl bg-sky-500 flex items-center justify-center text-white shadow-lg shadow-sky-500/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-black text-sky-950 tracking-wide">{{ isset($pengaturan) && $pengaturan->nama_lembaga ? $pengaturan->nama_lembaga : 'Mayantara Terminal' }}</h1>
                <p class="text-xs text-sky-600 font-bold tracking-widest uppercase mt-0.5">Terminal Kehadiran Otomatis</p>
            </div>
        </div>
        <button onclick="window.close()" class="px-5 py-2.5 bg-sky-50 hover:bg-rose-50 text-sky-700 hover:text-rose-600 border border-sky-200 hover:border-rose-200 rounded-xl transition-all font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            Tutup Scanner
        </button>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">
        
        <!-- Video Section (Left/Top) -->
        <div class="flex-1 lg:flex-[3] relative bg-sky-100/30 flex flex-col justify-center items-center overflow-hidden border-b lg:border-b-0 lg:border-r border-sky-200 p-4 lg:p-8">
            <!-- decorative background glows -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-sky-300/30 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-300/30 rounded-full blur-[100px] pointer-events-none"></div>
            
            <!-- Target Box Wrapper (Styling) -->
            <div class="relative w-full max-w-2xl aspect-[4/3] rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(14,165,233,0.15)] ring-4 ring-white z-10 flex flex-col bg-slate-900">
                <div id="reader" class="w-full h-full flex-1 relative bg-slate-950"></div>
                
                <!-- Laser Line and Target Corners (Only show when camera is active and scanStatus is null) -->
                <div x-show="isCameraActive && !scanStatus" class="absolute inset-0 z-20 pointer-events-none flex items-center justify-center">
                    <!-- Scanner Laser Line -->
                    <div class="absolute w-[60%] h-0.5 bg-gradient-to-r from-transparent via-sky-400 to-transparent animate-laser shadow-[0_0_10px_rgba(56,189,248,0.8)]"></div>
                    
                    <!-- Brackets -->
                    <div class="w-[60%] aspect-square max-w-[280px] relative border-2 border-transparent">
                        <!-- Top Left Corner -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-sky-400 rounded-tl-xl"></div>
                        <!-- Top Right Corner -->
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-sky-400 rounded-tr-xl"></div>
                        <!-- Bottom Left Corner -->
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-sky-400 rounded-bl-xl"></div>
                        <!-- Bottom Right Corner -->
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-sky-400 rounded-br-xl"></div>
                    </div>
                </div>

                <!-- Camera Loading / Error State -->
                <div x-show="!isCameraActive" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-6 text-center z-20">
                    <template x-if="isLoadingCameras">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 border-4 border-sky-400 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-sm font-semibold text-sky-200">Mengakses perangkat kamera...</p>
                        </div>
                    </template>
                    <template x-if="!isLoadingCameras && errorMessage">
                        <div class="flex flex-col items-center max-w-md p-4">
                            <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2">Akses Kamera Gagal</h4>
                            <p class="text-xs text-slate-400 mb-6 leading-relaxed" x-text="errorMessage"></p>
                            <button @click="startScanner()" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-500/20 transition-all">
                                Coba Hubungkan Kembali
                            </button>
                        </div>
                    </template>
                </div>
                
                <!-- Status Overlay (Success/Error) -->
                <div x-show="scanStatus" 
                     x-transition.opacity.duration.300ms
                     class="absolute inset-0 flex items-center justify-center bg-white/95 backdrop-blur-md z-30"
                     style="display: none;">
                    <div class="text-center p-8 transform transition-all w-full max-w-md" :class="scanStatus ? 'scale-100' : 'scale-90'">
                        <template x-if="scanStatus === 'success'">
                            <div class="flex flex-col items-center">
                                <div class="w-24 h-24 bg-emerald-100 border border-emerald-200 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-emerald-500/20">
                                    <svg class="w-12 h-12 text-emerald-500 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-800 mb-2 leading-tight" x-text="scanMessage"></h3>
                                <p class="text-emerald-600 font-bold tracking-wide uppercase text-sm">Berhasil Dicatat</p>
                            </div>
                        </template>
                        <template x-if="scanStatus === 'error'">
                            <div class="flex flex-col items-center">
                                <div class="w-24 h-24 bg-rose-100 border border-rose-200 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-rose-500/20">
                                    <svg class="w-12 h-12 text-rose-500 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight" x-text="scanMessage"></h3>
                                <p class="text-rose-600 font-bold tracking-wide uppercase text-sm">Gagal / Ditolak</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- Camera Selector & Controls -->
            <div x-show="cameras.length > 0" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 max-w-sm w-[90%] px-4">
                <div class="bg-white/90 backdrop-blur-md rounded-2xl border border-sky-100 p-2 shadow-xl flex items-center gap-2 w-full">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
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
        <div class="flex-1 lg:flex-[2] bg-white flex flex-col relative z-10 max-h-[40vh] lg:max-h-none shadow-[-10px_0_30px_rgba(0,0,0,0.02)]">
            <div class="p-6 border-b border-sky-100 bg-white/95 backdrop-blur-md shrink-0 flex items-center justify-between sticky top-0 z-20">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600 border border-sky-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Aktivitas Terkini
                </h3>
                <span class="px-3 py-1 rounded-full bg-sky-50 border border-sky-200 text-xs font-bold text-sky-700 tracking-wider uppercase" x-text="totalEntries + ' Entri Hari Ini'"></span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-4 custom-scrollbar scroll-smooth bg-slate-50/50">
                <template x-for="(log, index) in recentLogs" :key="index">
                    <div class="flex items-center gap-4 p-4 rounded-2xl border bg-white transition-all hover:shadow-md"
                         :class="{
                            'border-emerald-200 shadow-sm shadow-emerald-100': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                            'border-amber-200 shadow-sm shadow-amber-100': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                            'border-rose-200 shadow-sm shadow-rose-100': log.success && log.type === 'pulang',
                            'border-slate-200 shadow-sm': !log.success
                         }">
                        
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 border"
                             :class="{
                                'bg-emerald-50 text-emerald-600 border-emerald-100': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                                'bg-amber-50 text-amber-600 border-amber-100': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                                'bg-rose-50 text-rose-600 border-rose-100': log.success && log.type === 'pulang',
                                'bg-slate-100 text-slate-500 border-slate-200': !log.success
                             }">
                             <svg x-show="log.success && log.type === 'masuk' && log.status !== 'Terlambat'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                             <svg x-show="log.success && log.type === 'masuk' && log.status === 'Terlambat'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             <svg x-show="log.success && log.type === 'pulang'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                             <svg x-show="!log.success" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-lg font-bold text-slate-800 truncate tracking-wide" x-text="log.name"></p>
                            <p class="text-sm font-semibold mt-0.5 truncate"
                               :class="{
                                  'text-emerald-600': log.success && log.type === 'masuk' && log.status !== 'Terlambat',
                                  'text-amber-600': log.success && log.type === 'masuk' && log.status === 'Terlambat',
                                  'text-rose-600': log.success && log.type === 'pulang',
                                  'text-slate-500': !log.success
                                }" x-text="log.message"></p>
                        </div>
                        
                        <div class="text-xs font-bold text-slate-500 shrink-0 text-right bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
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
                            // Prefer front/selfie camera if typical terminal, or fallback to first
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

                    this.html5QrCode.start(
                        cameraId,
                        {
                            fps: 15,
                            qrbox: (width, height) => {
                                const minSize = Math.min(width, height);
                                const qrboxSize = Math.floor(minSize * 0.65);
                                return { width: qrboxSize, height: qrboxSize };
                            }
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
                        this.scanMessage = data.message;
                        
                        const now = new Date();
                        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');
                        
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
                            this.scanStatus = 'success';
                            this.playBeep(true);
                        } else {
                            this.scanStatus = 'error';
                            this.playBeep(false);
                        }
                        
                        setTimeout(() => {
                            this.scanStatus = null;
                            this.isScanning = true;
                        }, 3000);
                    })
                    .catch(error => {
                        this.scanStatus = 'error';
                        this.scanMessage = 'Terjadi kesalahan jaringan/server.';
                        
                        const now = new Date();
                        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                        
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
                        
                        setTimeout(() => {
                            this.scanStatus = null;
                            this.isScanning = true;
                        }, 3000);
                    });
                }
            }));
        });
    </script>

</body>
</html>

