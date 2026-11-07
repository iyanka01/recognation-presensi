@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Data Mata Pelajaran</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar mata pelajaran yang diajarkan di lembaga.</p>
        </div>
        <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200 w-full sm:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Mapel
        </button>
    </div>

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Kode</th>
                        <th class="p-4 font-semibold">Nama Mata Pelajaran</th>
                        <th class="p-4 font-semibold text-center">Jumlah Jadwal</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($mapels as $m)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4">
                            <span class="inline-flex px-2.5 py-1 rounded-md bg-sky-100 text-sky-700 font-bold">{{ $m->kode_mapel }}</span>
                        </td>
                        <td class="p-4 font-medium text-slate-800">{{ $m->nama_mapel }}</td>
                        <td class="p-4 text-center text-slate-500">{{ $m->jadwal_count }} jadwal</td>
                        <td class="p-4 flex items-center justify-end gap-2">
                            <button type="button" onclick="openEdit('{{ $m->id }}', '{{ $m->kode_mapel }}', `{{ $m->nama_mapel }}`)" class="p-2 text-sky-500 hover:bg-sky-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form action="{{ route('mapel.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            Belum ada data mata pelajaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
    <!-- Modal Tambah -->
    <div id="modal-tambah" class="fixed inset-0 z-[100] overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modal-tambah').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10">
                <div class="bg-white px-6 pt-6 pb-6 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Tambah Mata Pelajaran</h3>
                </div>

                <form action="{{ route('mapel.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kode Mapel</label>
                            <input type="text" name="kode_mapel" placeholder="Misal: MAT, IND, IPA" required maxlength="20" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Mata Pelajaran</label>
                            <input type="text" name="nama_mapel" placeholder="Misal: Matematika" required maxlength="100" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        </div>
                    </div>

                    <div class="bg-sky-50/30 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-amber-300/40">Simpan Mapel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit" class="fixed inset-0 z-[100] overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modal-edit').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10">
                <div class="bg-white px-6 pt-6 pb-6 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Edit Mata Pelajaran</h3>
                </div>

                <form id="form-edit" action="" method="POST">
                    @csrf @method('PUT')
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kode Mapel</label>
                            <input type="text" name="kode_mapel" id="edit-kode" required maxlength="20" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Mata Pelajaran</label>
                            <input type="text" name="nama_mapel" id="edit-nama" required maxlength="100" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                        </div>
                    </div>

                    <div class="bg-sky-50/30 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-sky-300/40">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush

    <script>
        function openEdit(id, kode, nama) {
            const form = document.getElementById('form-edit');
            form.action = '{{ url('mapel') }}/' + id;
            document.getElementById('edit-kode').value = kode;
            document.getElementById('edit-nama').value = nama;
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
</div>
@endsection
