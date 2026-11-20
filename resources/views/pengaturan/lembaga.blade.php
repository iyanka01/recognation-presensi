@extends('layouts.app')

@section('title', 'Pengaturan Lembaga & Sistem')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'profil' }">
    <!-- Header Banner Modern Bernuansa Hijau Elegan -->
    <div class="bg-gradient-to-r from-emerald-700 via-teal-700 to-slate-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden border border-emerald-600/30">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-20 -bottom-16 w-52 h-52 bg-teal-300/15 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-900/40 backdrop-blur-md border border-emerald-400/30 text-xs font-semibold text-emerald-200">
                    <i class="ti ti-settings text-sm text-emerald-300"></i>
                    <span>Pusat Konfigurasi Sistem</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Pengaturan Lembaga &amp; Integrasi
                </h1>
                <p class="text-sm text-emerald-100/90 max-w-2xl leading-relaxed">
                    Kelola profil identitas sekolah/lembaga, gateway pesan WhatsApp &amp; Telegram, geolokasi GPS presensi, serta kredensial akun administrator.
                </p>
            </div>

            <!-- Status Singkat Gateway -->
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                @if(!empty($lembaga->wanesia_token) && ($lembaga->wanesia_active ?? true))
                    <div class="px-4 py-2 rounded-2xl bg-emerald-900/50 backdrop-blur-md border border-emerald-400/40 text-emerald-200 text-xs font-bold flex items-center gap-2 shadow-xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>WhatsApp Gateway Aktif</span>
                    </div>
                @else
                    <div class="px-4 py-2 rounded-2xl bg-amber-500/20 backdrop-blur-md border border-amber-400/30 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-xs">
                        <i class="ti ti-alert-triangle text-sm"></i>
                        <span>WhatsApp Belum Aktif</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab Navigasi Horizontal Berdesain Modern -->
        <div class="flex items-center gap-2 mt-8 pt-4 border-t border-white/10 overflow-x-auto no-scrollbar">
            <button type="button" @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-white text-slate-900 shadow-md font-bold' : 'bg-white/10 text-white/80 hover:bg-white/15 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="ti ti-school text-base"></i>
                <span>Profil Lembaga</span>
            </button>
            <button type="button" @click="activeTab = 'whatsapp'"
                    :class="activeTab === 'whatsapp' ? 'bg-white text-slate-900 shadow-md font-bold' : 'bg-white/10 text-white/80 hover:bg-white/15 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="ti ti-brand-whatsapp text-emerald-500 text-base"></i>
                <span>WhatsApp &amp; Peringatan Guru</span>
            </button>
            <button type="button" @click="activeTab = 'lokasi'"
                    :class="activeTab === 'lokasi' ? 'bg-white text-slate-900 shadow-md font-bold' : 'bg-white/10 text-white/80 hover:bg-white/15 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="ti ti-map-pin text-sky-500 text-base"></i>
                <span>Geofencing &amp; GPS</span>
            </button>
            <button type="button" @click="activeTab = 'telegram'"
                    :class="activeTab === 'telegram' ? 'bg-white text-slate-900 shadow-md font-bold' : 'bg-white/10 text-white/80 hover:bg-white/15 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="ti ti-brand-telegram text-blue-400 text-base"></i>
                <span>Bot Telegram</span>
            </button>
            <button type="button" @click="activeTab = 'akun'"
                    :class="activeTab === 'akun' ? 'bg-white text-slate-900 shadow-md font-bold' : 'bg-white/10 text-white/80 hover:bg-white/15 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="ti ti-user-shield text-base"></i>
                <span>Akun Administrator</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: DATA PROFIL LEMBAGA -->
    <div x-show="activeTab === 'profil'" x-transition>
        <form action="{{ route('pengaturan-lembaga.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="ti ti-building-community text-blue-600 text-lg"></i>
                        Informasi Utama Lembaga / Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Data identitas ini akan dicantumkan secara otomatis pada kop surat, laporan presensi, dan kartu identitas.</p>
                </div>

                <!-- Upload Logo & Pratinjau -->
                <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-28 h-28 shrink-0 bg-white rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shadow-xs relative group">
                        @if(isset($lembaga) && $lembaga->logo)
                            <img src="{{ Storage::url($lembaga->logo) }}" alt="Logo Lembaga" class="w-full h-full object-contain p-2">
                        @else
                            <i class="ti ti-photo text-4xl text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2 text-center sm:text-left">
                        <label for="logo" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Logo Resmi Lembaga</label>
                        <input type="file" name="logo" id="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[11px] text-slate-400">Direkomendasikan rasio 1:1 format PNG transparan atau JPG, ukuran maksimal 2MB.</p>
                        @error('logo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Nama Yayasan -->
                    <div class="space-y-1.5">
                        <label for="nama_yayasan" class="block text-xs font-bold text-slate-700">Nama Yayasan / Dinas Pendidikan</label>
                        <input type="text" name="nama_yayasan" id="nama_yayasan" value="{{ old('nama_yayasan', $lembaga->nama_yayasan ?? '') }}"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                               placeholder="Contoh: Yayasan Pendidikan Mayantara Indonesia">
                        @error('nama_yayasan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Akreditasi -->
                    <div class="space-y-1.5">
                        <label for="akreditasi" class="block text-xs font-bold text-slate-700">Akreditasi</label>
                        <select name="akreditasi" id="akreditasi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <option value="">-- Pilih Akreditasi --</option>
                            <option value="A" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'A' ? 'selected' : '' }}>A (Unggul)</option>
                            <option value="B" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'B' ? 'selected' : '' }}>B (Baik Sekali)</option>
                            <option value="C" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'C' ? 'selected' : '' }}>C (Baik)</option>
                            <option value="D" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'D' ? 'selected' : '' }}>D (Tidak Terakreditasi)</option>
                        </select>
                        @error('akreditasi') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Website -->
                    <div class="space-y-1.5">
                        <label for="website" class="block text-xs font-bold text-slate-700">Situs Web / Portal Resmi</label>
                        <input type="text" name="website" id="website" value="{{ old('website', $lembaga->website ?? '') }}"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono"
                               placeholder="Contoh: www.smkmayantara.sch.id">
                        @error('website') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1.5">
                    <label for="alamat" class="block text-xs font-bold text-slate-700">Alamat Lengkap</label>
                    <textarea name="alamat" id="alamat" rows="3"
                              class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                              placeholder="Tuliskan jalan, nomor gedung, kelurahan, kecamatan, kabupaten/kota, serta kode pos...">{{ old('alamat', $lembaga->alamat ?? '') }}</textarea>
                    @error('alamat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tempat / Kota -->
                <div class="space-y-1.5">
                    <label for="tempat" class="block text-xs font-bold text-slate-700">Tempat / Kota Penandatanganan Dokumen</label>
                    <input type="text" name="tempat" id="tempat" value="{{ old('tempat', $lembaga->tempat ?? '') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                           placeholder="Contoh: Wonogiri, Bondowoso, Malang">
                    <p class="text-[11px] text-slate-400">Kota yang akan tertera pada tanggal penandatanganan berkas laporan (misal: Wonogiri, 20 November 2026).</p>
                    @error('tempat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="ti ti-device-floppy text-base"></i>
                        <span>Simpan Profil Lembaga</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 2: WHATSAPP GATEWAY & PERINGATAN GURU -->
    <div x-show="activeTab === 'whatsapp'" x-transition>
        <form action="{{ route('pengaturan-lembaga.store') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Simpan nilai yang dibutuhkan agar tidak ter-reset -->
            <input type="hidden" name="nama_lembaga" value="{{ $lembaga->nama_lembaga ?? 'Sekolah' }}">
            <input type="hidden" name="nama_yayasan" value="{{ $lembaga->nama_yayasan ?? '' }}">
            <input type="hidden" name="akreditasi" value="{{ $lembaga->akreditasi ?? '' }}">
            <input type="hidden" name="website" value="{{ $lembaga->website ?? '' }}">
            <input type="hidden" name="alamat" value="{{ $lembaga->alamat ?? '' }}">
            <input type="hidden" name="tempat" value="{{ $lembaga->tempat ?? '' }}">

            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                <!-- Header Gateway Wanesia -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                            <i class="ti ti-brand-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Integrasi WhatsApp Gateway (Wanesia Express)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Menghubungkan sistem ke server WhatsApp untuk pengiriman laporan harian dan pengingat otomatis.</p>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2.5 cursor-pointer bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-xl border border-slate-200 transition-all select-none self-start sm:self-auto">
                        <input type="checkbox" name="wanesia_active" value="1" {{ old('wanesia_active', $lembaga->wanesia_active ?? true) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span class="text-xs font-bold text-slate-700">Aktifkan Layanan WhatsApp</span>
                    </label>
                </div>

                <!-- Input API Token -->
                <div class="space-y-1.5">
                    <label for="wanesia_token" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Wanesia Instance API Token <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="wanesia_token" id="wanesia_token" value="{{ old('wanesia_token', $lembaga->wanesia_token ?? '') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-inner"
                               placeholder="Tempelkan API Token dari dashboard akun Wanesia Anda di sini">
                    </div>
                    <p class="text-[11px] text-slate-400">Token diperoleh dari akun Wanesia Anda (<a href="https://wanesia.com" target="_blank" class="text-emerald-600 underline font-semibold">wanesia.com</a>).</p>
                    @error('wanesia_token') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Kotak Uji Coba Pengiriman -->
                <div class="p-5 bg-gradient-to-br from-emerald-50/70 to-teal-50/50 rounded-2xl border border-emerald-200/80 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-emerald-500 text-white rounded-lg text-xs font-bold">🧪</span>
                        <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Uji Coba Pengiriman WhatsApp Langsung</h4>
                    </div>
                    <p class="text-xs text-emerald-800 leading-relaxed">Kirimkan pesan uji coba ke nomor WhatsApp Anda sendiri untuk memastikan token terhubung aktif dengan server Wanesia.</p>
                    <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
                        <input type="text" id="test_wa_number" placeholder="Nomor WhatsApp (Contoh: 081234567890)"
                               class="flex-1 px-4 py-2.5 text-xs bg-white border border-emerald-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-slate-800 shadow-xs font-mono">
                        <button type="button" id="btn-test-wa" class="px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl flex items-center justify-center gap-2 transition-all shadow-md cursor-pointer shrink-0">
                            <i class="ti ti-send text-sm"></i>
                            <span>Kirim Pesan Tes</span>
                        </button>
                    </div>
                    <div id="test-wa-result" class="mt-2 text-xs hidden"></div>
                </div>

                <!-- Bagian Pengingat Otomatis Guru Tidak Hadir -->
                <div class="p-6 bg-gradient-to-br from-amber-50/80 to-orange-50/60 rounded-2xl border border-amber-200/80 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-amber-200/70 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl bg-amber-500 text-white text-base shadow-xs">
                                <i class="ti ti-bell-ringing"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-amber-950">Pengingat Ketidakhadiran Guru (Auto Alert WhatsApp)</h4>
                                <p class="text-xs text-amber-800 mt-0.5">Mengirimkan pesan WhatsApp otomatis jika guru tidak hadir selama akumulasi hari yang ditentukan.</p>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3.5 py-2 rounded-xl border border-amber-300 shadow-xs self-start sm:self-auto select-none">
                            <input type="checkbox" name="notif_peringatan_guru_active" value="1" {{ old('notif_peringatan_guru_active', $lembaga->notif_peringatan_guru_active ?? true) ? 'checked' : '' }} class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                            <span class="text-xs font-bold text-slate-700">Aktifkan Pengingat</span>
                        </label>
                    </div>

                    <!-- Batas Hari -->
                    <div class="space-y-1.5">
                        <label for="batas_absen_guru_hari" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Ambang Batas Tidak Hadir / Alpa
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="batas_absen_guru_hari" id="batas_absen_guru_hari"
                                   value="{{ old('batas_absen_guru_hari', $lembaga->batas_absen_guru_hari ?? 14) }}" min="1" max="100"
                                   class="w-32 px-4 py-2.5 text-xs font-bold text-center bg-white border border-amber-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs">
                            <span class="text-xs font-semibold text-slate-600">Hari Akumulasi (Rekomendasi: 14 hari / 2 minggu kerja)</span>
                        </div>
                    </div>

                    <!-- Template Pesan -->
                    @php
                        $defaultTemplate = "Halo Bapak/Ibu {nama_guru},\n\nKami menginformasikan dari Bagian Kepegawaian & Kurikulum {nama_yayasan}.\n\nBerdasarkan data presensi sistem, tercatat Bapak/Ibu belum melakukan presensi kehadiran selama {jumlah_hari} hari terakhir.\n\nJika terdapat kendala teknis pada aplikasi, perubahan jadwal, atau kondisi izin/sakit, mohon segera mengonfirmasikannya kepada pihak kurikulum atau Kepala Sekolah.\n\nTerima kasih atas perhatian dan kerjasamanya.\nSistem Presensi {nama_sekolah}";
                        $currentTemplate = old('template_peringatan_guru', $lembaga->template_peringatan_guru ?? $defaultTemplate);
                    @endphp
                    <div class="space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <label for="template_peringatan_guru" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Redaksi Pesan WhatsApp Peringatan
                            </label>
                            <span class="text-[11px] text-amber-800 font-mono font-bold">Variabel: {nama_guru}, {jumlah_hari}, {nama_yayasan}, {nama_sekolah}</span>
                        </div>
                        <textarea name="template_peringatan_guru" id="template_peringatan_guru" rows="7"
                                  class="w-full p-4 font-sans text-xs sm:text-sm bg-white border border-amber-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-500/30 text-slate-800 leading-relaxed shadow-xs">{{ $currentTemplate }}</textarea>
                        <p class="text-[11px] text-amber-800">Variabel dalam kurung kurawal akan otomatis digantikan sesuai nama guru dan akumulasi hari alpa saat terkirim.</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="ti ti-device-floppy text-base"></i>
                        <span>Simpan Konfigurasi WhatsApp</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 3: TITIK LOKASI GEOFENCING & GPS -->
    <div x-show="activeTab === 'lokasi'" x-transition>
        <form action="{{ route('pengaturan-lembaga.store') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Simpan nilai yang dibutuhkan agar tidak ter-reset -->
            <input type="hidden" name="nama_lembaga" value="{{ $lembaga->nama_lembaga ?? 'Sekolah' }}">
            <input type="hidden" name="nama_yayasan" value="{{ $lembaga->nama_yayasan ?? '' }}">
            <input type="hidden" name="akreditasi" value="{{ $lembaga->akreditasi ?? '' }}">
            <input type="hidden" name="website" value="{{ $lembaga->website ?? '' }}">
            <input type="hidden" name="alamat" value="{{ $lembaga->alamat ?? '' }}">
            <input type="hidden" name="tempat" value="{{ $lembaga->tempat ?? '' }}">

            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-sky-500 text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                            <i class="ti ti-map-2"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Koordinat Lokasi &amp; Geofencing Presensi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Presensi wajah guru di perangkat hanya diizinkan apabila berada dalam radius lokasi ini.</p>
                        </div>
                    </div>
                    <button type="button" id="btn-detect-gps" class="self-start sm:self-auto px-4 py-2 text-xs font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                        <i class="ti ti-current-location text-base text-sky-600"></i>
                        <span>Gunakan GPS Saya Saat Ini</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label for="latitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Latitude Sekolah</label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $lembaga->latitude ?? '') }}"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                               placeholder="-6.2088">
                        @error('latitude') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="longitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Longitude Sekolah</label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $lembaga->longitude ?? '') }}"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                               placeholder="106.8456">
                        @error('longitude') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="radius_meter" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Radius Toleransi Presensi</label>
                        <div class="relative">
                            <input type="number" name="radius_meter" id="radius_meter" min="10" max="50000"
                                   value="{{ old('radius_meter', $lembaga->radius_meter ?? 100) }}"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="100">
                            <span class="absolute right-4 top-2.5 text-xs text-slate-400 font-bold">Meter</span>
                        </div>
                        @error('radius_meter') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Preview Map Interaktif -->
                <div class="space-y-2">
                    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                        <div id="map-admin" style="height: 320px; width: 100%; z-index: 1;"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                        <i class="ti ti-info-circle text-sky-500"></i>
                        <span>Anda dapat menggeser pin lokasi atau klik langsung di peta untuk menetapkan koordinat sekolah secara presisi.</span>
                    </p>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-7 py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="ti ti-device-floppy text-base"></i>
                        <span>Simpan Lokasi Geofencing</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 4: INTEGRASI BOT TELEGRAM -->
    <div x-show="activeTab === 'telegram'" x-transition>
        <form action="{{ route('pengaturan-lembaga.store') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Simpan nilai yang dibutuhkan agar tidak ter-reset -->
            <input type="hidden" name="nama_lembaga" value="{{ $lembaga->nama_lembaga ?? 'Sekolah' }}">
            <input type="hidden" name="nama_yayasan" value="{{ $lembaga->nama_yayasan ?? '' }}">
            <input type="hidden" name="akreditasi" value="{{ $lembaga->akreditasi ?? '' }}">
            <input type="hidden" name="website" value="{{ $lembaga->website ?? '' }}">
            <input type="hidden" name="alamat" value="{{ $lembaga->alamat ?? '' }}">
            <input type="hidden" name="tempat" value="{{ $lembaga->tempat ?? '' }}">

            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                        <i class="ti ti-brand-telegram"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Integrasi Saluran Bot Telegram</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Token bot resmi Telegram untuk mengirim pemberitahuan presensi realtime ke channel atau chat.</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="telegram_bot_token" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Telegram Bot Token (Opsional)
                    </label>
                    <input type="text" name="telegram_bot_token" id="telegram_bot_token"
                           value="{{ old('telegram_bot_token', $lembaga->telegram_bot_token ?? '') }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-inner"
                           placeholder="Contoh: 123456789:ABCdefGHIjklMNOpqrSTUvwxYZ">
                    <p class="text-[11px] text-slate-400">Dapatkan token dengan membuat bot baru di Telegram melalui <strong>@BotFather</strong>.</p>
                    @error('telegram_bot_token') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="ti ti-device-floppy text-base"></i>
                        <span>Simpan Token Telegram</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 5: AKUN PENGGUNA ADMINISTRATOR -->
    <div x-show="activeTab === 'akun'" x-transition>
        <form action="{{ route('pengaturan-akun.update') }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                        <i class="ti ti-shield-lock"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Kredensial Akun Administrator</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola nama profil login, username, dan perbarui kata sandi akun administrator.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label for="nama_lengkap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('nama_lengkap') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Username Login <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('username') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Bagian Ganti Password -->
                <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ti ti-key text-blue-600 text-sm"></i>
                            Perbarui Kata Sandi Akun
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Biarkan kolom di bawah ini kosong jika Anda tidak ingin merubah kata sandi lama.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi Baru</label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                   placeholder="Minimal 8 karakter">
                            @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                   placeholder="Ulangi kata sandi baru">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="ti ti-device-floppy text-base"></i>
                        <span>Simpan Akun Administrator</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .leaflet-container { font-family: inherit; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = {{ !empty($lembaga->latitude) ? $lembaga->latitude : -6.2088 }};
    const defaultLng = {{ !empty($lembaga->longitude) ? $lembaga->longitude : 106.8456 }};
    const defaultRadius = parseInt(document.getElementById('radius_meter')?.value) || 100;

    let map = null;
    let marker = null;
    let circle = null;

    function initMap() {
        const mapEl = document.getElementById('map-admin');
        if (!mapEl || map) return;

        map = L.map('map-admin').setView([defaultLat, defaultLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        circle = L.circle([defaultLat, defaultLng], {
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.25,
            radius: defaultRadius
        }).addTo(map);

        function updateInputs(lat, lng) {
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            if (latEl) latEl.value = parseFloat(lat).toFixed(6);
            if (lngEl) lngEl.value = parseFloat(lng).toFixed(6);
        }

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateInputs(pos.lat, pos.lng);
            circle.setLatLng(pos);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        const radiusInput = document.getElementById('radius_meter');
        if (radiusInput) {
            radiusInput.addEventListener('input', function (e) {
                const r = parseInt(e.target.value) || 50;
                circle.setRadius(r);
            });
        }
    }

    initMap();

    // Trigger map invalidation saat tab lokasi dibuka
    window.addEventListener('click', function (e) {
        if (e.target.closest('button')?.innerText?.includes('Geofencing')) {
            setTimeout(() => {
                if (map) {
                    map.invalidateSize();
                } else {
                    initMap();
                }
            }, 100);
        }
    });

    const btnGps = document.getElementById('btn-detect-gps');
    if (btnGps) {
        btnGps.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolokasi GPS.');
                return;
            }
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Mendeteksi...';

            navigator.geolocation.getCurrentPosition(function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (!map) initMap();
                map.setView([lat, lng], 17);
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                document.getElementById('latitude').value = parseFloat(lat).toFixed(6);
                document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
                btn.disabled = false;
                btn.innerHTML = '✓ Lokasi Terdeteksi';
                setTimeout(() => {
                    btn.innerHTML = '<i class="ti ti-current-location text-base text-sky-600"></i><span>Gunakan GPS Saya Saat Ini</span>';
                }, 3000);
            }, function (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-current-location text-base text-sky-600"></i><span>Gunakan GPS Saya Saat Ini</span>';
                alert('Gagal mengambil lokasi GPS: ' + err.message + '. Pastikan izin lokasi diizinkan di browser.');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }

    // Uji Coba Kirim Pesan WhatsApp via AJAX
    const btnTestWa = document.getElementById('btn-test-wa');
    if (btnTestWa) {
        btnTestWa.addEventListener('click', function () {
            const numInput = document.getElementById('test_wa_number');
            const tokenInput = document.getElementById('wanesia_token');
            const resultBox = document.getElementById('test-wa-result');
            const number = numInput ? numInput.value.trim() : '';
            const token = tokenInput ? tokenInput.value.trim() : '';

            if (!number) {
                alert('Silakan masukkan nomor WhatsApp untuk uji coba.');
                if (numInput) numInput.focus();
                return;
            }

            btnTestWa.disabled = true;
            btnTestWa.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Mengirim...';
            resultBox.classList.add('hidden');

            fetch('{{ route("pengaturan-lembaga.test-wa") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    test_number: number,
                    test_token: token
                })
            })
            .then(res => res.json())
            .then(data => {
                btnTestWa.disabled = false;
                btnTestWa.innerHTML = '<i class="ti ti-send text-sm"></i><span>Kirim Pesan Tes</span>';
                resultBox.classList.remove('hidden');

                if (data.success) {
                    resultBox.className = 'mt-2 text-xs p-3.5 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 font-medium';
                    resultBox.innerHTML = '<strong>✅ Berhasil:</strong> ' + data.message + ' Silakan cek WhatsApp Anda!';
                } else {
                    resultBox.className = 'mt-2 text-xs p-3.5 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 font-medium';
                    resultBox.innerHTML = '<strong>❌ Gagal:</strong> ' + data.message;
                }
            })
            .catch(err => {
                btnTestWa.disabled = false;
                btnTestWa.innerHTML = '<i class="ti ti-send text-sm"></i><span>Kirim Pesan Tes</span>';
                resultBox.classList.remove('hidden');
                resultBox.className = 'mt-2 text-xs p-3.5 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 font-medium';
                resultBox.innerHTML = '<strong>❌ Terjadi kesalahan:</strong> ' + err.message;
            });
        });
    }
});
</script>
@endpush
