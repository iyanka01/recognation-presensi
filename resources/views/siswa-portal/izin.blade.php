@extends('layouts.siswa')

@section('title', 'Pengajuan Izin / Sakit')
@section('back_url', route('siswa.dashboard'))

@section('content')
<div class="space-y-4" x-data="{ openForm: false }">

    {{-- Header Banner & Tombol Tambah --}}
    <div class="bg-gradient-to-r from-teal-500 to-sky-600 text-white rounded-3xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="font-extrabold text-base">Pengajuan Izin & Sakit</h3>
            <p class="text-xs text-teal-100 mt-0.5">Form izin ketidakhadiran oleh orang tua / wali murid</p>
        </div>
        <button type="button" @click="openForm = !openForm" class="px-3.5 py-2 bg-white text-teal-700 hover:bg-teal-50 font-bold text-xs rounded-xl shadow-sm transition-all active:scale-95 shrink-0 flex items-center gap-1.5">
            <span x-text="openForm ? '✕ Tutup' : '+ Ajukan Izin'"></span>
        </button>
    </div>

    {{-- Form Pengajuan Izin --}}
    <div x-show="openForm" x-cloak class="bg-white rounded-3xl p-5 border border-teal-100 shadow-md animate-fade-in">
        <h4 class="font-extrabold text-sm text-slate-800 mb-4 pb-2 border-b border-slate-100">
            Formulir Izin Ketidakhadiran Siswa
        </h4>

        <form action="{{ route('siswa.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf

            {{-- Jenis Izin --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Izin <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-teal-400 text-xs font-semibold">
                        <input type="radio" name="status" value="Sakit" required class="text-teal-600 focus:ring-teal-500">
                        <span>🤒 Sakit</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-teal-400 text-xs font-semibold">
                        <input type="radio" name="status" value="Izin" required class="text-teal-600 focus:ring-teal-500">
                        <span>📝 Izin Keperluan</span>
                    </label>
                </div>
                @error('status') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal Mulai & Selesai --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mulai Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-400/30">
                    @error('tanggal_mulai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-400/30">
                    @error('tanggal_selesai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Keterangan / Alasan --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Ketidakhadiran <span class="text-rose-500">*</span></label>
                <textarea name="keterangan" rows="3" required placeholder="Jelaskan alasan ketidakhadiran (contoh: Mengalami demam dan disarankan dokter istirahat)"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-400/30">{{ old('keterangan') }}</textarea>
                @error('keterangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Upload Bukti Surat Dokter / Surat Izin --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Surat (Opsional)</label>
                <input type="file" name="bukti" accept="image/jpeg,image/png,image/jpg"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG atau PNG (Maksimal 3MB)</p>
                @error('bukti') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-teal-500 to-sky-600 hover:from-teal-600 hover:to-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-[0.99]">
                Kirim Pengajuan Izin
            </button>
        </form>
    </div>

    {{-- Daftar Riwayat Izin --}}
    <div class="bg-white rounded-3xl p-5 border border-sky-100 shadow-sm">
        <h4 class="font-extrabold text-sm text-slate-800 mb-3 flex items-center justify-between">
            <span>Riwayat Izin & Sakit</span>
            <span class="text-xs font-semibold text-slate-400">{{ $riwayatIzin->count() }} Data</span>
        </h4>

        <div class="space-y-2.5">
            @forelse($riwayatIzin as $r)
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0
                        {{ $r->status == 'Sakit' ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                        {{ $r->status == 'Sakit' ? '🤒' : '📝' }}
                    </span>
                    <div>
                        <h5 class="font-bold text-xs sm:text-sm text-slate-800">
                            {{ Carbon\Carbon::parse($r->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </h5>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Status: <span class="font-semibold">{{ $r->status }}</span>
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[11px] font-bold rounded-xl {{ $r->status == 'Sakit' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                    {{ $r->status }}
                </span>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-xs text-slate-400">Belum ada riwayat perizinan atau sakit.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
