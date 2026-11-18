@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Jadwal Pelajaran</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Atur alokasi mata pelajaran per kelas, guru pengajar, hari, dan jam pembelajaran.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('jadwal.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto">
                <select name="kelas_id" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        @php
                            $namaTingkat = \Illuminate\Support\Str::startsWith(strtolower($k->tingkat), 'kelas') ? $k->tingkat : 'Kelas ' . $k->tingkat;
                        @endphp
                        <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>
                            {{ $namaTingkat }} {{ $k->nama_rombel }}
                        </option>
                    @endforeach
                </select>

                <select name="hari" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm">
                    <option value="">Semua Hari</option>
                    @foreach($hariList as $h)
                        <option value="{{ $h }}" {{ $filterHari === $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-[7px] transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <i class="ti ti-filter text-sm"></i>
                    <span>Filter</span>
                </button>

                @if($filterKelas || $filterHari)
                    <a href="{{ route('jadwal.index') }}" class="px-3 py-2 text-rose-600 dark:text-rose-400 hover:underline text-xs font-medium">Reset</a>
                @endif
            </form>

            <!-- Tambah Jadwal Button -->
            <button onclick="openModal('modal-tambah')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto shrink-0 cursor-pointer">
                <i class="ti ti-plus text-sm"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Hari</th>
                        <th class="py-3.5 px-4">Jam Pelajaran</th>
                        <th class="py-3.5 px-4">Kelas &amp; Rombel</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Guru Pengajar</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                        <!-- Hari -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @php
                                $hariColors = [
                                    'Senin' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                    'Selasa' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
                                    'Rabu' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                    'Kamis' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                    'Jumat' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800',
                                    'Sabtu' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                    'Minggu' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                ];
                                $colorClass = $hariColors[$j->hari] ?? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-[5px] text-[11px] font-bold border {{ $colorClass }}">
                                {{ $j->hari }}
                            </span>
                        </td>

                        <!-- Jam -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[5px] bg-slate-100 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-mono text-[11px] font-semibold">
                                <i class="ti ti-clock text-xs text-slate-400"></i>
                                {{ $j->jam_range }}
                            </span>
                        </td>

                        <!-- Kelas -->
                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                            @if($j->kelas)
                                @php
                                    $cleanTingkat = \Illuminate\Support\Str::startsWith(strtolower($j->kelas->tingkat), 'kelas') ? $j->kelas->tingkat : 'Kelas ' . $j->kelas->tingkat;
                                @endphp
                                <span class="text-xs">{{ $cleanTingkat }} {{ $j->kelas->nama_rombel }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- Mapel -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                    {{ $j->mataPelajaran?->nama_mapel ?? '-' }}
                                </span>
                                @if($j->mataPelajaran?->kode_mapel)
                                    <span class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $j->mataPelajaran->kode_mapel }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Guru -->
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                    {{ substr($j->guru?->nama_lengkap ?? 'G', 0, 1) }}
                                </div>
                                <span class="font-medium">{{ $j->guru?->nama_lengkap ?? '-' }}</span>
                            </div>
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button type="button"
                                    onclick='openEdit(@json($j->id), @json($j->kelas_id), @json($j->mata_pelajaran_id), @json($j->guru_id), @json($j->hari), @json(substr($j->jam_mulai,0,5)), @json(substr($j->jam_selesai,0,5)))'
                                    title="Edit Jadwal"
                                    class="w-8 h-8 rounded-[7px] text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="ti ti-edit text-base"></i>
                                </button>

                                <form action="{{ route('jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini? Data absensi mapel terkait juga akan ikut terhapus.');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                        title="Hapus Jadwal"
                                        class="w-8 h-8 rounded-[7px] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ti ti-calendar-off text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-xs font-semibold">Belum ada jadwal pelajaran{{ ($filterKelas || $filterHari) ? ' untuk filter ini' : '' }}.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
    <!-- Modal Tambah -->
    <div id="modal-tambah" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-tambah')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-[7px] bg-white dark:bg-slate-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 border border-slate-200 dark:border-slate-700">
                <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="ti ti-calendar-plus text-blue-600 text-lg"></i>
                        Tambah Jadwal Pelajaran
                    </h3>
                    <button type="button" onclick="closeModal('modal-tambah')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('jadwal.store') }}" method="POST">
                    @csrf
                    @include('jadwal._form')
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-tambah')" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-[7px] transition-all shadow-sm cursor-pointer">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-edit')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-[7px] bg-white dark:bg-slate-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 border border-slate-200 dark:border-slate-700">
                <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="ti ti-edit text-blue-600 text-lg"></i>
                        Edit Jadwal Pelajaran
                    </h3>
                    <button type="button" onclick="closeModal('modal-edit')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form id="form-edit" action="" method="POST">
                    @csrf @method('PUT')
                    @include('jadwal._form', ['prefixEdit' => true])
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-edit')" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-[7px] transition-all shadow-sm cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
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
