@extends('layouts.app')

@section('title', 'Kelola Tahun Ajaran')

@section('content')
<div class="space-y-6" x-data="{
    openModal: false,
    isEdit: false,
    editData: {
        id: '',
        tahun_ajaran: '',
        tanggal_mulai: '',
        tanggal_akhir: '',
        status: 'Aktif'
    },
    openCreate() {
        this.isEdit = false;
        this.editData = {
            id: '',
            tahun_ajaran: '',
            tanggal_mulai: '',
            tanggal_akhir: '',
            status: 'Aktif'
        };
        this.openModal = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.editData = {
            id: item.id,
            tahun_ajaran: item.tahun_ajaran,
            tanggal_mulai: item.tanggal_mulai ? item.tanggal_mulai.substring(0, 10) : '',
            tanggal_akhir: item.tanggal_akhir ? item.tanggal_akhir.substring(0, 10) : '',
            status: item.status
        };
        this.openModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Master Tahun Ajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola periode tahun akademik presensi dan kegiatan sekolah.</p>
        </div>
        <button type="button" @click="openCreate()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-2 transition-all shadow-sm">
            <i class="ti ti-plus text-base"></i>
            Tambah Tahun Ajaran
        </button>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-[7px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Tahun Ajaran</th>
                        <th class="py-3.5 px-4">Tanggal Mulai</th>
                        <th class="py-3.5 px-4">Tanggal Akhir</th>
                        <th class="py-3.5 px-4 text-center">Kelas Terdaftar</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($tahunAjarans as $ta)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                                <i class="ti ti-calendar text-blue-600 text-base"></i>
                                <span>{{ $ta->tahun_ajaran }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                {{ $ta->tanggal_mulai ? $ta->tanggal_mulai->locale('id')->isoFormat('D MMMM Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                {{ $ta->tanggal_akhir ? $ta->tanggal_akhir->locale('id')->isoFormat('D MMMM Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                    {{ $ta->kelas_count }} Rombel
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($ta->status === 'Aktif')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200 flex items-center justify-center gap-1 w-max mx-auto">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif (Sekarang)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 font-bold text-[10px]">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($ta) }})" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition-colors" title="Edit Tahun Ajaran">
                                        <i class="ti ti-edit text-sm"></i>
                                    </button>
                                    <form action="{{ route('tahun-ajaran.destroy', $ta->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors" title="Hapus Tahun Ajaran">
                                            <i class="ti ti-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                <i class="ti ti-calendar-off text-3xl mb-2 block text-slate-300"></i>
                                Belum ada data tahun ajaran. Klik tombol "Tambah Tahun Ajaran" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tahunAjarans->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tahunAjarans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit Tahun Ajaran -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru'"></h3>
                    <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600"><i class="ti ti-x text-lg"></i></button>
                </div>

                <form :action="isEdit ? '{{ url('tahun-ajaran') }}/' + editData.id : '{{ route('tahun-ajaran.store') }}'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Tahun Ajaran -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="tahun_ajaran" x-model="editData.tahun_ajaran" placeholder="Contoh: 2026/2027" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" x-model="editData.tanggal_mulai" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>

                        <!-- Tanggal Akhir -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Akhir <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_akhir" x-model="editData.tanggal_akhir" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="editData.status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">*Jika diatur Aktif, tahun ajaran aktif lainnya otomatis dinonaktifkan.</p>
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
