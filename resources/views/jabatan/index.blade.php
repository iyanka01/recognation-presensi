@extends('layouts.app')

@section('title', 'Master Jabatan Pegawai')

@section('content')
<div class="space-y-6" x-data="{
    openModal: false,
    isEdit: false,
    editData: {
        id: '',
        nama_jabatan: '',
        kategori: 'guru',
        urutan: 0,
        keterangan: ''
    },
    openCreate() {
        this.isEdit = false;
        this.editData = {
            id: '',
            nama_jabatan: '',
            kategori: 'guru',
            urutan: 0,
            keterangan: ''
        };
        this.openModal = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.editData = {
            id: item.id,
            nama_jabatan: item.nama_jabatan,
            kategori: item.kategori,
            urutan: item.urutan || 0,
            keterangan: item.keterangan || ''
        };
        this.openModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Master Jabatan Pegawai</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola daftar jabatan untuk Pendidik (Guru) dan Tenaga Kependidikan (Staff).</p>
        </div>
        <button type="button" @click="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-2 transition-all shadow-sm">
            <i class="ti ti-plus text-base"></i>
            Tambah Jabatan
        </button>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
        <i class="ti ti-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center gap-2">
        <i class="ti ti-alert-circle text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Table List -->
    <div class="bg-white rounded-[7px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Jabatan</th>
                        <th class="py-3 px-4">Kategori Pegawai</th>
                        <th class="py-3 px-4 text-center">Urutan</th>
                        <th class="py-3 px-4 text-center">Jumlah Pegawai</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($jabatans as $idx => $j)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center text-slate-400">{{ $jabatans->firstItem() + $idx }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $j->nama_jabatan }}</td>
                            <td class="py-3 px-4">
                                @if($j->kategori === 'guru')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="ti ti-school text-xs"></i>
                                        Pendidik (Guru)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="ti ti-briefcase text-xs"></i>
                                        Tenaga Kependidikan (Staff)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-600">{{ $j->urutan }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-bold text-slate-700 font-mono">
                                    {{ $j->gurus_count }} orang
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $j->keterangan ?: '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($j) }})" class="p-1.5 rounded-md hover:bg-blue-50 text-blue-600 transition-colors" title="Edit">
                                        <i class="ti ti-edit text-sm"></i>
                                    </button>
                                    <form action="{{ route('jabatan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md hover:bg-rose-50 text-rose-600 transition-colors" title="Hapus">
                                            <i class="ti ti-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="ti ti-briefcase-off text-2xl mb-1 block"></i>
                                Belum ada data jabatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jabatans->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $jabatans->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <div x-show="openModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs" @click="openModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <h3 class="font-bold text-slate-800 text-sm" x-text="isEdit ? 'Edit Jabatan' : 'Tambah Jabatan Baru'"></h3>
                    <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>

                <form :action="isEdit ? '{{ url('admin/jabatan') }}/' + editData.id : '{{ route('jabatan.store') }}'" method="POST" class="p-5 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Jabatan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_jabatan" x-model="editData.nama_jabatan" required placeholder="Contoh: Guru Mata Pelajaran, Tata Usaha" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kategori Pegawai <span class="text-rose-500">*</span></label>
                        <select name="kategori" x-model="editData.kategori" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="guru">Pendidik (Guru)</option>
                            <option value="staff">Tenaga Kependidikan (Staff)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan Tampilan</label>
                        <input type="number" name="urutan" x-model="editData.urutan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Keterangan (Opsional)</label>
                        <textarea name="keterangan" x-model="editData.keterangan" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Deskripsi tugas atau peruntukan jabatan..."></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm">
                            Simpan Jabatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
