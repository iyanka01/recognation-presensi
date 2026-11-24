@extends('layouts.app')

@section('title', 'Kirim Laporan WhatsApp')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'manual', // 'manual' atau 'laporan'
    ...whatsappBroadcast()
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                </span>
                Kirim Pesan &amp; Laporan WhatsApp
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pilih broadcast manual ke kelas/wali murid atau kirim otomatis rekapitulasi kehadiran presensi harian.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(empty($lembaga->wanesia_token))
                <div class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-medium flex items-center gap-1.5">
                    <span>⚠️ Token WhatsApp belum disetel</span>
                    <a href="{{ route('pengaturan-lembaga.index') }}" class="underline font-bold hover:text-amber-900">Atur Di Sini</a>
                </div>
            @else
                <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Gateway Wanesia Aktif
                </div>
            @endif
        </div>
    </div>

    <!-- Tab Buttons -->
    <div class="flex items-center gap-3 border-b border-slate-200">
        <button type="button" @click="activeTab = 'manual'"
                :class="activeTab === 'manual' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="px-4 py-2.5 border-b-2 text-sm transition-all flex items-center gap-2">
            <i class="ti ti-message-share text-base"></i>
            Kirim Pesan Broadcast Manual
        </button>
        <button type="button" @click="activeTab = 'peringatan'"
                :class="activeTab === 'peringatan' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="px-4 py-2.5 border-b-2 text-sm transition-all flex items-center gap-2">
            <i class="ti ti-bell-ringing text-base text-amber-500"></i>
            Monitoring &amp; Peringatan Guru
        </button>
        <button type="button" @click="activeTab = 'laporan'"
                :class="activeTab === 'laporan' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="px-4 py-2.5 border-b-2 text-sm transition-all flex items-center gap-2">
            <i class="ti ti-report-analytics text-base"></i>
            Kirim Laporan Rekap Presensi
        </button>
    </div>

    <!-- TAB 1: FORM BROADCAST MANUAL (Desain Modern & Luas) -->
    <div x-show="activeTab === 'manual'" x-transition class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200">
        <form action="{{ route('whatsapp-broadcast.sendManual') }}" method="POST" x-data="{
            tujuan: 'siswa_per_kelas',
            departemens: {{ json_encode($departemens) }},
            kelasList: {{ json_encode($kelasList) }},
            departemenId: '{{ $departemens->first()?->id ?? '' }}',
            selectedTingkat: '',
            selectedKelas: [],
            pesanText: '',
            charCount: 0,
            init() {
                this.updateAvailableTingkats();
            },
            onDepartemenChange() {
                this.updateAvailableTingkats();
                this.selectedKelas = [];
            },
            get availableTingkats() {
                const classesInDep = this.kelasList.filter(k => !this.departemenId || k.departemen_id == this.departemenId);
                const tingkats = [...new Set(classesInDep.map(k => String(k.tingkat)).filter(Boolean))];
                return tingkats.sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
            },
            updateAvailableTingkats() {
                const t = this.availableTingkats;
                this.selectedTingkat = t.length > 0 ? t[0] : '';
            },
            get filteredKelas() {
                return this.kelasList.filter(k => {
                    const matchDep = !this.departemenId || k.departemen_id == this.departemenId;
                    const matchTingkat = !this.selectedTingkat || String(k.tingkat) === String(this.selectedTingkat);
                    return matchDep && matchTingkat;
                });
            },
            toggleSelectAll(e) {
                if (e.target.checked) {
                    this.selectedKelas = this.filteredKelas.map(k => k.id);
                } else {
                    this.selectedKelas = [];
                }
            },
            updateCharCount() {
                this.charCount = this.pesanText.length;
            }
        }">
            @csrf

            <!-- Heading Modul -->
            <div class="border-b border-slate-100 pb-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Form Pengiriman Pesan Broadcast</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kirim pengumuman, info sekolah, atau notifikasi langsung ke WhatsApp orang tua siswa atau staf pengajar.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold flex items-center gap-1.5">
                        <i class="ti ti-brand-whatsapp text-sm"></i>
                        Wanesia Gateway Siap
                    </span>
                </div>
            </div>

            <!-- 2-Kolom Lebar & Proporsional -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Kolom Kiri: Target & Filter Penerima (5 Col) -->
                <div class="lg:col-span-5 space-y-5 bg-slate-50/70 p-5 sm:p-6 rounded-xl border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i class="ti ti-target-arrow text-blue-600 text-sm"></i>
                        Target Sasaran Penerima
                    </h3>

                    <!-- 1. Tujuan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Tujuan Pengiriman <span class="text-rose-500">*</span></label>
                        <select name="tujuan" x-model="tujuan" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs">
                            <option value="siswa_per_kelas">👨‍🎓 Siswa / Orang Tua per Kelas</option>
                            <option value="guru">👨‍🏫 Seluruh Guru &amp; Karyawan</option>
                        </select>
                    </div>

                    <!-- Target Siswa Filters -->
                    <div x-show="tujuan === 'siswa_per_kelas'" class="space-y-4 pt-2">
                        <!-- 2. Departemen -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700">Departemen / Unit <span class="text-rose-500">*</span></label>
                            <select name="departemen_id" x-model="departemenId" @change="onDepartemenChange()" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs">
                                @foreach($departemens as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->kode_departemen ? $dep->kode_departemen . ' - ' : '' }}{{ $dep->nama_departemen }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. Tingkat -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700">Tingkat Kelas <span class="text-rose-500">*</span></label>
                            <select name="tingkat" x-model="selectedTingkat" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-xs">
                                <template x-for="t in availableTingkats" :key="t">
                                    <option :value="t" x-text="'Tingkat ' + t"></option>
                                </template>
                                <option value="" x-show="availableTingkats.length === 0">-- Belum ada kelas terdaftar --</option>
                            </select>
                        </div>

                        <!-- 4. Daftar Rombel (Checkboxes) -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700">Pilih Sub-Kelas / Rombel <span class="text-rose-500">*</span></label>
                                <span class="text-[11px] font-bold text-blue-600" x-text="selectedKelas.length + ' dipilih'"></span>
                            </div>
                            
                            <div class="border border-slate-300 rounded-xl p-3 bg-white max-h-56 overflow-y-auto space-y-1.5 shadow-inner">
                                <div class="pb-2 mb-2 border-b border-slate-100 flex items-center justify-between">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-blue-600 select-none">
                                        <input type="checkbox" @change="toggleSelectAll($event)" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                                        <span>Pilih Semua Rombel</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="filteredKelas.length + ' rombel tersedia'"></span>
                                </div>

                                <template x-for="k in filteredKelas" :key="k.id">
                                    <label class="flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-100 cursor-pointer transition-all select-none">
                                        <div class="flex items-center gap-2.5">
                                            <input type="checkbox" name="kelas_ids[]" :value="k.id" x-model="selectedKelas" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                                            <span class="text-xs font-semibold text-slate-800" x-text="k.nama_rombel"></span>
                                        </div>
                                    </label>
                                </template>
                                
                                <div x-show="filteredKelas.length === 0" class="text-xs text-slate-400 text-center py-4">
                                    Tidak ada kelas ditemukan pada departemen &amp; tingkat ini.
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400">Pesan otomatis terkirim ke WhatsApp seluruh nomor wali murid yang terdaftar di kelas terpilih.</p>
                        </div>
                    </div>

                    <!-- Nomor Manual Tambahan -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-200">
                        <label class="block text-xs font-bold text-slate-700">Nomor Manual Tambahan (Opsional)</label>
                        <input type="text" name="manual_number" placeholder="Contoh: 081234567890"
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-xs">
                    </div>
                </div>

                <!-- Kolom Kanan: Pesan & Aksi Kirim (7 Col) -->
                <div class="lg:col-span-7 space-y-5">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                <i class="ti ti-message text-blue-600 text-sm"></i>
                                Isi Pesan Siaran / Broadcast <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-xs font-mono font-bold" :class="charCount > 300 ? 'text-amber-600' : 'text-slate-400'">
                                <span x-text="charCount">0</span> / 500 Karakter
                            </span>
                        </div>
                        
                        <textarea name="pesan" rows="9" x-model="pesanText" @input="updateCharCount()" maxlength="500" required
                                  placeholder="Tuliskan pengumuman atau informasi sekolah yang ingin disampaikan ke penerima..."
                                  class="w-full p-4 text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 leading-relaxed shadow-xs transition-all"></textarea>
                    </div>

                    <!-- Saluran Pengiriman & Tombol Submit -->
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <span class="text-xs font-bold text-slate-700">Saluran Pengiriman:</span>
                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-700 select-none">
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs hover:border-emerald-300">
                                    <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                                        <i class="ti ti-brand-whatsapp text-sm"></i>
                                        WhatsApp
                                    </span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs hover:border-blue-300">
                                    <input type="checkbox" name="channel_telegram" value="1" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                                    <span class="text-blue-700 font-bold flex items-center gap-1">
                                        <i class="ti ti-brand-telegram text-sm"></i>
                                        Telegram
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex justify-end">
                            <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <i class="ti ti-send text-base"></i>
                                <span>Kirim Pesan Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- TAB 2: LAPORAN REKAP PRESENSI OTOMATIS -->
    <div x-show="activeTab === 'laporan'" x-transition class="space-y-6">

    <!-- Filter Kelas & Tanggal -->
    <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
        <form method="GET" action="{{ route('whatsapp-broadcast.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="filter_tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Tanggal Presensi</label>
                <input type="date" name="tanggal" id="filter_tanggal" value="{{ $tanggal }}" x-model="tanggal" @change="refreshPreview()"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
            </div>

            <div>
                <label for="filter_kelas" class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Laporan Kelas</label>
                <select name="kelas_id" id="filter_kelas" x-model="kelasId" @change="refreshPreview()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="all" {{ $kelasId == 'all' ? 'selected' : '' }}>📌 Semua Kelas (Rekapitulasi Global)</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                            Kelas {{ $k->tingkat }} {{ $k->nama_rombel }} {{ $k->waliKelas ? '— Wali: ' . $k->waliKelas->nama_lengkap : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-sm font-semibold transition-all shadow-sm">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Kartu -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm text-center">
            <span class="text-xs text-slate-500 font-medium">Total Siswa</span>
            <p class="text-xl font-bold text-slate-800 mt-1" x-text="stats.total">{{ $reportData['stats']['total'] }}</p>
        </div>
        <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 shadow-sm text-center">
            <span class="text-xs text-emerald-600 font-medium">Hadir</span>
            <p class="text-xl font-bold text-emerald-700 mt-1" x-text="stats.hadir">{{ $reportData['stats']['hadir'] }}</p>
        </div>
        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 shadow-sm text-center">
            <span class="text-xs text-amber-600 font-medium">Terlambat</span>
            <p class="text-xl font-bold text-amber-700 mt-1" x-text="stats.terlambat">{{ $reportData['stats']['terlambat'] }}</p>
        </div>
        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 shadow-sm text-center">
            <span class="text-xs text-blue-600 font-medium">Sakit</span>
            <p class="text-xl font-bold text-blue-700 mt-1" x-text="stats.sakit">{{ $reportData['stats']['sakit'] }}</p>
        </div>
        <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 shadow-sm text-center">
            <span class="text-xs text-purple-600 font-medium">Izin</span>
            <p class="text-xl font-bold text-purple-700 mt-1" x-text="stats.izin">{{ $reportData['stats']['izin'] }}</p>
        </div>
        <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100 shadow-sm text-center">
            <span class="text-xs text-rose-600 font-medium">Alpa</span>
            <p class="text-xl font-bold text-rose-700 mt-1" x-text="stats.alpa">{{ $reportData['stats']['alpa'] }}</p>
        </div>
    </div>

    <!-- Form Kirim Pesan -->
    <form action="{{ route('whatsapp-broadcast.send') }}" method="POST" @submit="confirmSend($event)">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Kolom Kiri: Preview & Edit Pesan -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
                    <div class="flex items-center justify-between mb-3">
                        <label for="message_content" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span>💬</span> Preview &amp; Edit Pesan WhatsApp
                        </label>
                        <span class="text-xs text-slate-400">Pesan dapat disesuaikan manual</span>
                    </div>

                    <textarea name="message" id="message_content" rows="14" x-model="messageContent"
                              class="w-full p-4 font-mono text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400/40 text-slate-700 transition-all leading-relaxed"
                              placeholder="Menyiapkan pesan laporan..."></textarea>

                    <div class="mt-4 space-y-2">
                        <label for="custom_note" class="block text-xs font-semibold text-slate-700">Catatan Tambahan (Opsional)</label>
                        <input type="text" id="custom_note" x-model="customNote" @input="refreshPreview()"
                               placeholder="Contoh: Bagi siswa yang sakit harap segera mengonfirmasi surat dokter."
                               class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pilih Penerima (Kepala Sekolah, Wali Kelas, Daftar Guru) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100 flex flex-col h-full">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Target Penerima WhatsApp</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pilih siapa saja yang akan menerima laporan ini.</p>
                        </div>
                    </div>

                    <!-- Shortcut Cepat: Kepala Sekolah & Wali Kelas -->
                    <div class="space-y-2.5 mb-4">
                        <!-- Kepala Sekolah -->
                        @php
                            $kepsekPhone = $lembaga->no_telp ?? ($lembaga->kepalaSekolah->no_hp ?? '');
                            $kepsekNama = $lembaga->nama_kepala ?? ($lembaga->kepalaSekolah->nama_lengkap ?? 'Kepala Sekolah');
                        @endphp
                        <label class="flex items-center justify-between p-3 rounded-xl border border-sky-200 bg-sky-50/60 hover:bg-sky-50 cursor-pointer transition-all">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="recipients[]" value="{{ $kepsekPhone }}"
                                       @checked(!empty($kepsekPhone))
                                       class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">👑 Kepala Sekolah: {{ $kepsekNama }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $kepsekPhone ?: '(Nomor WA belum diisi di Pengaturan Lembaga)' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full">Kepsek</span>
                        </label>

                        <!-- Wali Kelas (jika kelas spesifik dipilih) -->
                        @if($reportData['targetKelas'] && $reportData['targetKelas']->waliKelas)
                            @php
                                $wk = $reportData['targetKelas']->waliKelas;
                            @endphp
                            <label class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-50 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="recipients[]" value="{{ $wk->no_hp }}"
                                           @checked(!empty($wk->no_hp))
                                           class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block">🎓 Wali Kelas: {{ $wk->nama_lengkap }}</span>
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $wk->no_hp ?: '(Belum ada nomor HP)' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Wali Kelas</span>
                            </label>
                        @endif
                    </div>

                    <!-- Daftar Seluruh Guru dengan Checkbox Select All -->
                    <div class="flex-1 flex flex-col">
                        <div class="flex items-center justify-between py-2 border-y border-slate-100 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" @change="toggleSelectAllGurus($event)"
                                       class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-xs font-bold text-slate-700">Centang Semua Guru ({{ count($gurus) }})</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Pilih perorangan di bawah</span>
                        </div>

                        <!-- Daftar Guru Scrollable -->
                        <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                            @forelse($gurus as $g)
                                <label class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 cursor-pointer transition-all">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox" name="recipients[]" value="{{ $g->no_hp }}"
                                               class="guru-checkbox rounded text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5"
                                               {{ empty($g->no_hp) ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-xs font-medium text-slate-800 block leading-tight">{{ $g->nama_lengkap }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                {{ $g->no_hp ?: '❌ No HP belum diisi' }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada data guru terdaftar.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Nomor Manual / Kustom Tambahan -->
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5">
                        <label for="manual_number" class="block text-xs font-semibold text-slate-700">Kirim ke Nomor Manual Tambahan</label>
                        <input type="text" name="recipients[]" id="manual_number" placeholder="Contoh: 081234567890 (Opsional)"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                    </div>

                    <!-- Tombol Eksekusi Kirim -->
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Kirimkan Pesan WhatsApp Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
    </div> <!-- Tutup Tab 2 Laporan -->

    <!-- TAB 3: MONITORING & PERINGATAN KETIDAKHADIRAN GURU -->
    <div x-show="activeTab === 'peringatan'" x-transition class="space-y-6">
        <!-- Banner Info Ambang Batas -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-amber-500 text-white shadow-xs">
                        <i class="ti ti-bell-ringing text-lg"></i>
                    </span>
                    <h3 class="text-base font-bold text-amber-950">Monitoring Kehadiran &amp; Peringatan Otomatis Guru</h3>
                </div>
                <p class="text-xs text-amber-800 leading-relaxed max-w-2xl">
                    Sistem mendeteksi akumulasi ketidakhadiran (Alpa) guru dalam 30 hari terakhir. Jika mencapai ambang batas 
                    <span class="font-bold underline decoration-amber-500">{{ $batasHari }} hari</span>, sistem secara otomatis menjadwalkan notifikasi WhatsApp setiap hari pukul 18:00, atau Anda dapat mengirimkannya secara langsung melalui tombol di bawah.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('pengaturan-lembaga.index') }}" class="px-4 py-2.5 bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                    <i class="ti ti-settings text-sm"></i>
                    <span>Atur Template &amp; Ambang Batas</span>
                </a>
            </div>
        </div>

        <!-- Tabel Monitoring Guru -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="ti ti-users text-blue-600"></i>
                        Daftar Guru &amp; Akumulasi Alpa
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh guru dengan status akumulasi alpa dan riwayat pengiriman peringatan.</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                        Melebihi Batas (≥ {{ $batasHari }} Hari)
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                        Aman
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Guru</th>
                            <th class="py-3.5 px-4">Kontak WhatsApp</th>
                            <th class="py-3.5 px-4 text-center">Akumulasi Alpa</th>
                            <th class="py-3.5 px-4">Status Ambang Batas</th>
                            <th class="py-3.5 px-4">Peringatan Terakhir</th>
                            <th class="py-3.5 px-4 text-center">Aksi Langsung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($monitoringGurus as $g)
                            <tr class="hover:bg-slate-50/70 transition-colors {{ $g->is_over_limit ? 'bg-amber-50/30' : '' }}">
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $g->is_over_limit ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ strtoupper(substr($g->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $g->nama_lengkap }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $g->nip ?: 'NIP: -' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    @if($g->no_hp)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                            <i class="ti ti-brand-whatsapp text-sm"></i>
                                            {{ $g->no_hp }}
                                        </span>
                                    @else
                                        <span class="text-rose-500 italic text-[11px]">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $g->is_over_limit ? 'bg-rose-100 text-rose-800' : ($g->total_alpa > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $g->total_alpa }} Hari
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($g->is_over_limit)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="ti ti-alert-triangle text-xs"></i> Melebihi Batas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="ti ti-check text-xs"></i> Dalam Batas Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($g->terakhir_kirim)
                                        <div class="text-xs font-semibold text-slate-700">{{ $g->terakhir_kirim }}</div>
                                        <span class="text-[10px] {{ $g->status_kirim === 'Terkirim' ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">
                                            Status: {{ $g->status_kirim }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum pernah dikirim</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('whatsapp-broadcast.sendPeringatanGuru') }}" method="POST" onsubmit="return confirm('Kirimkan pesan peringatan WhatsApp sekarang ke {{ addslashes($g->nama_lengkap) }}?');">
                                        @csrf
                                        <input type="hidden" name="guru_id" value="{{ $g->id }}">
                                        <button type="submit" 
                                                {{ empty($g->no_hp) ? 'disabled' : '' }}
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs {{ empty($g->no_hp) ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : ($g->is_over_limit ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700') }}">
                                            <i class="ti ti-send text-xs"></i>
                                            <span>Kirim Peringatan</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada data guru terdaftar dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Peringatan yang Pernah Terkirim -->
        @if($riwayatPeringatan->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="ti ti-history text-blue-600"></i>
                    Riwayat Pengiriman Peringatan Terakhir
                </h4>

                <div class="space-y-3">
                    @foreach($riwayatPeringatan as $r)
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <span>{{ $r->guru->nama_lengkap ?? 'Guru' }}</span>
                                    <span class="font-normal text-slate-400">({{ $r->no_hp }})</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $r->status_kirim === 'Terkirim' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $r->status_kirim }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-mono line-clamp-1">
                                    {{ Str::limit($r->pesan, 100) }}
                                </p>
                            </div>
                            <div class="text-[11px] text-slate-400 shrink-0">
                                {{ \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y, H:i') }} WIB
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function whatsappBroadcast() {
    return {
        tanggal: '{{ $tanggal }}',
        kelasId: '{{ $kelasId }}',
        customNote: '',
        messageContent: `{!! addslashes(app(\App\Http\Controllers\WhatsAppBroadcastController::class)->buildWhatsAppMessage($reportData)) !!}`,
        stats: {
            total: {{ $reportData['stats']['total'] }},
            hadir: {{ $reportData['stats']['hadir'] }},
            terlambat: {{ $reportData['stats']['terlambat'] }},
            sakit: {{ $reportData['stats']['sakit'] }},
            izin: {{ $reportData['stats']['izin'] }},
            alpa: {{ $reportData['stats']['alpa'] }}
        },
        toggleSelectAllGurus(e) {
            const isChecked = e.target.checked;
            document.querySelectorAll('.guru-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = isChecked;
            });
        },
        refreshPreview() {
            fetch(`{{ route('whatsapp-broadcast.preview') }}?tanggal=${this.tanggal}&kelas_id=${this.kelasId}&custom_note=${encodeURIComponent(this.customNote)}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.messageContent = res.message;
                        this.stats = res.stats;
                    }
                })
                .catch(e => console.error(e));
        },
        confirmSend(e) {
            const checkedCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
            const manualVal = document.getElementById('manual_number')?.value.trim();
            if (checkedCount === 0 && !manualVal) {
                alert('Silakan pilih minimal 1 penerima pesan WhatsApp!');
                e.preventDefault();
                return false;
            }
            if (!confirm('Apakah Anda yakin ingin mengirim laporan presensi ini ke seluruh penerima terpilih via WhatsApp?')) {
                e.preventDefault();
                return false;
            }
        }
    }
}
</script>
@endsection
