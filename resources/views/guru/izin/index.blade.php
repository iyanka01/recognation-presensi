@extends('layouts.guru')

@section('title', 'Perizinan & Cuti Guru')
@section('back_url', route('dashboard'))

@section('content')
<div class="space-y-4 sm:space-y-5">

    <!-- Header Banner -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-indigo-200 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                    <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Layanan Mandiri Guru
                </span>
                <button type="button" onclick="openIzinModal()"
                        class="px-3.5 sm:px-4 py-1.5 sm:py-2 bg-white text-indigo-700 hover:bg-indigo-50 active:scale-95 font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Ajukan Baru</span>
                </button>
            </div>

            <h1 class="text-lg sm:text-xl font-black tracking-tight">Perizinan, Sakit &amp; Tugas Dinas</h1>
            <p class="text-xs text-indigo-100/90 mt-1">Ajukan permohonan izin, keterangan sakit, cuti, atau penugasan dinas luar dengan melampirkan dokumen resmi.</p>

            <!-- Summary Chips -->
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3 pt-3 border-t border-white/15">
                <div class="bg-white/10 rounded-xl p-2 text-center backdrop-blur-sm">
                    <p class="text-base sm:text-lg font-black">{{ $statBulanIni['Izin'] }}</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Izin</p>
                </div>
                <div class="bg-white/10 rounded-xl p-2 text-center backdrop-blur-sm">
                    <p class="text-base sm:text-lg font-black">{{ $statBulanIni['Sakit'] }}</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Sakit</p>
                </div>
                <div class="bg-white/10 rounded-xl p-2 text-center backdrop-blur-sm">
                    <p class="text-base sm:text-lg font-black">{{ $statBulanIni['Dinas Luar'] }}</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Dinas Luar</p>
                </div>
                <div class="bg-white/10 rounded-xl p-2 text-center backdrop-blur-sm hidden sm:block">
                    <p class="text-base sm:text-lg font-black">{{ $statBulanIni['Cuti'] }}</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Cuti</p>
                </div>
                <div class="bg-white/10 rounded-xl p-2 text-center backdrop-blur-sm hidden sm:block">
                    <p class="text-base sm:text-lg font-black">{{ $statBulanIni['Dispensasi'] }}</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Dispensasi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tab Bar -->
    <div class="overflow-x-auto no-scrollbar -mx-1 sm:mx-0 px-1 pb-1">
        <div class="flex items-center gap-1.5 sm:gap-2 min-w-max">
            <a href="{{ route('guru.izin.index') }}"
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl text-[11px] sm:text-xs font-bold transition-all {{ !$filterStatus ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white/90 text-slate-600 border border-sky-100 hover:bg-sky-50' }}">
                Semua Riwayat
            </a>
            @foreach($kategoriList as $kat)
            @php $isAktif = ($filterStatus === $kat); @endphp
            <a href="{{ route('guru.izin.index', ['status' => $kat]) }}"
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl text-[11px] sm:text-xs font-bold transition-all {{ $isAktif ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white/90 text-slate-600 border border-sky-100 hover:bg-sky-50' }}">
                {{ $kat }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Riwayat Pengajuan -->
    <div>
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h2 class="text-xs sm:text-sm font-extrabold text-slate-800">Riwayat Pengajuan {{ $filterStatus ? "($filterStatus)" : '' }}</h2>
            <span class="text-[11px] sm:text-xs font-semibold text-slate-400">{{ $riwayat->count() }} Data</span>
        </div>

        <div class="space-y-2.5 sm:space-y-3">
            @forelse($riwayat as $r)
            @php
                $badgeTheme = match($r->status) {
                    'Sakit' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'Dinas Luar' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'Cuti' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'Dispensasi' => 'bg-teal-100 text-teal-800 border-teal-200',
                    default => 'bg-sky-100 text-sky-800 border-sky-200',
                };
            @endphp
            <div class="bg-white/95 rounded-2xl p-3.5 sm:p-4 shadow-sm border border-sky-100 flex items-start justify-between gap-3 transition-all hover:shadow-md">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] sm:text-xs font-black border {{ $badgeTheme }}">
                            {{ $r->status }}
                        </span>
                        <span class="text-xs font-bold text-slate-700">
                            {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1.5 font-medium leading-relaxed">
                        {{ $r->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                    </p>
                </div>

                @if($r->bukti)
                <div class="shrink-0 self-center">
                    <a href="{{ Storage::url($r->bukti) }}" target="_blank"
                       class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 text-indigo-600 text-xs font-bold transition-colors border border-slate-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="hidden sm:inline">Lihat</span> Bukti
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white/80 rounded-2xl border border-dashed border-sky-200 p-8 sm:p-10 text-center">
                <div class="w-12 h-12 mx-auto mb-2.5 rounded-full bg-indigo-50 text-indigo-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-700">Belum Ada Riwayat</h3>
                <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Anda belum memiliki riwayat pengajuan perizinan atau cuti.</p>
                <button type="button" onclick="openIzinModal()" class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-all shadow-sm">
                    Buat Pengajuan Sekarang
                </button>
            </div>
            @endforelse
        </div>
    </div>

</div>

<!-- ======================== MODAL POPUP FORM PENGAJUAN ======================== -->
<div id="modal-pengajuan-izin" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop Blur -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeIzinModal()"></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all z-10 p-5 sm:p-6 border border-slate-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-slate-800">Form Pengajuan Permohonan</h3>
                        <p class="text-[10px] sm:text-xs text-slate-400">Pilih jenis permohonan dan isi keterangan lengkap.</p>
                    </div>
                </div>
                <button type="button" onclick="closeIzinModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('guru.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Pilihan Jenis: 5 Kategori Interaktif -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Permohonan <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @php
                        $opsi = [
                            ['key' => 'Izin',       'desc' => 'Urusan Pribadi', 'peer' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700'],
                            ['key' => 'Sakit',      'desc' => 'Surat Dokter',   'peer' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700'],
                            ['key' => 'Dinas Luar', 'desc' => 'Tugas/MGMP',    'peer' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700'],
                            ['key' => 'Cuti',       'desc' => 'Cuti Resmi',     'peer' => 'peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700'],
                            ['key' => 'Dispensasi', 'desc' => 'Penugasan Khusus','peer' => 'peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:text-teal-700'],
                        ];
                        @endphp
                        @foreach($opsi as $op)
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $op['key'] }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                            <div class="p-2.5 rounded-xl border-2 border-slate-200 text-center transition-all hover:border-slate-300 {{ $op['peer'] }}">
                                <span class="block text-xs font-black">{{ $op['key'] }}</span>
                                <span class="block text-[9px] text-slate-400 mt-0.5">{{ $op['desc'] }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Rentang Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Mulai Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400/30">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_selesai" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400/30">
                    </div>
                </div>

                <!-- Alasan / Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alasan / Keterangan Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="keterangan" rows="3" required placeholder="Jelaskan keperluan izin, kondisi sakit, atau tugas dinas luar..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400/30"></textarea>
                </div>

                <!-- Upload Dokumen / Foto Bukti -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                        Lampiran Surat Dokter / Surat Tugas <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-2xl p-3 sm:p-4 text-center bg-slate-50 transition-colors">
                        <input type="file" name="bukti" accept="image/*,.pdf" capture="environment" id="file-bukti" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this)">
                        <div class="flex flex-col items-center justify-center pointer-events-none">
                            <svg class="w-6 h-6 text-indigo-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p id="file-label" class="text-xs font-bold text-slate-700">Pilih / Jepret Foto Surat Keterangan</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400">JPG, PNG, atau PDF (Maksimal 5MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeIzinModal()"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-300 transition-all flex items-center gap-1.5 active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Kirim Permohonan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openIzinModal() {
    document.getElementById('modal-pengajuan-izin').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
function closeIzinModal() {
    document.getElementById('modal-pengajuan-izin').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
function previewFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('file-label').innerText = 'File: ' + input.files[0].name;
    }
}

// Auto open if query parameter ?open=1
@if($openModal)
document.addEventListener('DOMContentLoaded', function() {
    openIzinModal();
});
@endif
</script>
@endsection
