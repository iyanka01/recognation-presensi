@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('siswa.index') }}" class="p-2 text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Data Siswa</h2>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
            <ul class="list-disc pl-4 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data" class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="bg-slate-50/50 px-8 py-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Informasi Pribadi</h3>
                <p class="text-sm text-slate-500">Perbarui data profil siswa di bawah ini.</p>
            </div>
            @if($siswa->foto_siswa)
            <img src="{{ asset('storage/' . $siswa->foto_siswa) }}" alt="Foto Lama" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md">
            @endif
        </div>

        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIS (Nomor Induk Siswa) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nis" value="{{ old('nis', $siswa->nis) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NISN (Nasional)</label>
                    <input type="text" name="nisn" value="{{ old('nisn', $siswa->nisn) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Agama <span class="text-rose-500">*</span></label>
                    <select name="agama" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        <option value="">-- Pilih Agama --</option>
                        <option value="Islam" {{ old('agama', $siswa->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('agama', $siswa->agama) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('agama', $siswa->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('agama', $siswa->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('agama', $siswa->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ old('agama', $siswa->agama) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tempat Lahir <span class="text-rose-500">*</span></label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $tingkat => $kelasGroup)
                            <optgroup label="Tingkat {{ $tingkat }}">
                                @foreach($kelasGroup as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                                        {{ $tingkat }} - {{ $k->nama_rombel }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No HP Orang Tua</label>
                    <input type="text" name="no_hp_wali" value="{{ old('no_hp_wali', $siswa->no_hp_wali) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                </div>

                <div x-data="telegramModal('{{ old('telegram_chat_id', $siswa->telegram_chat_id) }}')">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telegram Chat ID Wali Murid</label>
                    <div class="flex gap-2">
                        <input type="text" name="telegram_chat_id" x-model="chatId" placeholder="Contoh: 123456789" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        <button type="button" @click="openModal()" class="px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            Get Data
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Chat ID unik milik wali murid untuk bot Telegram.</p>

                    <!-- Modal -->
                    <div x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center bg-sky-900/40 backdrop-blur-sm" x-cloak>
                        <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                            <div class="bg-sky-500 px-6 py-4 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    Get Data Telegram
                                </h3>
                                <button type="button" @click="open = false" class="text-sky-100 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            <div class="p-6">
                                <div x-show="loading" class="text-center py-4">
                                    <svg class="animate-spin h-8 w-8 text-sky-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-sm text-slate-500 mt-2">Mengambil data dari Telegram...</p>
                                </div>
                                
                                <div x-show="!loading">
                                    <template x-if="updates.length === 0">
                                        <p class="text-sm text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-200 mb-4">
                                            Belum ada pesan masuk ke Bot. Minta wali murid mengirim pesan ke Bot Telegram terlebih dahulu.
                                        </p>
                                    </template>
                                    <template x-if="updates.length > 0">
                                        <div class="space-y-4">
                                            <p class="text-sm text-slate-600">Pilih dari daftar pesan terakhir yang masuk ke Bot:</p>
                                            <select x-model="selectedId" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                                                <option value="">-- Pilih Pengirim --</option>
                                                <template x-for="item in updates" :key="item.chat_id">
                                                    <option :value="item.chat_id" x-text="`${item.name} (Pesan: ${item.text})`"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                                <button type="button" @click="applySelection()" :disabled="!selectedId" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    Kirim / Pilih
                                </button>
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 rounded-xl transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Siswa <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        <option value="Aktif" {{ old('status', $siswa->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Lulus" {{ old('status', $siswa->status) == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="Pindah" {{ old('status', $siswa->status) == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="Dikeluarkan" {{ old('status', $siswa->status) == 'Dikeluarkan' ? 'selected' : '' }}>Dikeluarkan</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Foto Siswa (Kosongkan jika tidak ingin diubah)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer" onclick="document.getElementById('file-upload').click()">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <span class="relative font-medium text-sky-600 hover:text-sky-500">
                                    <span>Upload foto baru</span>
                                    <input id="file-upload" name="foto_siswa" type="file" class="sr-only" accept="image/*">
                                </span>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-500">PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-sky-50/30 px-8 py-5 border-t border-sky-100 flex items-center justify-end gap-3">
            <a href="{{ route('siswa.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-xl transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-amber-300/40 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Perbarui Data Siswa
            </button>
        </div>
    </form>
</div>
@endsection
