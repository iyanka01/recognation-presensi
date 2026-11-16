@extends('layouts.app')

@section('title', 'Setting')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Data Profil Lembaga</h3>
                <p class="text-sm text-slate-500 mt-1">Informasi ini akan digunakan pada kop surat dan kartu identitas.</p>
            </div>
            <div class="p-3 bg-indigo-100 rounded-xl text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>

        <form action="{{ route('pengaturan-lembaga.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Logo -->
            <div class="mb-8 flex items-start gap-6">
                <div class="w-24 h-24 shrink-0 bg-slate-100 rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden">
                    @if(isset($lembaga) && $lembaga->logo)
                        <img src="{{ Storage::url($lembaga->logo) }}" alt="Logo Lembaga" class="w-full h-full object-contain p-2">
                    @else
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    @endif
                </div>
                <div class="flex-1 space-y-2">
                    <label for="logo" class="block text-sm font-medium text-slate-700">Logo Lembaga (Opsional)</label>
                    <input type="file" name="logo" id="logo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-xs text-slate-500">Format yang didukung: JPG, PNG, SVG. Maksimal 2MB.</p>
                    @error('logo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Yayasan -->
                <div class="space-y-2">
                    <label for="nama_yayasan" class="block text-sm font-medium text-slate-700">Nama Yayasan / Dinas</label>
                    <input type="text" name="nama_yayasan" id="nama_yayasan" value="{{ old('nama_yayasan', $lembaga->nama_yayasan ?? '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Misal: Yayasan Pendidikan Mayantara">
                    @error('nama_yayasan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Nama Lembaga -->
                <div class="space-y-2">
                    <label for="nama_lembaga" class="block text-sm font-medium text-slate-700">Nama Lembaga / Sekolah <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lembaga" id="nama_lembaga" value="{{ old('nama_lembaga', $lembaga->nama_lembaga ?? '') }}" required class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Misal: SMK Mayantara">
                    @error('nama_lembaga') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Akreditasi -->
                <div class="space-y-2">
                    <label for="akreditasi" class="block text-sm font-medium text-slate-700">Terakreditasi</label>
                    <select name="akreditasi" id="akreditasi" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        <option value="">-- Pilih Akreditasi --</option>
                        <option value="A" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'A' ? 'selected' : '' }}>A (Unggul)</option>
                        <option value="B" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'B' ? 'selected' : '' }}>B (Baik Sekali)</option>
                        <option value="C" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'C' ? 'selected' : '' }}>C (Baik)</option>
                        <option value="D" {{ old('akreditasi', $lembaga->akreditasi ?? '') == 'D' ? 'selected' : '' }}>D (Tidak Terakreditasi)</option>
                    </select>
                    @error('akreditasi') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Website -->
                <div class="space-y-2">
                    <label for="website" class="block text-sm font-medium text-slate-700">Website Lembaga</label>
                    <input type="text" name="website" id="website" value="{{ old('website', $lembaga->website ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Misal: www.smkmayantara.sch.id">
                    @error('website') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Kepala Sekolah (Dropdown dari Data Guru) -->
                <div class="space-y-2">
                    <label for="kepala_sekolah_id" class="block text-sm font-medium text-slate-700">Kepala Sekolah / Lembaga <span class="text-rose-500">*</span></label>
                    <select name="kepala_sekolah_id" id="kepala_sekolah_id" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        <option value="">-- Pilih dari Data Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" 
                                data-nama="{{ $g->nama_lengkap }}"
                                data-nip="{{ $g->nip ?? '' }}"
                                data-nohp="{{ $g->no_hp ?? '' }}"
                                {{ old('kepala_sekolah_id', $lembaga->kepala_sekolah_id ?? '') == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_lengkap }} {{ $g->nip ? '('.$g->nip.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400">Pilih dari daftar guru agar otomatis terhubung ke sistem &amp; laporan.</p>
                    @error('kepala_sekolah_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Nama Kepala (Teks Bebas / Otomatis) -->
                <div class="space-y-2">
                    <label for="nama_kepala" class="block text-sm font-medium text-slate-700">Nama Kepala Lembaga <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kepala" id="nama_kepala" value="{{ old('nama_kepala', $lembaga->nama_kepala ?? '') }}" required class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Nama Kepala Sekolah beserta gelar">
                    @error('nama_kepala') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- NIP -->
                <div class="space-y-2">
                    <label for="nip" class="block text-sm font-medium text-slate-700">NIP Kepala Lembaga</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $lembaga->nip ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Nomor Induk Pegawai (jika ada)">
                    @error('nip') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- No Telp -->
                <div class="space-y-2">
                    <label for="no_telp" class="block text-sm font-medium text-slate-700">No. WhatsApp / Telepon Kepala Sekolah</label>
                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $lembaga->no_telp ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Misal: 081234567890">
                    <p class="text-[11px] text-slate-400">Nomor ini akan menerima rekap dan laporan presensi via WhatsApp.</p>
                    @error('no_telp') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Alamat -->
            <div class="mt-6 space-y-2">
                <label for="alamat" class="block text-sm font-medium text-slate-700">Alamat Lengkap</label>
                <textarea name="alamat" id="alamat" rows="3" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Alamat lengkap lembaga beserta kecamatan, kabupaten/kota, dan kode pos">{{ old('alamat', $lembaga->alamat ?? '') }}</textarea>
                @error('alamat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Tempat / Kota -->
            <div class="mt-4 space-y-2">
                <label for="tempat" class="block text-sm font-medium text-slate-700">Tempat / Kota</label>
                <input type="text" name="tempat" id="tempat" value="{{ old('tempat', $lembaga->tempat ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Misal: Wonogiri, Bondowoso, Jakarta">
                <p class="text-xs text-slate-500">Nama kota/kabupaten yang akan muncul pada dokumen cetak (surat, laporan, dll).</p>
                @error('tempat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>


            <!-- Telegram Bot Token (Tetap dipertahankan!) -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    <h4 class="text-sm font-semibold text-slate-800">Integrasi Bot Telegram</h4>
                </div>
                <p class="text-xs text-slate-500 mb-4">Token bot telegram digunakan untuk mengirim notifikasi absensi secara otomatis ke akun Telegram. Dapatkan dari @BotFather.</p>
                
                <div class="space-y-2">
                    <label for="telegram_bot_token" class="block text-sm font-medium text-slate-700">Telegram Bot Token (Opsional)</label>
                    <input type="text" name="telegram_bot_token" id="telegram_bot_token" value="{{ old('telegram_bot_token', $lembaga->telegram_bot_token ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono" placeholder="Misal: 123456789:ABCdefGHIjklMNOpqrSTUvwxYZ">
                    @error('telegram_bot_token') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Integrasi WhatsApp Gateway (Wanesia) -->
            <div class="mt-6 pt-6 border-t border-slate-100 bg-emerald-50/50 -mx-6 px-6 py-6 border-y border-emerald-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Integrasi WhatsApp Gateway (Wanesia)</h4>
                            <p class="text-xs text-slate-500">Kirim notifikasi presensi langsung ke WhatsApp orang tua/guru dan broadcast laporan.</p>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer self-start sm:self-auto bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-sm">
                        <input type="checkbox" name="wanesia_active" value="1" {{ old('wanesia_active', $lembaga->wanesia_active ?? true) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span class="text-xs font-semibold text-slate-700">Aktifkan WhatsApp</span>
                    </label>
                </div>

                <div class="space-y-4 mt-3">
                    <div class="space-y-1.5">
                        <label for="wanesia_token" class="block text-xs font-semibold text-slate-700">Wanesia Instance API Token <span class="text-emerald-600">*</span></label>
                        <input type="text" name="wanesia_token" id="wanesia_token" value="{{ old('wanesia_token', $lembaga->wanesia_token ?? '') }}" class="w-full px-4 py-2.5 bg-white border border-emerald-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono transition-all" placeholder="Tempel API Token dari panel Wanesia Anda di sini">
                        <p class="text-[11px] text-slate-500">Dapatkan token dari akun Wanesia Anda. Cukup tempel token di atas dan simpan, pengiriman pesan WhatsApp akan langsung berjalan.</p>
                        @error('wanesia_token') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Uji Coba Kirim Pesan WhatsApp -->
                    <div class="p-4 bg-white rounded-xl border border-emerald-200">
                        <h5 class="text-xs font-semibold text-slate-800 mb-1 flex items-center gap-1.5">
                            <span>🧪</span> Tes Pengiriman Pesan WhatsApp (Uji Coba Langsung)
                        </h5>
                        <p class="text-[11px] text-slate-500 mb-3">Kirimkan pesan tes langsung ke nomor HP Anda untuk memastikan token berfungsi aktif.</p>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" id="test_wa_number" placeholder="Nomor WhatsApp (Contoh: 081234567890)" class="flex-1 px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <button type="button" id="btn-test-wa" class="px-4 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex items-center justify-center gap-1.5 transition-all shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Kirim Pesan Tes</span>
                            </button>
                        </div>
                        <div id="test-wa-result" class="mt-2 text-xs hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Lokasi Sekolah & Geofencing GPS -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Titik Lokasi Sekolah & Radius Presensi (Geofencing GPS)
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">Presensi wajah guru hanya dapat dilakukan jika berada di dalam radius toleransi dari koordinat ini.</p>
                    </div>
                    <button type="button" id="btn-detect-gps" class="self-start sm:self-auto px-3 py-1.5 text-xs font-semibold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-lg flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Gunakan GPS Saya Saat Ini
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="space-y-1.5">
                        <label for="latitude" class="block text-xs font-semibold text-slate-700">Latitude Sekolah</label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $lembaga->latitude ?? '') }}" class="w-full px-3.5 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 font-mono transition-all" placeholder="-6.2088">
                        @error('latitude') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="longitude" class="block text-xs font-semibold text-slate-700">Longitude Sekolah</label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $lembaga->longitude ?? '') }}" class="w-full px-3.5 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 font-mono transition-all" placeholder="106.8456">
                        @error('longitude') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="radius_meter" class="block text-xs font-semibold text-slate-700">Radius Toleransi (Meter)</label>
                        <div class="relative">
                            <input type="number" name="radius_meter" id="radius_meter" min="10" max="50000" value="{{ old('radius_meter', $lembaga->radius_meter ?? 100) }}" class="w-full px-3.5 py-2 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 font-semibold transition-all" placeholder="100">
                            <span class="absolute right-3.5 top-2 text-xs text-slate-400 font-medium">Meter</span>
                        </div>
                        @error('radius_meter') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Preview Map -->
                <div class="mt-4 rounded-2xl overflow-hidden border border-sky-200 shadow-inner bg-slate-100">
                    <div id="map-admin" style="height: 280px; width: 100%; z-index: 1;"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">💡 Tips: Anda dapat menggeser (drag) pin pada peta atau klik langsung pada peta untuk menentukan koordinat sekolah secara presisi.</p>
            </div>

            <div class="mt-8 flex items-center justify-end border-t border-slate-100 pt-6">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Profil Lembaga
                </button>
            </div>
        </form>
    </div>

    <!-- Akun Pengguna Section -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Akun Pengguna</h3>
                <p class="text-sm text-slate-500 mt-1">Ubah nama pengguna dan kata sandi Anda di sini.</p>
            </div>
            <div class="p-3 bg-indigo-100 rounded-xl text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
            </div>
        </div>

        <form action="{{ route('pengaturan-akun.update') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama -->
                <div class="space-y-2">
                    <label for="nama_lengkap" class="block text-sm font-medium text-slate-700">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    @error('nama_lengkap') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Username / Email -->
                <div class="space-y-2">
                    <label for="username" class="block text-sm font-medium text-slate-700">Username (Untuk Login) <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    @error('username') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <h4 class="text-sm font-semibold text-slate-800 mb-4">Ganti Kata Sandi</h4>
                <p class="text-xs text-slate-500 mb-6">Kosongkan kolom di bawah ini jika Anda tidak ingin mengganti kata sandi.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password Baru -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Minimal 8 karakter">
                        @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Ulangi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Ketik ulang kata sandi baru">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end border-t border-slate-100 pt-6">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Akun Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .leaflet-container { font-family: inherit; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = {{ !empty($lembaga->latitude) ? $lembaga->latitude : -6.2088 }};
    const defaultLng = {{ !empty($lembaga->longitude) ? $lembaga->longitude : 106.8456 }};
    const defaultRadius = parseInt(document.getElementById('radius_meter').value) || 100;

    const map = L.map('map-admin').setView([defaultLat, defaultLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    let circle = L.circle([defaultLat, defaultLng], {
        color: '#0284c7',
        fillColor: '#38bdf8',
        fillOpacity: 0.25,
        radius: defaultRadius
    }).addTo(map);

    function updateInputs(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(6);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
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

    document.getElementById('radius_meter').addEventListener('input', function (e) {
        const r = parseInt(e.target.value) || 50;
        circle.setRadius(r);
    });

    document.getElementById('btn-detect-gps').addEventListener('click', function () {
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
            map.setView([lat, lng], 17);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            updateInputs(lat, lng);
            btn.disabled = false;
            btn.innerHTML = '✓ Lokasi Terdeteksi';
            setTimeout(() => {
                btn.innerHTML = '<svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Gunakan GPS Saya Saat Ini';
            }, 3000);
        }, function (err) {
            btn.disabled = false;
            btn.innerHTML = 'Gunakan GPS Saya Saat Ini';
            alert('Gagal mengambil lokasi GPS: ' + err.message + '. Pastikan izin lokasi aktif.');
        }, { enableHighAccuracy: true, timeout: 10000 });
    });

    setTimeout(() => { map.invalidateSize(); }, 300);

    // Auto-fill Data Kepala Sekolah saat Guru dipilih
    const kepalaSekolahSelect = document.getElementById('kepala_sekolah_id');
    if (kepalaSekolahSelect) {
        kepalaSekolahSelect.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const nama = selectedOpt.getAttribute('data-nama');
                const nip = selectedOpt.getAttribute('data-nip');
                const nohp = selectedOpt.getAttribute('data-nohp');

                if (nama) document.getElementById('nama_kepala').value = nama;
                if (nip) document.getElementById('nip').value = nip;
                if (nohp) document.getElementById('no_telp').value = nohp;
            }
        });
    }

    // Uji Coba Kirim Pesan WhatsApp via AJAX
    const btnTestWa = document.getElementById('btn-test-wa');
    if (btnTestWa) {
        btnTestWa.addEventListener('click', function () {
            const numInput = document.getElementById('test_wa_number');
            const tokenInput = document.getElementById('wanesia_token');
            const resultBox = document.getElementById('test-wa-result');
            const number = numInput.value.trim();
            const token = tokenInput.value.trim();

            if (!number) {
                alert('Silakan masukkan nomor WhatsApp untuk uji coba.');
                numInput.focus();
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
                btnTestWa.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Kirim Pesan Tes';
                resultBox.classList.remove('hidden');

                if (data.success) {
                    resultBox.className = 'mt-2 text-xs p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300';
                    resultBox.innerHTML = '<strong>✅ Berhasil:</strong> ' + data.message + ' Silakan cek WhatsApp Anda!';
                } else {
                    resultBox.className = 'mt-2 text-xs p-3 rounded-lg bg-rose-100 text-rose-800 border border-rose-300';
                    resultBox.innerHTML = '<strong>❌ Gagal:</strong> ' + data.message;
                }
            })
            .catch(err => {
                btnTestWa.disabled = false;
                btnTestWa.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Kirim Pesan Tes';
                resultBox.classList.remove('hidden');
                resultBox.className = 'mt-2 text-xs p-3 rounded-lg bg-rose-100 text-rose-800 border border-rose-300';
                resultBox.innerHTML = '<strong>❌ Terjadi kesalahan:</strong> ' + err.message;
            });
        });
    }
});
</script>
@endpush
