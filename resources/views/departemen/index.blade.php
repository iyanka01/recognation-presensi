@extends('layouts.app')

@section('title', 'Kelola Departemen / Instansi')

@section('content')
<div class="space-y-6" x-data="{
    openModal: false,
    isEdit: false,
    editData: {
        id: '',
        kode_departemen: '',
        nama_departemen: '',
        tingkatan: '',
        kepala_sekolah_id: '',
        status: 'Aktif',
        alamat: '',
        no_telp: '',
        keterangan: '',
        logo_url: ''
    },
    openCreate() {
        this.isEdit = false;
        this.editData = {
            id: '',
            kode_departemen: '',
            nama_departemen: '',
            tingkatan: '',
            kepala_sekolah_id: '',
            status: 'Aktif',
            alamat: '',
            no_telp: '',
            keterangan: '',
            logo_url: ''
        };
        this.openModal = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.editData = {
            id: item.id,
            kode_departemen: item.kode_departemen,
            nama_departemen: item.nama_departemen,
            tingkatan: item.tingkatan,
            kepala_sekolah_id: item.kepala_sekolah_id || '',
            status: item.status,
            alamat: item.alamat || '',
            no_telp: item.no_telp || '',
            keterangan: item.keterangan || '',
            logo_url: item.logo ? '{{ Storage::url('') }}' + item.logo : ''
        };
        this.openModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Master Departemen / Instansi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola unit sekolah (SMK, SMA, SMP, SD) dalam satu yayasan.</p>
        </div>
        <button type="button" @click="openCreate()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-2 transition-all shadow-sm">
            <i class="ti ti-plus text-base"></i>
            Tambah Departemen
        </button>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-[7px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">No / Kode</th>
                        <th class="py-3.5 px-4">Logo</th>
                        <th class="py-3.5 px-4">Nama Departemen</th>
                        <th class="py-3.5 px-4">Tingkatan</th>
                        <th class="py-3.5 px-4">Kepala Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Kelas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($departemens as $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-600">{{ $d->kode_departemen }}</td>
                            <td class="py-3.5 px-4">
                                @if($d->logo)
                                    <img src="{{ Storage::url($d->logo) }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                        <i class="ti ti-building text-base"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $d->nama_departemen }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-bold text-[10px] uppercase">
                                    {{ $d->tingkatan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($d->kepalaSekolah)
                                    <span class="font-semibold text-slate-800 block">{{ $d->kepalaSekolah->nama_lengkap }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">NIP: {{ $d->kepalaSekolah->nip ?? '-' }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                    {{ $d->kelas_count }} Rombel
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($d->status === 'Aktif')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 font-bold text-[10px]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($d) }})" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition-colors" title="Edit Departemen">
                                        <i class="ti ti-edit text-sm"></i>
                                    </button>
                                    <form action="{{ route('departemen.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors" title="Hapus Departemen">
                                            <i class="ti ti-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                <i class="ti ti-building-off text-3xl mb-2 block text-slate-300"></i>
                                Belum ada data departemen. Klik tombol "Tambah Departemen" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departemens->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $departemens->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit Departemen -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Departemen' : 'Tambah Departemen Baru'"></h3>
                    <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600"><i class="ti ti-x text-lg"></i></button>
                </div>

                <form :action="isEdit ? '{{ url('departemen') }}/' + editData.id : '{{ route('departemen.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- No / Kode Departemen -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No / Kode Departemen <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_departemen" x-model="editData.kode_departemen" placeholder="Contoh: 01 atau SMK" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>

                        <!-- Tingkatan -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tingkatan <span class="text-rose-500">*</span></label>
                            <input type="text" name="tingkatan" x-model="editData.tingkatan" placeholder="Contoh: SMK, SMA, SMP" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Nama Departemen -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Departemen / Instansi <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_departemen" x-model="editData.nama_departemen" placeholder="Contoh: SMK Lentera Bangsa" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <!-- Kepala Sekolah (Otomatis dari Guru) -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kepala Sekolah (Otomatis dari Guru)</label>
                        <select name="kepala_sekolah_id" x-model="editData.kepala_sekolah_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">-- Pilih Guru Sebagai Kepala Sekolah --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }} ({{ $g->nip ?? 'Tanpa NIP' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                        <!-- Upload Logo Unit -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Logo Unit Sekolah</label>
                            <input type="file" name="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="editData.status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
