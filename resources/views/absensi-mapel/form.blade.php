@extends('layouts.guru')

@section('title', 'Input Absensi Mapel')
@section('back_url', route('absensi-mapel.index'))

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white/90 backdrop-blur-sm p-4 rounded-2xl border border-sky-100 shadow-sm">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <a href="{{ route('absensi-mapel.index', ['tanggal' => $tanggal]) }}" class="p-2 rounded-xl bg-sky-50 border border-sky-200 text-sky-600 hover:bg-sky-100 transition-colors shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-lg sm:text-xl font-black text-slate-800 truncate">Input Absensi Mapel</h1>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs text-slate-500 mt-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-sky-100 text-sky-800 font-bold">
                    {{ $jadwal->mataPelajaran?->nama_mapel }}
                </span>
                <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-800 font-bold">
                    Kelas {{ $jadwal->kelas?->tingkat }} {{ $jadwal->kelas?->nama_rombel }}
                </span>
                <span class="text-slate-400 font-medium">
                    &middot; {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </span>
                <span class="text-slate-400 font-medium">
                    &middot; Jam {{ $jadwal->jam_range }}
                </span>
            </div>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-3.5 py-2 shadow-sm text-xs font-bold shrink-0 flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><span id="rekap-hadir" class="font-extrabold text-emerald-700">0</span> / {{ $siswaList->count() }} Siswa Hadir</span>
        </div>
    </div>

    <form action="{{ route('absensi-mapel.store', $jadwal->id) }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <!-- Data Pembelajaran (wajib diisi sebelum absen) -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden mb-4">
            <div class="px-4 sm:px-5 py-3 sm:py-3.5 bg-gradient-to-r from-violet-600 to-purple-700 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-xs sm:text-sm">Jurnal &amp; Data Pembelajaran</h2>
                    <p class="text-[10px] sm:text-[11px] text-violet-100/90">Wajib diisi guru untuk jurnal kegiatan belajar mengajar.</p>
                </div>
            </div>

            <div class="p-3.5 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        Jumlah Jam Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="jumlah_jam" min="1" max="15" required
                           value="{{ old('jumlah_jam', $jurnal->jumlah_jam ?? '') }}"
                           placeholder="Misal: 2"
                           class="w-full px-3.5 py-2 sm:py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 transition-all @error('jumlah_jam') border-rose-400 bg-rose-50 @enderror">
                    @error('jumlah_jam')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        Keterangan Pembelajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="keterangan_pembelajaran" maxlength="500" required
                           value="{{ old('keterangan_pembelajaran', $jurnal->keterangan ?? '') }}"
                           placeholder="Misal: KBM berjalan kondusif"
                           class="w-full px-3.5 py-2 sm:py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 transition-all @error('keterangan_pembelajaran') border-rose-400 bg-rose-50 @enderror">
                    @error('keterangan_pembelajaran')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        Materi Hari Ini <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="materi" rows="2" maxlength="500" required
                              placeholder="Materi yang diajarkan hari ini..."
                              class="w-full px-3.5 py-2 sm:py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 transition-all @error('materi') border-rose-400 bg-rose-50 @enderror">{{ old('materi', $jurnal->materi ?? '') }}</textarea>
                    @error('materi')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        Rencana Pertemuan Berikutnya <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="materi_selanjutnya" rows="2" maxlength="500"
                              placeholder="Materi lanjutan pertemuan berikutnya..."
                              class="w-full px-3.5 py-2 sm:py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 transition-all @error('materi_selanjutnya') border-rose-400 bg-rose-50 @enderror">{{ old('materi_selanjutnya', $jurnal->materi_selanjutnya ?? '') }}</textarea>
                    @error('materi_selanjutnya')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-3">
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                <button type="button" onclick="tandaiSemua('Hadir')"
                        class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Semua Hadir
                </button>
                <button type="button" onclick="tandaiSemua('Alpa')"
                        class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5 shadow-sm">
                    Semua Alpa
                </button>
                <button type="button" id="btn-open-qr"
                        class="col-span-2 sm:col-span-1 px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-sky-300/30 active:scale-95 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    Scan QR Siswa
                </button>
            </div>
            <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Klik status (Hadir/Izin/Sakit/Alpa) pada setiap siswa untuk mencatat kehadiran.</span>
        </div>

        <!-- Daftar Siswa -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden divide-y divide-sky-100">
            <!-- Desktop Table Header -->
            <div class="hidden md:grid md:grid-cols-12 md:gap-3 px-5 py-3.5 bg-sky-50/70 border-b border-sky-100 text-slate-600 text-xs font-extrabold uppercase tracking-wider">
                <div class="md:col-span-1 text-center">No</div>
                <div class="md:col-span-4">Nama Siswa &amp; NIS</div>
                <div class="md:col-span-4 text-center">Status Kehadiran</div>
                <div class="md:col-span-3">Keterangan (Opsional)</div>
            </div>

            <!-- Student Rows / Cards -->
            @forelse($siswaList as $i => $s)
            @php
                $saved = $existing->get($s->id);
            @endphp
            <div class="p-3.5 md:px-5 md:py-3.5 hover:bg-sky-50/30 transition-all duration-200"
                 id="row-{{ $s->id }}"
                 data-siswa-id="{{ $s->id }}"
                 data-nis="{{ $s->nis }}">
                <input type="hidden" name="absensi[{{ $i }}][siswa_id]" value="{{ $s->id }}">

                <div class="flex flex-col md:grid md:grid-cols-12 md:gap-3 md:items-center gap-2.5">
                    <!-- No & Nama (Card header on mobile, columns on desktop) -->
                    <div class="flex items-center gap-2.5 md:col-span-5 min-w-0">
                        <span class="w-6 h-6 md:w-7 md:h-7 rounded-lg bg-slate-100 text-slate-500 font-black text-xs flex items-center justify-center shrink-0 md:mx-auto">
                            {{ $i + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="siswa-nama font-bold text-slate-800 text-xs sm:text-sm truncate">{{ $s->nama_lengkap }}</p>
                            <p class="font-mono text-[11px] text-slate-400">NIS: {{ $s->nis ?: '-' }}</p>
                        </div>
                    </div>

                    <!-- Status Buttons: 4 Touch-friendly columns on mobile, 4 pills on desktop -->
                    <div class="md:col-span-4">
                        <div class="grid grid-cols-4 gap-1.5">
                            @foreach($statusList as $st)
                            @php
                                $warna = [
                                    'Hadir' => 'peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:text-white peer-checked:shadow-sm peer-checked:shadow-emerald-200',
                                    'Izin'  => 'peer-checked:bg-sky-500 peer-checked:border-sky-500 peer-checked:text-white peer-checked:shadow-sm peer-checked:shadow-sky-200',
                                    'Sakit' => 'peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white peer-checked:shadow-sm peer-checked:shadow-amber-200',
                                    'Alpa'  => 'peer-checked:bg-rose-500 peer-checked:border-rose-500 peer-checked:text-white peer-checked:shadow-sm peer-checked:shadow-rose-200',
                                ];
                            @endphp
                            <label class="cursor-pointer select-none">
                                <input type="radio" name="absensi[{{ $i }}][status]" value="{{ $st }}"
                                       class="peer sr-only" @checked(($saved?->status ?? null) === $st)
                                       onchange="hitung()">
                                <span class="flex items-center justify-center py-2 md:py-1.5 px-1 rounded-xl border border-slate-200 bg-white text-center text-xs font-black text-slate-600 transition-all hover:border-sky-300 active:scale-95 {{ $warna[$st] }}">
                                    {{ $st }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Keterangan Field -->
                    <div class="md:col-span-3">
                        <input type="text" name="absensi[{{ $i }}][keterangan]" value="{{ $saved?->keterangan }}"
                               placeholder="Keterangan..."
                               class="w-full px-3 py-1.5 md:py-2 bg-slate-50 border border-sky-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-300 transition-all">
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-xs sm:text-sm">
                Belum ada siswa terdaftar di kelas ini.
            </div>
            @endforelse
        </div>

        @if($siswaList->isNotEmpty())
            <div class="mt-4">
                @if(!$canAbsen && !$isWaliKelas)
                    <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl mb-3 flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-xs sm:text-sm font-medium">
                            Sesi jam pelajaran ini telah berakhir. Anda hanya dapat melihat data absensi (hanya wali kelas yang dapat mengubah susulan).
                        </p>
                    </div>
                @elseif($hasAbsen)
                    <div class="p-3 bg-sky-50 border border-sky-200 text-sky-800 rounded-2xl mb-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs font-semibold">
                            Mode Perbarui: Data absensi dan jurnal telah tersimpan. Anda dapat mengubah status kehadiran jika diperlukan.
                        </p>
                    </div>
                @endif

                <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2.5 pb-8">
                    <a href="{{ route('absensi-mapel.index', ['tanggal' => $tanggal]) }}"
                       class="px-5 py-2.5 text-center text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors border border-slate-200 sm:border-transparent">
                        Batal &amp; Kembali
                    </a>
                    <button type="submit"
                            @if(!$canAbsen && !$isWaliKelas) disabled @endif
                            class="px-6 py-2.5 text-xs sm:text-sm font-black text-white bg-gradient-to-r from-sky-500 to-blue-600 @if(!$canAbsen && !$isWaliKelas) opacity-50 cursor-not-allowed @else hover:from-sky-600 hover:to-blue-700 shadow-md shadow-sky-300/40 hover:-translate-y-0.5 active:scale-95 @endif rounded-xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $hasAbsen ? 'Perbarui Absensi Mapel' : 'Simpan Absensi Mapel' }}</span>
                    </button>
                </div>
            </div>
        @endif

    </form>
</div>

<!-- ======================== MODAL QR SCANNER ======================== -->
<div id="qr-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div id="qr-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal Content -->
    <div class="absolute inset-4 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-full sm:max-w-2xl sm:max-h-[90vh] bg-white rounded-3xl shadow-2xl shadow-black/20 flex flex-col overflow-hidden z-10">

        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-blue-700 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm">Scanner QR Kehadiran</h3>
                    <p class="text-[11px] text-sky-100/90">Arahkan QR Code siswa ke kamera</p>
                </div>
            </div>
            <button type="button" id="btn-close-qr"
                    class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white flex items-center justify-center transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Scanner Area -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden min-h-0">

            <!-- Camera View -->
            <div class="flex-1 relative bg-slate-900 flex items-center justify-center min-h-[280px] lg:min-h-0">
                <div id="qr-reader" class="w-full h-full"></div>

                <!-- Laser Scanner Overlay -->
                <div id="qr-laser-overlay" class="absolute inset-0 z-10 pointer-events-none flex items-center justify-center hidden">
                    <div class="absolute w-[60%] h-0.5 bg-gradient-to-r from-transparent via-sky-400 to-transparent shadow-[0_0_10px_rgba(56,189,248,0.8)]"
                         style="animation: qrLaser 3s ease-in-out infinite;"></div>
                    <div class="w-[60%] aspect-square max-w-[200px] relative">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-sky-400 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-sky-400 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-sky-400 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-sky-400 rounded-br-lg"></div>
                    </div>
                </div>

                <!-- Success Flash -->
                <div id="qr-flash-success" class="absolute inset-0 bg-emerald-500/20 z-20 pointer-events-none hidden"></div>
                <!-- Error Flash -->
                <div id="qr-flash-error" class="absolute inset-0 bg-rose-500/20 z-20 pointer-events-none hidden"></div>

                <!-- Loading Camera -->
                <div id="qr-loading" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center gap-3 z-30">
                    <div class="w-10 h-10 border-4 border-sky-400 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm font-semibold text-sky-200">Menghubungkan kamera...</p>
                </div>
            </div>

            <!-- Scan Log Panel -->
            <div class="w-full lg:w-72 bg-slate-50 flex flex-col border-t lg:border-t-0 lg:border-l border-sky-100 max-h-[200px] lg:max-h-none">
                <div class="px-4 py-3 bg-white border-b border-sky-100 flex items-center justify-between shrink-0">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Log Scan</h4>
                    <span id="qr-scan-count" class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-600">0 terscan</span>
                </div>
                <div id="qr-log-list" class="flex-1 overflow-y-auto p-3 space-y-2 text-xs">
                    <div class="text-center text-slate-400 py-6">
                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Menunggu scan QR...
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer: Camera Selector -->
        <div class="px-5 py-3 bg-white border-t border-sky-100 flex items-center gap-3 shrink-0">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <select id="qr-camera-select" class="flex-1 text-xs font-semibold text-slate-600 bg-slate-50 border border-sky-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400/30 min-w-0"></select>
            </div>
            <div id="qr-status-indicator" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Idle</span>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes qrLaser {
        0%   { top: 25%; opacity: 0.3; }
        50%  { top: 75%; opacity: 1; }
        100% { top: 25%; opacity: 0.3; }
    }
    #qr-reader video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader img[alt="Info icon"], #qr-reader img[alt="Camera based scan"] { display: none !important; }
    #qr-reader > div:last-child { display: none !important; }
    .qr-highlight-success {
        animation: qrRowFlash 1.5s ease-out;
    }
    @keyframes qrRowFlash {
        0%   { background-color: rgba(16, 185, 129, 0.25); }
        100% { background-color: transparent; }
    }
</style>
@endsection

@push('scripts')
<!-- HTML5 QR Code Library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    // ========== Original Functions ==========
    function tandaiSemua(status) {
        document.querySelectorAll('input[type="radio"][name$="[status]"][value="' + status + '"]').forEach(function (r) {
            r.checked = true;
        });
        hitung();
    }

    function hitung() {
        var total = document.querySelectorAll('input[type="hidden"][name$="[siswa_id]"]').length;
        var hadir = document.querySelectorAll('input[type="radio"][name$="[status]"]:checked[value="Hadir"]').length;
        var el = document.getElementById('rekap-hadir');
        if (el) el.textContent = hadir;
    }

    document.addEventListener('DOMContentLoaded', hitung);

    // ========== QR Scanner Module ==========
    (function() {
        'use strict';

        // Build siswa lookup map from the form rows
        var siswaMap = {};
        document.querySelectorAll('[data-siswa-id]').forEach(function(row) {
            var id = row.getAttribute('data-siswa-id');
            var nis = row.getAttribute('data-nis');
            var namaEl = row.querySelector('.siswa-nama') || row.querySelector('td:nth-child(2)');
            var nama = namaEl ? namaEl.textContent.trim() : 'Siswa';
            siswaMap[id] = { row: row, nama: nama, nis: nis };
            if (nis) {
                siswaMap[nis] = { row: row, nama: nama, nis: nis, siswaId: id };
            }
        });

        var html5QrCode = null;
        var isScanning = true;
        var scanCount = 0;
        var scannedIds = new Set(); // Track already-scanned students

        // DOM elements
        var modal = document.getElementById('qr-modal');
        var btnOpen = document.getElementById('btn-open-qr');
        var btnClose = document.getElementById('btn-close-qr');
        var backdrop = document.getElementById('qr-backdrop');
        var cameraSelect = document.getElementById('qr-camera-select');
        var laserOverlay = document.getElementById('qr-laser-overlay');
        var loadingEl = document.getElementById('qr-loading');
        var logList = document.getElementById('qr-log-list');
        var scanCountEl = document.getElementById('qr-scan-count');
        var statusIndicator = document.getElementById('qr-status-indicator');
        var flashSuccess = document.getElementById('qr-flash-success');
        var flashError = document.getElementById('qr-flash-error');

        // ---- Open Modal ----
        btnOpen.addEventListener('click', function() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            startScanner();
        });

        // ---- Close Modal ----
        function closeModal() {
            stopCamera().then(function() {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }
        btnClose.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);

        // ---- Camera Select Change ----
        cameraSelect.addEventListener('change', function() {
            startCamera(this.value);
        });

        // ---- Set Status Indicator ----
        function setStatus(state, text) {
            var colors = {
                scanning: 'bg-emerald-100 border-emerald-200',
                success: 'bg-emerald-100 border-emerald-200',
                error: 'bg-rose-100 border-rose-200',
                idle: 'bg-slate-100 border-slate-200',
                loading: 'bg-sky-100 border-sky-200'
            };
            var dotColors = {
                scanning: 'bg-emerald-500 animate-pulse',
                success: 'bg-emerald-500',
                error: 'bg-rose-500',
                idle: 'bg-slate-400',
                loading: 'bg-sky-500 animate-pulse'
            };
            var textColors = {
                scanning: 'text-emerald-600',
                success: 'text-emerald-600',
                error: 'text-rose-600',
                idle: 'text-slate-500',
                loading: 'text-sky-600'
            };
            statusIndicator.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full border ' + (colors[state] || colors.idle);
            statusIndicator.innerHTML = '<span class="w-2 h-2 rounded-full ' + (dotColors[state] || dotColors.idle) + '"></span>' +
                '<span class="text-[10px] font-bold uppercase tracking-wider ' + (textColors[state] || textColors.idle) + '">' + text + '</span>';
        }

        // ---- Start Scanner (get cameras) ----
        function startScanner() {
            setStatus('loading', 'Memuat...');
            loadingEl.classList.remove('hidden');
            laserOverlay.classList.add('hidden');

            Html5Qrcode.getCameras().then(function(devices) {
                cameraSelect.innerHTML = '';
                devices.forEach(function(d) {
                    var opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.label || ('Kamera ' + d.id.substr(0, 8));
                    cameraSelect.appendChild(opt);
                });

                if (devices.length > 0) {
                    // Prefer back camera for scanning
                    var backCamera = devices.find(function(d) {
                        return d.label.toLowerCase().includes('back') ||
                               d.label.toLowerCase().includes('belakang') ||
                               d.label.toLowerCase().includes('rear') ||
                               d.label.toLowerCase().includes('environment');
                    });
                    var selectedId = backCamera ? backCamera.id : devices[0].id;
                    cameraSelect.value = selectedId;
                    startCamera(selectedId);
                } else {
                    loadingEl.innerHTML = '<div class="text-center"><svg class="w-12 h-12 text-rose-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><p class="text-sm font-bold text-white">Tidak ada kamera terdeteksi</p></div>';
                    setStatus('error', 'No Camera');
                }
            }).catch(function(err) {
                loadingEl.innerHTML = '<div class="text-center p-4"><svg class="w-12 h-12 text-rose-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><p class="text-sm font-bold text-white mb-1">Gagal Akses Kamera</p><p class="text-xs text-slate-400">Pastikan izin kamera diaktifkan di browser.</p></div>';
                setStatus('error', 'Gagal');
            });
        }

        // ---- Start Camera ----
        function startCamera(cameraId) {
            var doStart = function() {
                loadingEl.classList.remove('hidden');
                laserOverlay.classList.add('hidden');
                setStatus('loading', 'Memuat...');

                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode('qr-reader');
                }

                html5QrCode.start(
                    cameraId,
                    {
                        fps: 15,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            var min = Math.min(viewfinderWidth, viewfinderHeight);
                            var size = Math.floor(min * 0.6);
                            return { width: size, height: size };
                        }
                    },
                    function(decodedText) {
                        if (!isScanning) return;
                        isScanning = false;
                        handleScan(decodedText);
                    },
                    function() { /* ignore scan errors */ }
                ).then(function() {
                    loadingEl.classList.add('hidden');
                    laserOverlay.classList.remove('hidden');
                    setStatus('scanning', 'Scanning...');
                    isScanning = true;
                }).catch(function(err) {
                    loadingEl.innerHTML = '<div class="text-center p-4"><p class="text-sm font-bold text-white mb-1">Gagal Memulai Kamera</p><p class="text-xs text-slate-400">' + err + '</p></div>';
                    setStatus('error', 'Error');
                });
            };

            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(doStart).catch(doStart);
            } else {
                doStart();
            }
        }

        // ---- Stop Camera ----
        function stopCamera() {
            if (html5QrCode && html5QrCode.isScanning) {
                return html5QrCode.stop().then(function() {
                    laserOverlay.classList.add('hidden');
                    setStatus('idle', 'Idle');
                });
            }
            return Promise.resolve();
        }

        // ---- Handle Scanned QR ----
        function handleScan(code) {
            var entry = siswaMap[code];

            if (!entry) {
                // Not found
                playBeep(false);
                showFlash('error');
                addLog(false, 'Tidak Dikenali', 'QR "' + code.substr(0, 20) + '..." tidak ditemukan di kelas ini.');
                setStatus('error', 'Tidak Ditemukan');
                resumeAfterDelay(1500);
                return;
            }

            var row = entry.row;
            var siswaId = row.getAttribute('data-siswa-id');

            // Check if already scanned
            if (scannedIds.has(siswaId)) {
                playBeep(false);
                showFlash('error');
                addLog(false, entry.nama, 'Sudah di-scan sebelumnya.');
                setStatus('error', 'Duplikat');
                resumeAfterDelay(1200);
                return;
            }

            // Mark as Hadir
            var radioHadir = row.querySelector('input[type="radio"][value="Hadir"]');
            if (radioHadir) {
                radioHadir.checked = true;
            }

            scannedIds.add(siswaId);
            scanCount++;
            scanCountEl.textContent = scanCount + ' terscan';

            // Visual feedback on table row
            row.classList.add('qr-highlight-success');
            setTimeout(function() { row.classList.remove('qr-highlight-success'); }, 1500);

            // Update counter
            hitung();

            // Feedback
            playBeep(true);
            showFlash('success');
            addLog(true, entry.nama, 'Hadir ✓');
            setStatus('success', 'Berhasil!');

            // Scroll to the row in the background table (smooth)
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });

            resumeAfterDelay(1000);
        }

        // ---- Resume scanning after delay ----
        function resumeAfterDelay(ms) {
            setTimeout(function() {
                isScanning = true;
                setStatus('scanning', 'Scanning...');
            }, ms);
        }

        // ---- Add Log Entry ----
        function addLog(success, nama, message) {
            var now = new Date();
            var time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');

            // Remove placeholder if exists
            var placeholder = logList.querySelector('.text-center.text-slate-400');
            if (placeholder) placeholder.remove();

            var div = document.createElement('div');
            div.className = 'flex items-center gap-2.5 p-2.5 rounded-xl border transition-all ' +
                (success
                    ? 'bg-emerald-50 border-emerald-200'
                    : 'bg-rose-50 border-rose-200');

            div.innerHTML =
                '<div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ' +
                (success ? 'bg-emerald-500' : 'bg-rose-500') + '">' +
                (success
                    ? '<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>'
                ) + '</div>' +
                '<div class="flex-1 min-w-0">' +
                '<p class="font-bold text-slate-700 truncate">' + nama + '</p>' +
                '<p class="text-[10px] ' + (success ? 'text-emerald-600' : 'text-rose-600') + '">' + message + '</p>' +
                '</div>' +
                '<span class="text-[10px] font-mono text-slate-400 shrink-0">' + time + '</span>';

            logList.insertBefore(div, logList.firstChild);

            // Keep max 30 logs
            while (logList.children.length > 30) {
                logList.removeChild(logList.lastChild);
            }
        }

        // ---- Flash Effect ----
        function showFlash(type) {
            var el = type === 'success' ? flashSuccess : flashError;
            el.classList.remove('hidden');
            setTimeout(function() { el.classList.add('hidden'); }, 300);
        }

        // ---- Beep Sound ----
        function playBeep(isSuccess) {
            try {
                var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                var oscillator = audioCtx.createOscillator();
                var gainNode = audioCtx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                if (isSuccess) {
                    oscillator.type = 'sine';
                    oscillator.frequency.value = 1000;
                    gainNode.gain.setValueAtTime(0.4, audioCtx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.12);
                    oscillator.start(audioCtx.currentTime);
                    oscillator.stop(audioCtx.currentTime + 0.12);
                } else {
                    oscillator.type = 'sawtooth';
                    oscillator.frequency.value = 300;
                    gainNode.gain.setValueAtTime(0.4, audioCtx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                    oscillator.start(audioCtx.currentTime);
                    oscillator.stop(audioCtx.currentTime + 0.3);
                }
            } catch(e) { /* silent */ }
        }

    })();
</script>
@endpush
