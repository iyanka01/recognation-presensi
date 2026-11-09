@extends('layouts.guru')

@section('title', 'Absensi Wajah')
@section('back_url', route('dashboard'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #video-container {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        background: #0f172a;
        border-radius: 1.5rem;
        overflow: hidden;
        max-width: 360px;
        margin: 0 auto;
    }
    #video {
        width: 100%; height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
    }
    #canvas-overlay {
        position: absolute; top: 0; left: 0;
        width: 100%; height: 100%;
        transform: scaleX(-1);
        pointer-events: none;
    }
    .face-oval {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        pointer-events: none;
    }
    .face-oval-ring {
        width: 200px; height: 240px;
        border: 3px solid rgba(255,255,255,0.4);
        border-radius: 50%;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .face-oval-ring.detected {
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34,197,94,0.25), 0 0 30px rgba(34,197,94,0.2);
        animation: pulse-oval 1.2s ease-in-out infinite;
    }
    .face-oval-ring.matched {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 6px rgba(14,165,233,0.3), 0 0 40px rgba(14,165,233,0.2);
    }
    @keyframes pulse-oval {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    .scan-line {
        position: absolute;
        left: 50%; top: 20%;
        transform: translateX(-50%);
        width: 160px; height: 2px;
        background: linear-gradient(90deg, transparent, rgba(14,165,233,0.8), transparent);
        animation: scan 2.5s linear infinite;
    }
    @keyframes scan {
        0% { top: 20%; opacity: 0.3; }
        50% { top: 75%; opacity: 1; }
        100% { top: 20%; opacity: 0.3; }
    }
    .status-chip {
        position: absolute; bottom: 12px; left: 0; right: 0;
        display: flex; justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="space-y-4">

    {{-- ======== HEADER: INFO JAM ABSENSI DARI ADMIN ======== --}}
    <div class="rounded-2xl overflow-hidden shadow-md" style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);">
        <div class="p-4">
            <p class="text-sky-100 text-xs font-semibold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="text-white font-bold text-base mt-0.5">Absensi Wajah — {{ $guru->nama_lengkap }}</h2>

            {{-- Jam Absensi dari Admin --}}
            @if($pengaturanJam)
            <div class="mt-3 bg-white/10 rounded-xl p-3">
                <p class="text-sky-200 text-[10px] font-bold uppercase tracking-wider mb-2">Jam Absensi (dari Admin)</p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                        <p class="text-white font-extrabold text-sm">{{ substr($pengaturanJam->jam_mulai, 0, 5) }}</p>
                        <p class="text-sky-200/70 text-[10px]">Mulai</p>
                    </div>
                    <div>
                        <p class="text-amber-300 font-extrabold text-sm">{{ substr($pengaturanJam->jam_terlambat, 0, 5) }}</p>
                        <p class="text-sky-200/70 text-[10px]">Terlambat</p>
                    </div>
                    <div>
                        <p class="text-rose-300 font-extrabold text-sm">{{ substr($pengaturanJam->jam_pulang, 0, 5) }}</p>
                        <p class="text-sky-200/70 text-[10px]">Pulang</p>
                    </div>
                </div>
            </div>
            @else
            <div class="mt-3 bg-amber-400/20 rounded-xl p-3">
                <p class="text-amber-200 text-xs font-semibold">⚠️ Jam absensi guru belum diatur oleh admin.</p>
            </div>
            @endif

            {{-- Status Absensi Hari Ini --}}
            <div class="grid grid-cols-2 gap-2 mt-3">
                <div class="bg-white/10 rounded-xl p-2.5 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full {{ $presensiHariIni && $presensiHariIni->jam_masuk ? 'bg-emerald-400' : 'bg-white/20' }} flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $presensiHariIni && $presensiHariIni->jam_masuk ? 'M5 13l4 4L19 7' : 'M12 6v6m0 0v6m0-6h6m-6 0H6' }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-sky-200">Masuk</p>
                        <p class="text-white font-bold text-xs">
                            @if($presensiHariIni && $presensiHariIni->jam_masuk)
                                {{ substr($presensiHariIni->jam_masuk, 0, 5) }}
                                <span class="text-emerald-300 text-[9px]">{{ $presensiHariIni->status }}</span>
                            @else
                                <span class="text-white/40 font-normal italic">Belum</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="bg-white/10 rounded-xl p-2.5 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full {{ $presensiHariIni && $presensiHariIni->jam_pulang ? 'bg-amber-400' : 'bg-white/20' }} flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $presensiHariIni && $presensiHariIni->jam_pulang ? 'M5 13l4 4L19 7' : 'M17 16l4-4m0 0l-4-4m4 4H7' }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-sky-200">Pulang</p>
                        <p class="text-white font-bold text-xs">
                            @if($presensiHariIni && $presensiHariIni->jam_pulang)
                                {{ substr($presensiHariIni->jam_pulang, 0, 5) }}
                            @else
                                <span class="text-white/40 font-normal italic">Belum</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if(isset($lockedPresensi) && $lockedPresensi && (!$presensiHariIni || !$presensiHariIni->jam_masuk))
            @php
                $tglLockedStr = \Carbon\Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
            @endphp
            <div class="mt-3 bg-rose-500/30 border border-rose-300/40 rounded-xl p-3 flex items-start gap-2.5 text-white">
                <svg class="w-5 h-5 text-rose-200 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <div class="text-xs">
                    <p class="font-bold text-white">Presensi Anda Terkunci!</p>
                    <p class="text-rose-100 text-[11px] mt-0.5">
                        Anda tidak melakukan absen pulang pada hari <strong>{{ $tglLockedStr }}</strong>. Hubungi Administrator untuk membuka kunci kehadiran Anda.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ======== STATUS PENDAFTARAN WAJAH ======== --}}
    @if($hasFace)
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-emerald-800 font-bold text-sm">Wajah Sudah Terdaftar ✓</p>
            <p class="text-emerald-600 text-xs mt-0.5">Hadapkan wajah ke kamera untuk absen otomatis</p>
        </div>
        <button onclick="startRegister()"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all">
            Update
        </button>
    </div>
    @else
    <div class="bg-rose-50 border-2 border-dashed border-rose-300 rounded-2xl p-4">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-rose-800 font-bold text-sm">Wajah Belum Terdaftar!</p>
                <p class="text-rose-600 text-xs mt-0.5">Anda harus mendaftarkan wajah sebelum bisa absen via Recognition.</p>
            </div>
        </div>
        <button onclick="startRegister()"
                class="w-full py-3 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            📸 Daftarkan Wajah Sekarang
        </button>
    </div>
    @endif

    {{-- ======== KAMERA ABSENSI ======== --}}
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Scan Wajah untuk Absensi</h3>

        {{-- Loading --}}
        <div id="loading-model" class="bg-white/90 border border-sky-100 rounded-2xl p-6 text-center shadow-sm">
            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-sky-50 flex items-center justify-center">
                <svg class="w-7 h-7 text-sky-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            <p class="text-sm font-bold text-slate-700">Memuat AI Pengenalan Wajah</p>
            <p class="text-xs text-slate-400 mt-1" id="loading-status">Mohon tunggu...</p>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div id="loading-bar" class="h-full bg-sky-400 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
        </div>

        {{-- Kamera --}}
        <div id="camera-section" class="hidden space-y-3">
            <div id="video-container" class="shadow-2xl shadow-slate-900/30">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas-overlay"></canvas>
                {{-- Oval wajah --}}
                <div class="face-oval">
                    <div class="face-oval-ring" id="face-ring"></div>
                </div>
                {{-- Laser scan line (hanya saat idle) --}}
                <div class="scan-line" id="scan-line"></div>
                {{-- Status chip --}}
                <div class="status-chip">
                    <span id="cam-status-text" class="px-3 py-1.5 bg-slate-900/70 backdrop-blur-sm rounded-full text-xs font-semibold text-white">
                        Arahkan wajah ke dalam oval
                    </span>
                </div>
            </div>

            {{-- Result box --}}
            <div id="result-box" class="hidden rounded-2xl p-4 text-center border transition-all">
                <div id="result-icon" class="w-12 h-12 mx-auto mb-2 rounded-full flex items-center justify-center"></div>
                <p id="result-name" class="font-bold text-sm"></p>
                <p id="result-message" class="text-xs mt-1"></p>
                <p id="result-time" class="text-xs font-bold mt-1"></p>
            </div>

            {{-- ======== PETA GPS & GEOFENCING ======== --}}
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500 animate-pulse" id="gps-pulse"></span>
                        <h4 class="text-xs font-bold text-slate-700">Verifikasi Lokasi GPS Presensi</h4>
                    </div>
                    <button type="button" onclick="refreshUserGPS()" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh GPS
                    </button>
                </div>

                {{-- Status Geofencing Card --}}
                <div id="gps-status-card" class="mb-2.5 p-3 rounded-xl border bg-slate-50 border-slate-200 transition-all">
                    <div class="flex items-center gap-2.5">
                        <div id="gps-status-icon" class="w-7 h-7 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p id="gps-status-title" class="text-xs font-bold text-slate-700">Mendeteksi koordinat GPS...</p>
                            <p id="gps-status-desc" class="text-[11px] text-slate-500 truncate">Mohon aktifkan izin lokasi pada browser</p>
                        </div>
                    </div>
                </div>

                {{-- Leaflet Map Container --}}
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100 relative" style="height: 180px;">
                    <div id="map-face" style="height: 100%; width: 100%; z-index: 1;"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1.5 px-0.5">
                    <span class="flex items-center gap-1">🔵 Posisi Anda</span>
                    <span class="flex items-center gap-1">🏫 Sekolah</span>
                    <span class="flex items-center gap-1">🟢 Radius</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ======== MODAL PENDAFTARAN WAJAH ======== --}}
<div id="register-modal" class="hidden fixed inset-0 z-50 flex items-end justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeRegister()"></div>
    <div class="relative bg-white rounded-t-3xl w-full max-w-lg z-10 shadow-2xl">
        {{-- Handle --}}
        <div class="pt-4 pb-2 flex justify-center">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
        </div>
        <div class="px-6 pb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Pendaftaran Wajah</h3>
                    <p class="text-slate-500 text-xs">Posisikan wajah dalam lingkaran, lalu tekan Capture</p>
                </div>
            </div>

            {{-- Tips --}}
            <div class="bg-sky-50 border border-sky-100 rounded-xl p-3 mb-4 text-xs text-sky-700 space-y-1">
                <p class="font-semibold">💡 Tips agar berhasil:</p>
                <p>• Pastikan pencahayaan cukup terang</p>
                <p>• Wajah menghadap lurus ke kamera</p>
                <p>• Jaga jarak ±30-50cm dari kamera</p>
            </div>

            {{-- Video registrasi --}}
            <div id="reg-video-container" class="rounded-2xl overflow-hidden bg-slate-900 relative mb-4" style="aspect-ratio:1/1; max-width:320px; margin:0 auto;">
                <video id="reg-video" autoplay muted playsinline class="w-full h-full object-cover" style="transform: scaleX(-1)"></video>
                {{-- Oval panduan --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div id="reg-oval" class="border-4 border-white/50 rounded-full transition-all duration-300"
                         style="width: 200px; height: 240px;"></div>
                </div>
                {{-- Corner markers --}}
                <div class="absolute top-3 left-3 w-6 h-6 border-t-3 border-l-3 border-white/50 rounded-tl-lg" style="border-top-width:3px; border-left-width:3px;"></div>
                <div class="absolute top-3 right-3 w-6 h-6 border-t-3 border-r-3 border-white/50 rounded-tr-lg" style="border-top-width:3px; border-right-width:3px;"></div>
                <div class="absolute bottom-3 left-3 w-6 h-6 border-b-3 border-l-3 border-white/50 rounded-bl-lg" style="border-bottom-width:3px; border-left-width:3px;"></div>
                <div class="absolute bottom-3 right-3 w-6 h-6 border-b-3 border-r-3 border-white/50 rounded-br-lg" style="border-bottom-width:3px; border-right-width:3px;"></div>
                {{-- Status --}}
                <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                    <span id="reg-detect-status" class="px-3 py-1 bg-slate-900/70 rounded-full text-xs text-white font-semibold">Mendeteksi wajah...</span>
                </div>
            </div>

            {{-- Status teks --}}
            <p id="reg-status" class="text-xs text-center font-medium mb-3 hidden"></p>

            <div class="flex gap-3">
                <button onclick="closeRegister()"
                        class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition-all">
                    Batal
                </button>
                <button id="btn-capture" onclick="captureAndSave()"
                        class="flex-1 py-3 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Capture & Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
const MODEL_URL  = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
const THRESHOLD  = 0.53;
const CSRF       = '{{ csrf_token() }}';
const HAS_FACE   = {{ $hasFace ? 'true' : 'false' }};

// Konfigurasi GPS Geofencing Sekolah
const SCHOOL_LAT    = {{ !empty($pengaturan->latitude) ? (float)$pengaturan->latitude : 'null' }};
const SCHOOL_LNG    = {{ !empty($pengaturan->longitude) ? (float)$pengaturan->longitude : 'null' }};
const SCHOOL_RADIUS = {{ !empty($pengaturan->radius_meter) ? (int)$pengaturan->radius_meter : 100 }};
const SCHOOL_HAS_GEO= SCHOOL_LAT !== null && SCHOOL_LNG !== null;

let userLat = null;
let userLng = null;
let userDist = null;
let isOutsideRadius = false;
let faceMap = null;
let userMarker = null;
let schoolMarker = null;
let schoolCircle = null;

let mainVideo, canvas;
let allDescriptors = [];
let isProcessing   = false;
let lastSuccessAt  = 0;
let regStream      = null;
let regDetectTimer = null;

// ===================== LOADING / INIT =====================
const steps = [
    { label: 'Memuat model deteksi wajah...', pct: 20,  fn: () => faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL) },
    { label: 'Memuat model landmark wajah...', pct: 50, fn: () => faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL) },
    { label: 'Memuat model pengenalan wajah...', pct: 75, fn: () => faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL) },
    { label: 'Mengambil data wajah terdaftar...', pct: 90, fn: () => loadDescriptors() },
    { label: 'Memulai kamera & GPS...', pct: 95, fn: () => Promise.all([startMainCamera(), initGPSMap()]) },
];

async function init() {
    for (const step of steps) {
        document.getElementById('loading-status').textContent = step.label;
        document.getElementById('loading-bar').style.width = step.pct + '%';
        try { await step.fn(); } catch (e) {
            document.getElementById('loading-status').textContent = '❌ Gagal: ' + e.message;
            return;
        }
    }
    document.getElementById('loading-bar').style.width = '100%';
    document.getElementById('loading-model').classList.add('hidden');
    document.getElementById('camera-section').classList.remove('hidden');
    setTimeout(() => { if (faceMap) faceMap.invalidateSize(); }, 300);
    detectLoop();
}

// ===================== GPS & MAPS LOGIC =====================
function calculateDistanceJS(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

async function initGPSMap() {
    const defaultCenter = SCHOOL_HAS_GEO ? [SCHOOL_LAT, SCHOOL_LNG] : [-6.2088, 106.8456];
    faceMap = L.map('map-face', { zoomControl: false, attributionControl: false }).setView(defaultCenter, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(faceMap);

    if (SCHOOL_HAS_GEO) {
        schoolMarker = L.marker([SCHOOL_LAT, SCHOOL_LNG]).addTo(faceMap)
            .bindPopup('<b>Lokasi Sekolah</b>');

        schoolCircle = L.circle([SCHOOL_LAT, SCHOOL_LNG], {
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.25,
            radius: SCHOOL_RADIUS
        }).addTo(faceMap);
    }

    refreshUserGPS();
}

function refreshUserGPS() {
    if (!navigator.geolocation) {
        updateGPSUI('error', 'GPS Tidak Didukung', 'Browser Anda tidak mendukung Geolocation.');
        return;
    }

    updateGPSUI('loading', 'Mencari sinyal GPS...', 'Mengambil koordinat lokasi Anda...');

    navigator.geolocation.getCurrentPosition(
        (pos) => handleGPSPosition(pos),
        (err) => {
            let msg = 'Izin lokasi (GPS) ditolak atau tidak tersedia.';
            if (err.code === 1) msg = 'Izin lokasi ditolak. Harap izinkan GPS pada browser.';
            else if (err.code === 2) msg = 'Posisi GPS tidak ditemukan. Pastikan GPS aktif.';
            else if (err.code === 3) msg = 'Waktu pencarian GPS habis.';
            updateGPSUI('error', 'GPS Tidak Terdeteksi', msg);
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 5000 }
    );

    // Watch position continually
    if (!window.gpsWatchStarted) {
        window.gpsWatchStarted = true;
        navigator.geolocation.watchPosition(
            (pos) => handleGPSPosition(pos),
            (err) => console.warn('Watch GPS error:', err),
            { enableHighAccuracy: true, maximumAge: 10000 }
        );
    }
}

function handleGPSPosition(pos) {
    userLat = pos.coords.latitude;
    userLng = pos.coords.longitude;

    if (!userMarker) {
        const blueIcon = L.divIcon({
            className: 'custom-user-marker',
            html: '<div style="background-color:#0284c7; width:16px; height:16px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 0 8px rgba(2,132,199,0.7);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        userMarker = L.marker([userLat, userLng], { icon: blueIcon }).addTo(faceMap)
            .bindPopup('<b>Posisi Anda Saat Ini</b>');
    } else {
        userMarker.setLatLng([userLat, userLng]);
    }

    if (SCHOOL_HAS_GEO) {
        const dist = calculateDistanceJS(userLat, userLng, SCHOOL_LAT, SCHOOL_LNG);
        userDist = Math.round(dist);

        if (dist <= SCHOOL_RADIUS) {
            isOutsideRadius = false;
            schoolCircle.setStyle({ color: '#10b981', fillColor: '#34d399', fillOpacity: 0.25 });
            updateGPSUI('success', `✅ Di Dalam Radius Sekolah (${userDist} m)`, `Batas radius sekolah: ${SCHOOL_RADIUS} meter. Absensi diizinkan.`);
        } else {
            isOutsideRadius = true;
            schoolCircle.setStyle({ color: '#f43f5e', fillColor: '#fb7185', fillOpacity: 0.35 });
            updateGPSUI('danger', `❌ Di Luar Radius Sekolah (${userDist} m)`, `Maksimal toleransi: ${SCHOOL_RADIUS} meter. Absensi DITOLAK!`);
        }

        // Fit map bounds to show both user and school
        const group = L.featureGroup([schoolMarker, userMarker]);
        faceMap.fitBounds(group.getBounds().pad(0.3));
    } else {
        isOutsideRadius = false;
        faceMap.setView([userLat, userLng], 16);
        updateGPSUI('info', 'Lokasi GPS Terdeteksi', 'Sekolah belum menetapkan batas radius koordinat.');
    }
}

function updateGPSUI(type, title, desc) {
    const card  = document.getElementById('gps-status-card');
    const icon  = document.getElementById('gps-status-icon');
    const titleEl = document.getElementById('gps-status-title');
    const descEl  = document.getElementById('gps-status-desc');
    const pulse   = document.getElementById('gps-pulse');

    titleEl.textContent = title;
    descEl.textContent = desc;

    card.className = 'mb-2.5 p-3 rounded-xl border transition-all ';
    pulse.className = 'w-2.5 h-2.5 rounded-full ';

    if (type === 'success') {
        card.classList.add('bg-emerald-50', 'border-emerald-200');
        icon.className = 'w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        pulse.classList.add('bg-emerald-500');
    } else if (type === 'danger') {
        card.classList.add('bg-rose-50', 'border-rose-200');
        icon.className = 'w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        pulse.classList.add('bg-rose-500');
    } else if (type === 'error') {
        card.classList.add('bg-amber-50', 'border-amber-200');
        icon.className = 'w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        pulse.classList.add('bg-amber-500');
    } else {
        card.classList.add('bg-sky-50', 'border-sky-200');
        icon.className = 'w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-4 h-4 animate-spin text-sky-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        pulse.classList.add('bg-sky-500', 'animate-pulse');
    }
}

async function loadDescriptors() {
    const res  = await fetch('{{ route("guru.face-descriptors") }}');
    const data = await res.json();
    allDescriptors = data.filter(g => g.face_descriptor && g.face_descriptor.length > 0);
}

async function startMainCamera() {
    mainVideo = document.getElementById('video');
    canvas    = document.getElementById('canvas-overlay');
    const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }
    });
    mainVideo.srcObject = stream;
    await new Promise(r => mainVideo.addEventListener('loadeddata', r, { once: true }));
    faceapi.matchDimensions(canvas, { width: mainVideo.videoWidth, height: mainVideo.videoHeight });
}

// ===================== DETEKSI LOOP =====================
async function detectLoop() {
    if (!mainVideo || mainVideo.paused || mainVideo.ended) return;

    const ring    = document.getElementById('face-ring');
    const scanLine= document.getElementById('scan-line');
    const statusEl= document.getElementById('cam-status-text');

    // Gunakan inputSize: 416 & scoreThreshold: 0.38 agar langsung mendeteksi wajah di HP pada jarak selfie normal
    const detection = await faceapi
        .detectSingleFace(mainVideo, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.38 }))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (detection) {
        ring.classList.add('detected');
        ring.classList.remove('matched');
        scanLine.style.display = 'none';

        if (!isProcessing && allDescriptors.length > 0) {
            const q = detection.descriptor;
            let best = null, bestDist = Infinity;
            for (const g of allDescriptors) {
                const d = faceapi.euclideanDistance(q, g.face_descriptor);
                if (d < bestDist) { bestDist = d; best = g; }
            }
            if (bestDist < THRESHOLD) {
                const now = Date.now();
                if (now - lastSuccessAt > 4000) {
                    lastSuccessAt = now;
                    ring.classList.remove('detected');
                    ring.classList.add('matched');
                    statusEl.textContent = '✓ ' + best.nama_lengkap;
                    await processAbsensi(best.id);
                }
            } else {
                statusEl.textContent = allDescriptors.length === 0 ? 'Belum ada wajah terdaftar' : 'Wajah tidak dikenali';
            }
        } else if (allDescriptors.length === 0) {
            statusEl.textContent = 'Daftarkan wajah terlebih dahulu ↑';
        } else {
            statusEl.textContent = 'Wajah terdeteksi, sedang memproses...';
        }
    } else {
        ring.classList.remove('detected', 'matched');
        scanLine.style.display = '';
        statusEl.textContent = 'Arahkan wajah ke dalam oval';
    }

    setTimeout(detectLoop, 200);
}

// ===================== PROSES ABSENSI =====================
async function processAbsensi(guruId) {
    // Validasi Geofencing GPS di sisi Klien (Tolak jika di luar radius)
    if (SCHOOL_HAS_GEO && isOutsideRadius) {
        showResult({
            success: false,
            message: `Presensi DITOLAK: Anda berada di luar radius sekolah. Jarak: ${userDist} meter (Maks: ${SCHOOL_RADIUS} meter).`
        });
        return;
    }

    if (SCHOOL_HAS_GEO && (userLat === null || userLng === null)) {
        showResult({
            success: false,
            message: 'Presensi DITOLAK: Koordinat GPS belum terdeteksi. Silakan aktifkan izin lokasi di browser Anda.'
        });
        return;
    }

    isProcessing = true;
    try {
        const res  = await fetch('{{ route("guru.face-process") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                guru_id:   guruId,
                latitude:  userLat,
                longitude: userLng
            })
        });
        showResult(await res.json());
    } catch(e) {
        showResult({ success: false, message: 'Koneksi gagal.' });
    }
    setTimeout(() => { isProcessing = false; }, 4500);
}

function showResult(data) {
    const box  = document.getElementById('result-box');
    const icon = document.getElementById('result-icon');
    const name = document.getElementById('result-name');
    const msg  = document.getElementById('result-message');
    const time = document.getElementById('result-time');

    box.className = 'rounded-2xl p-4 text-center border transition-all';

    if (data.success) {
        box.classList.add('bg-emerald-50', 'border-emerald-200');
        icon.className = 'w-12 h-12 mx-auto mb-2 rounded-full bg-emerald-100 flex items-center justify-center';
        icon.innerHTML = `<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        name.textContent  = data.user_name;
        name.className    = 'font-bold text-emerald-700 text-sm';
        msg.textContent   = data.type === 'masuk' ? '✅ Absensi Masuk Berhasil!' : '🚪 Absensi Pulang Berhasil!';
        msg.className     = 'text-xs text-emerald-600 mt-1';
        time.textContent  = new Date().toLocaleTimeString('id-ID');
        time.className    = 'text-xs font-bold text-emerald-500 mt-1';
    } else {
        box.classList.add('bg-rose-50', 'border-rose-200');
        icon.className = 'w-12 h-12 mx-auto mb-2 rounded-full bg-rose-100 flex items-center justify-center';
        icon.innerHTML = `<svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
        name.textContent = 'Tidak Berhasil';
        name.className   = 'font-bold text-rose-700 text-sm';
        msg.textContent  = data.message;
        msg.className    = 'text-xs text-rose-500 mt-1';
        time.textContent = '';
    }

    box.classList.remove('hidden');
    setTimeout(() => box.classList.add('hidden'), 5000);
}

// ===================== REGISTER WAJAH =====================
async function startRegister() {
    document.getElementById('register-modal').classList.remove('hidden');
    const regVideo = document.getElementById('reg-video');
    try {
        regStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        regVideo.srcObject = regStream;
        await new Promise(r => regVideo.addEventListener('loadeddata', r, { once: true }));
        startRegDetect();
    } catch(e) {
        document.getElementById('reg-status').textContent = '❌ Kamera tidak dapat diakses: ' + e.message;
        document.getElementById('reg-status').classList.remove('hidden');
    }
}

function startRegDetect() {
    const regVideo    = document.getElementById('reg-video');
    const regOval     = document.getElementById('reg-oval');
    const regStatus   = document.getElementById('reg-detect-status');

    async function detect() {
        if (!regStream) return;
        const det = await faceapi.detectSingleFace(regVideo,
            new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.38 }));
        if (det) {
            regOval.style.borderColor = 'rgba(34,197,94,0.9)';
            regStatus.textContent = '✓ Wajah terdeteksi – siap di-capture!';
            document.getElementById('btn-capture').disabled = false;
            document.getElementById('btn-capture').classList.remove('opacity-50');
        } else {
            regOval.style.borderColor = 'rgba(255,255,255,0.5)';
            regStatus.textContent = 'Arahkan wajah ke tengah lingkaran...';
        }
        regDetectTimer = setTimeout(detect, 500);
    }
    detect();
}

function closeRegister() {
    document.getElementById('register-modal').classList.add('hidden');
    if (regDetectTimer) clearTimeout(regDetectTimer);
    if (regStream) { regStream.getTracks().forEach(t => t.stop()); regStream = null; }
    document.getElementById('reg-status').classList.add('hidden');
    document.getElementById('reg-status').textContent = '';
}

async function captureAndSave() {
    const regVideo = document.getElementById('reg-video');
    const statusEl = document.getElementById('reg-status');
    const btn      = document.getElementById('btn-capture');

    btn.disabled = true;
    btn.textContent = 'Memproses...';
    statusEl.textContent = '🔍 Mendeteksi & memproses wajah...';
    statusEl.className = 'text-xs text-center font-medium mb-3 text-blue-600';
    statusEl.classList.remove('hidden');

    try {
        const det = await faceapi
            .detectSingleFace(regVideo, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.38 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!det) {
            statusEl.textContent = '❌ Wajah tidak terdeteksi. Coba lagi dengan pencahayaan lebih baik.';
            statusEl.className = 'text-xs text-center font-medium mb-3 text-rose-600';
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Capture & Simpan`;
            return;
        }

        // Cek langsung di sisi client apakah wajah ini sudah terdaftar atas nama guru lain
        const currentGuruId = Number('{{ $guru->id }}');
        for (const g of allDescriptors) {
            if (Number(g.id) !== currentGuruId && g.face_descriptor && g.face_descriptor.length === 128) {
                const dist = faceapi.euclideanDistance(det.descriptor, g.face_descriptor);
                if (dist < 0.45) {
                    statusEl.textContent = `❌ Pendaftaran Ditolak: Wajah ini sudah terdaftar atas nama ${g.nama_lengkap} (NIP: ${g.nip || '-'}). Satu wajah hanya boleh untuk 1 akun guru!`;
                    statusEl.className = 'text-xs text-center font-medium mb-3 text-rose-600';
                    btn.disabled = false;
                    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Capture & Simpan`;
                    return;
                }
            }
        }

        statusEl.textContent = '💾 Menyimpan data wajah ke server...';

        const res = await fetch('{{ route("guru.face-save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ descriptor: Array.from(det.descriptor) })
        });
        const data = await res.json();

        if (data.success) {
            statusEl.textContent = '🎉 ' + data.message;
            statusEl.className = 'text-xs text-center font-medium mb-3 text-emerald-600';
            await loadDescriptors();
            // Reload page after 2s to update the hasFace status badge
            setTimeout(() => { window.location.reload(); }, 2000);
        } else {
            statusEl.textContent = '❌ ' + (data.message || 'Gagal menyimpan.');
            statusEl.className = 'text-xs text-center font-medium mb-3 text-rose-600';
        }
    } catch(e) {
        statusEl.textContent = '❌ Error: ' + e.message;
        statusEl.className = 'text-xs text-center font-medium mb-3 text-rose-600';
    }

    btn.disabled = false;
    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Capture & Simpan`;
}

window.addEventListener('load', init);
</script>
@endpush
