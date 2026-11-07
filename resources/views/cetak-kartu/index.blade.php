@extends('layouts.app')

@section('title', 'Cetak Kartu ID')

@section('content')
    <div class="p-4 sm:p-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                    <div class="p-2.5 bg-blue-500/10 rounded-xl">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                    </div>
                    Cetak Kartu ID
                </h1>
                <p class="mt-2 text-slate-500 font-medium">Cetak kartu identitas untuk siswa dan guru.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6 md:p-8" x-data="cetakKartu()">
            <form action="{{ route('cetak-kartu.print') }}" method="POST" target="_blank">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipe -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Pengguna</label>
                        <select name="type" x-model="type" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>

                    <!-- Filter Siswa -->
                    <div x-show="type === 'siswa'" style="display: none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mode Cetak (Siswa)</label>
                        <select name="filter" x-model="filterSiswa" :disabled="type !== 'siswa'" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                            <option value="kelas">Per Kelas</option>
                            <option value="individu">Per Individu</option>
                        </select>
                    </div>

                    <!-- Filter Guru -->
                    <div x-show="type === 'guru'" style="display: none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mode Cetak (Guru)</label>
                        <select name="filter" x-model="filterGuru" :disabled="type !== 'guru'" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                            <option value="semua">Semua Guru</option>
                            <option value="individu">Per Individu</option>
                        </select>
                    </div>

                    <!-- Pilihan Kelas (Siswa - Kelas & Individu) -->
                    <div x-show="type === 'siswa' && (filterSiswa === 'kelas' || filterSiswa === 'individu')" style="display: none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                        <select name="kelas_id" x-model="kelasId" @change="fetchSiswa" :disabled="type !== 'siswa'" :required="type === 'siswa' && (filterSiswa === 'kelas' || filterSiswa === 'individu')" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->tingkat }} {{ $kelas->nama_rombel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilihan Siswa (Siswa - Individu) -->
                    <div x-show="type === 'siswa' && filterSiswa === 'individu'" style="display: none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Siswa</label>
                        <select name="siswa_id" x-model="siswaId" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all" :disabled="loadingSiswa || siswas.length === 0" :required="type === 'siswa' && filterSiswa === 'individu'">
                            <option value="">-- Pilih Siswa --</option>
                            <template x-for="s in siswas" :key="s.id">
                                <option :value="s.id" x-text="s.nama_lengkap"></option>
                            </template>
                        </select>
                        <div x-show="loadingSiswa" class="text-xs text-blue-500 mt-1">Memuat data siswa...</div>
                    </div>

                    <!-- Pilihan Guru (Guru - Individu) -->
                    <div x-show="type === 'guru' && filterGuru === 'individu'" style="display: none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Guru</label>
                        <select name="guru_id" :disabled="type !== 'guru' || filterGuru !== 'individu'" :required="type === 'guru' && filterGuru === 'individu'" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium shadow-sm shadow-blue-500/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Tampilkan Kartu ID
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cetakKartu', () => ({
                type: 'siswa',
                filterSiswa: 'kelas',
                filterGuru: 'semua',
                kelasId: '',
                siswaId: '',
                siswas: [],
                loadingSiswa: false,

                fetchSiswa() {
                    if (!this.kelasId) {
                        this.siswas = [];
                        this.siswaId = '';
                        return;
                    }
                    this.loadingSiswa = true;
                    fetch(`/cetak-kartu/get-siswa/${this.kelasId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.siswas = data;
                            this.loadingSiswa = false;
                            this.siswaId = '';
                        })
                        .catch(err => {
                            console.error(err);
                            this.loadingSiswa = false;
                        });
                },

                canSubmit() {
                    if (this.type === 'siswa') {
                        if (this.filterSiswa === 'kelas' && !this.kelasId) return false;
                        if (this.filterSiswa === 'individu' && !this.siswaId) return false;
                    } else if (this.type === 'guru') {
                        // Untuk guru tidak masalah
                    }
                    return true;
                }
            }));
        });
    </script>
    @endpush
@endsection
