@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header + Filter -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Jadwal Pelajaran</h1>
            <p class="text-sm text-slate-500 mt-1">Atur jadwal mata pelajaran per kelas, guru pengajar, hari, dan jam.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <form method="GET" action="{{ route('jadwal.index') }}" class="flex gap-2">
                <select name="kelas_id" class="px-3 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>Kelas {{ $k->tingkat }} {{ $k->nama_rombel }}</option>
                    @endforeach
                </select>
                <select name="hari" class="px-3 py-2.5 bg-white border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 shadow-sm">
                    <option value="">Semua Hari</option>
                    @foreach($hariList as $h)
                    <option value="{{ $h }}" {{ $filterHari === $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium rounded-xl transition-all shadow-sm">Filter</button>
            </form>
            <button onclick="openModal('modal-tambah')" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl flex items-center gap-2 justify-center transition-all shadow-sm shadow-sky-200 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Jadwal
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Hari</th>
                        <th class="p-4 font-semibold">Jam</th>
                        <th class="p-4 font-semibold">Kelas</th>
                        <th class="p-4 font-semibold">Mata Pelajaran</th>
                        <th class="p-4 font-semibold">Guru Pengajar</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4">
                            <span class="inline-flex px-2.5 py-1 rounded-md bg-violet-100 text-violet-700 font-bold">{{ $j->hari }}</span>
                        </td>
                        <td class="p-4 font-mono font-medium text-slate-700">{{ $j->jam_range }}</td>
                        <td class="p-4 font-medium text-slate-800">{{ $j->kelas ? 'Kelas ' . $j->kelas->tingkat . ' ' . $j->kelas->nama_rombel : '-' }}</td>
                        <td class="p-4">
                            <span class="font-medium text-slate-800">{{ $j->mataPelajaran?->nama_mapel ?? '-' }}</span>
                            <span class="block text-xs text-slate-400">{{ $j->mataPelajaran?->kode_mapel }}</span>
                        </td>
                        <td class="p-4 text-slate-500">{{ $j->guru?->nama_lengkap ?? '-' }}</td>
                        <td class="p-4 flex items-center justify-end gap-2">
                            <button type="button"
                                onclick='openEdit(@json($j->id), @json($j->kelas_id), @json($j->mata_pelajaran_id), @json($j->guru_id), @json($j->hari), @json(substr($j->jam_mulai,0,5)), @json(substr($j->jam_selesai,0,5)))'
                                class="p-2 text-sky-500 hover:bg-sky-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form action="{{ route('jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini? Data absensi mapel terkait juga akan ikut terhapus.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            Belum ada jadwal pelajaran{{ ($filterKelas || $filterHari) ? ' untuk filter ini' : '' }}.
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
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-tambah')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10">
                <div class="bg-white px-6 pt-6 pb-6 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Tambah Jadwal Pelajaran</h3>
                </div>

                <form action="{{ route('jadwal.store') }}" method="POST">
                    @csrf
                    @include('jadwal._form')
                    <div class="bg-sky-50/30 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="closeModal('modal-tambah')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-amber-300/40">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit" class="fixed inset-0 z-[100] overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-edit')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10">
                <div class="bg-white px-6 pt-6 pb-6 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Edit Jadwal Pelajaran</h3>
                </div>

                <form id="form-edit" action="" method="POST">
                    @csrf @method('PUT')
                    @include('jadwal._form', ['prefixEdit' => true])
                    <div class="bg-sky-50/30 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="closeModal('modal-edit')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-sky-300/40">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            // Preselect filter kelas di modal tambah
            const filterKelas = '{{ $filterKelas }}';
            if (id === 'modal-tambah' && filterKelas) {
                const sel = document.querySelector('#modal-tambah select[name="kelas_id"]');
                if (sel && !sel.value) sel.value = filterKelas;
            }
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function openEdit(id, kelasId, mapelId, guruId, hari, jamMulai, jamSelesai) {
            const form = document.getElementById('form-edit');
            form.action = '{{ url('jadwal') }}/' + id;
            document.querySelector('#modal-edit select[name="kelas_id"]').value = kelasId;
            document.querySelector('#modal-edit select[name="mata_pelajaran_id"]').value = mapelId;
            document.querySelector('#modal-edit select[name="guru_id"]').value = guruId || '';
            document.querySelector('#modal-edit select[name="hari"]').value = hari;
            document.querySelector('#modal-edit input[name="jam_mulai"]').value = jamMulai;
            document.querySelector('#modal-edit input[name="jam_selesai"]').value = jamSelesai;
            openModal('modal-edit');
        }
    </script>
</div>
@endsection
