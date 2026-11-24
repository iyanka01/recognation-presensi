@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar di bawah teks -->
    <div class="space-y-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Jadwal Pelajaran</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola alokasi mata pelajaran per rombel kelas, guru pengajar, dan jam KBM.</p>
        </div>

        <!-- Filter Form & Action Buttons (Berada persis di bawah teks, ringkas & rapi) -->
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('jadwal.index') }}" class="inline-flex flex-wrap items-center gap-2">
                <!-- Dropdown Departemen -->
                <div class="inline-flex items-center gap-1.5">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">UNIT:</label>
                    <select name="departemen_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-xs cursor-pointer">
                        @foreach($departemens as $dep)
                            <option value="{{ $dep->id }}" {{ $filterDepartemen == $dep->id ? 'selected' : '' }}>
                                {{ $dep->nama_departemen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Filter Kelas -->
                <div class="inline-flex items-center">
                    <select name="kelas_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-xs cursor-pointer">
                        <option value="">🏫 Semua Kelas</option>
                        @foreach($kelasList as $k)
                            @php
                                $namaTingkat = \Illuminate\Support\Str::startsWith(strtolower($k->tingkat), 'kelas') ? $k->tingkat : 'Kelas ' . $k->tingkat;
                            @endphp
                            <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>
                                {{ $namaTingkat }} {{ $k->nama_rombel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($filterKelas || $filterHari)
                    <a href="{{ route('jadwal.index', ['departemen_id' => $filterDepartemen]) }}" class="px-2.5 py-1 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-[6px] text-xs font-semibold transition-colors">Reset Filter</a>
                @endif
            </form>

            <!-- Tambah Jadwal Button -->
            <button onclick="openModal('modal-tambah')" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] inline-flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                <i class="ti ti-plus text-sm"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    @php
        // Koleksi jam unik terurut untuk matriks
        $jamSlots = $jadwals->map(function($j) {
            return [
                'jam_mulai' => substr($j->jam_mulai, 0, 5),
                'jam_selesai' => substr($j->jam_selesai, 0, 5),
                'range' => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5),
            ];
        })->unique('range')->sortBy('jam_mulai')->values();

        // Hitung total sesi per hari
        $jadwalPerHari = $jadwals->groupBy('hari');
    @endphp

    <!-- Container Utama dengan Alpine.js untuk Switch Tab Hari & View Mode -->
    <div x-data="{ 
            selectedHari: '{{ $filterHari ?: 'Semua' }}',
            viewMode: 'table', // Default 'table' agar pas di layar dan tidak melebar
            searchQuery: ''
         }" class="space-y-4">

        <!-- Baris Tab Hari & Switcher Mode Tampilan -->
        <div class="bg-white dark:bg-slate-800 rounded-[7px] p-2 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
            
            <!-- Tab Tombol Hari -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <button type="button" 
                        @click="selectedHari = 'Semua'"
                        :class="selectedHari === 'Semua' 
                            ? 'bg-blue-600 text-white shadow-xs font-bold' 
                            : 'bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium'"
                        class="px-3 py-1.5 rounded-[6px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Semua Hari</span>
                    <span :class="selectedHari === 'Semua' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                          class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">
                        {{ $jadwals->count() }}
                    </span>
                </button>

                @foreach($hariList as $h)
                    @php
                        $countHari = isset($jadwalPerHari[$h]) ? $jadwalPerHari[$h]->count() : 0;
                    @endphp
                    <button type="button" 
                            @click="selectedHari = '{{ $h }}'"
                            :class="selectedHari === '{{ $h }}' 
                                ? 'bg-blue-600 text-white shadow-xs font-bold' 
                                : 'bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium'"
                            class="px-2.5 py-1.5 rounded-[6px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <span>{{ $h }}</span>
                        <span :class="selectedHari === '{{ $h }}' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                              class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">
                            {{ $countHari }}
                        </span>
                    </button>
                @endforeach
            </div>

            <!-- View Switcher -->
            <div class="flex items-center justify-end gap-1.5 shrink-0">
                <div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-900 rounded-[7px] border border-slate-200 dark:border-slate-700">
                    <button type="button" 
                            @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 font-medium'"
                            class="px-2.5 py-1 text-xs rounded-[5px] flex items-center gap-1 transition-all cursor-pointer">
                        <i class="ti ti-table text-sm"></i>
                        <span>Tabel List</span>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'matrix'"
                            :class="viewMode === 'matrix' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 font-medium'"
                            class="px-2.5 py-1 text-xs rounded-[5px] flex items-center gap-1 transition-all cursor-pointer">
                        <i class="ti ti-layout-grid text-sm"></i>
                        <span>Matriks Grid</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- VIEW 1: MATRIKS SEMUA KELAS PER HARI (GRID VIEW)         -->
        <!-- ======================================================== -->
        <div x-show="viewMode === 'matrix'" class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        Matriks Jadwal Seluruh Kelas &bull; Hari <span class="text-blue-600 dark:text-blue-400" x-text="selectedHari"></span>
                    </h2>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $kelasList->count() }} Kelas</span> pada departemen ini
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-100/80 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3 w-36 text-center border-r border-slate-200 dark:border-slate-700 sticky left-0 bg-slate-100 dark:bg-slate-900 z-10 shadow-xs">
                                <i class="ti ti-clock mr-1"></i> Jam Pembelajaran
                            </th>
                            @forelse($kelasList as $kelasItem)
                                @php
                                    $namaTingkat = \Illuminate\Support\Str::startsWith(strtolower($kelasItem->tingkat), 'kelas') ? $kelasItem->tingkat : 'Kelas ' . $kelasItem->tingkat;
                                @endphp
                                <th class="py-2.5 px-3 min-w-[200px] border-r border-slate-200 dark:border-slate-700 text-center">
                                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $namaTingkat }} {{ $kelasItem->nama_rombel }}</div>
                                </th>
                            @empty
                                <th class="py-2.5 px-3 text-slate-400 font-normal">Tidak ada kelas</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        @php
                            // Ambil slot jam hanya untuk hari yang ada
                            // Jika belum ada jadwal sama sekali, tampilkan default slot
                            $defaultSlots = [
                                ['jam_mulai' => '07:00', 'jam_selesai' => '08:30', 'range' => '07:00 - 08:30'],
                                ['jam_mulai' => '08:30', 'jam_selesai' => '10:00', 'range' => '08:30 - 10:00'],
                                ['jam_mulai' => '10:15', 'jam_selesai' => '11:45', 'range' => '10:15 - 11:45'],
                                ['jam_mulai' => '12:30', 'jam_selesai' => '14:00', 'range' => '12:30 - 14:00'],
                            ];
                        @endphp

                        @foreach($hariList as $h)
                            @php
                                $jadwalsHariIni = $jadwals->where('hari', $h);
                                $slotsHariIni = $jadwalsHariIni->map(function($j) {
                                    return [
                                        'jam_mulai' => substr($j->jam_mulai, 0, 5),
                                        'jam_selesai' => substr($j->jam_selesai, 0, 5),
                                        'range' => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5),
                                    ];
                                })->unique('range')->sortBy('jam_mulai')->values();

                                if ($slotsHariIni->isEmpty()) {
                                    $slotsHariIni = collect($defaultSlots);
                                }
                            @endphp

                            @foreach($slotsHariIni as $slot)
                            <tr x-show="selectedHari === 'Semua' || selectedHari === '{{ $h }}'" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <!-- Kolom Jam Mulai - Jam Selesai -->
                                    <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-50/70 dark:bg-slate-900/40 sticky left-0 z-10 shadow-xs whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[5px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                            <span class="text-blue-600 font-bold" x-show="selectedHari === 'Semua'">{{ $h }} &bull; </span>
                                            <span>{{ $slot['range'] }}</span>
                                        </div>
                                    </td>

                                    <!-- Kolom Per Kelas -->
                                    @foreach($kelasList as $kelasItem)
                                        @php
                                            // Cari jadwal pada slot jam dan kelas ini
                                            $jadwalItem = $jadwalsHariIni->first(function($item) use ($kelasItem, $slot) {
                                                return $item->kelas_id == $kelasItem->id && substr($item->jam_mulai, 0, 5) == $slot['jam_mulai'];
                                            });
                                        @endphp

                                        <td class="py-2 px-2.5 border-r border-slate-200 dark:border-slate-700 align-top">
                                            @if($jadwalItem)
                                                <div class="p-2.5 rounded-[7px] bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900 text-left space-y-1 group relative transition-all hover:shadow-sm hover:border-blue-300">
                                                    <!-- Mapel -->
                                                    <div class="font-bold text-slate-800 dark:text-slate-100 text-xs leading-tight">
                                                        {{ $jadwalItem->mataPelajaran?->nama_mapel ?? '-' }}
                                                    </div>
                                                    <!-- Guru Pengajar -->
                                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300 font-medium truncate">
                                                        <i class="ti ti-user text-xs text-blue-600 dark:text-blue-400"></i>
                                                        <span class="truncate">{{ $jadwalItem->guru?->nama_lengkap ?? 'Belum ditentukan' }}</span>
                                                    </div>

                                                    <!-- Floating Action Buttons -->
                                                    <div class="pt-1 flex items-center justify-between border-t border-blue-100 dark:border-blue-900/60 mt-1">
                                                        <span class="text-[10px] font-mono text-slate-400">
                                                            {{ $jadwalItem->mataPelajaran?->kode_mapel }}
                                                        </span>
                                                        <div class="flex items-center gap-1">
                                                            <button type="button"
                                                                onclick='openEdit(@json($jadwalItem->id), @json($jadwalItem->kelas_id), @json($jadwalItem->mata_pelajaran_id), @json($jadwalItem->guru_id), @json($jadwalItem->hari), @json(substr($jadwalItem->jam_mulai,0,5)), @json(substr($jadwalItem->jam_selesai,0,5)))'
                                                                title="Edit"
                                                                class="p-1 rounded-[5px] text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900 transition-colors cursor-pointer">
                                                                <i class="ti ti-pencil text-xs"></i>
                                                            </button>
                                                            <form action="{{ route('jadwal.destroy', $jadwalItem->id) }}" method="POST" onsubmit="return confirm('Hapus sesi ini?');" class="inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" 
                                                                    title="Hapus"
                                                                    class="p-1 rounded-[5px] text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-900 transition-colors cursor-pointer">
                                                                    <i class="ti ti-trash text-xs"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Kotak Jam Kosong -->
                                                <div class="h-16 rounded-[6px] border border-dashed border-slate-200 dark:border-slate-700/80 flex items-center justify-center text-slate-300 dark:text-slate-600 hover:border-blue-300 hover:bg-blue-50/20 transition-colors group cursor-pointer"
                                                     onclick="openModalWithPrefill('modal-tambah', '{{ $kelasItem->id }}', '{{ $h }}', '{{ $slot['jam_mulai'] }}', '{{ $slot['jam_selesai'] }}')"
                                                     title="Klik untuk tambah jadwal di slot ini">
                                                    <span class="text-[11px] font-medium group-hover:text-blue-600 flex items-center gap-1">
                                                        <i class="ti ti-plus text-xs"></i> Kosong
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- VIEW 2: TABEL LIST BERKELOMPOK PER HARI (TABLE VIEW)     -->
        <!-- ======================================================== -->
        <div x-show="viewMode === 'table'" class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3 w-12 text-center">NO</th>
                            <th class="py-2.5 px-3">HARI &amp; JAM</th>
                            <th class="py-2.5 px-3">KELAS / ROMBEL</th>
                            <th class="py-2.5 px-3">MATA PELAJARAN</th>
                            <th class="py-2.5 px-3">GURU PENGAJAR</th>
                            <th class="py-2.5 px-3 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        @php $no = 1; @endphp
                        @forelse($jadwals as $j)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors"
                            x-show="selectedHari === 'Semua' || selectedHari === '{{ $j->hari }}'">
                            <!-- No -->
                            <td class="py-2.5 px-3 text-center text-slate-400 text-[11px] font-mono">
                                {{ $no++ }}
                            </td>

                            <!-- Hari & Jam -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
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
                                        $colorClass = $hariColors[$j->hari] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-[5px] text-[10px] font-bold border {{ $colorClass }}">
                                        {{ $j->hari }}
                                    </span>
                                    <span class="font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $j->jam_range }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kelas -->
                            <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                @if($j->kelas)
                                    @php
                                        $cleanTingkat = \Illuminate\Support\Str::startsWith(strtolower($j->kelas->tingkat), 'kelas') ? $j->kelas->tingkat : 'Kelas ' . $j->kelas->tingkat;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                        <i class="ti ti-school text-xs"></i>
                                        <span>{{ $cleanTingkat }} {{ $j->kelas->nama_rombel }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Mapel -->
                            <td class="py-2.5 px-3">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                        {{ $j->mataPelajaran?->nama_mapel ?? '-' }}
                                    </span>
                                    @if($j->mataPelajaran?->kode_mapel)
                                        <span class="text-[10px] text-slate-400 font-mono">
                                            Kode: {{ $j->mataPelajaran->kode_mapel }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Guru -->
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ substr($j->guru?->nama_lengkap ?? 'G', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-xs">{{ $j->guru?->nama_lengkap ?? 'Belum ditentukan' }}</span>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <button type="button"
                                        onclick='openEdit(@json($j->id), @json($j->kelas_id), @json($j->mata_pelajaran_id), @json($j->guru_id), @json($j->hari), @json(substr($j->jam_mulai,0,5)), @json(substr($j->jam_selesai,0,5)))'
                                        title="Edit Jadwal"
                                        class="p-1 rounded-[6px] text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors cursor-pointer">
                                        <i class="ti ti-pencil text-sm"></i>
                                    </button>

                                    <form action="{{ route('jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini? Data absensi mapel terkait juga akan ikut terhapus.');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" 
                                            title="Hapus Jadwal"
                                            class="p-1 rounded-[6px] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer">
                                            <i class="ti ti-trash text-sm"></i>
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
                                    <p class="text-xs font-semibold">Belum ada jadwal pelajaran untuk filter ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
        function openModalWithPrefill(id, kelasId, hari, jamMulai, jamSelesai) {
            openModal(id);
            if (kelasId) document.querySelector('#' + id + ' select[name="kelas_id"]').value = kelasId;
            if (hari) document.querySelector('#' + id + ' select[name="hari"]').value = hari;
            if (jamMulai) document.querySelector('#' + id + ' input[name="jam_mulai"]').value = jamMulai;
            if (jamSelesai) document.querySelector('#' + id + ' input[name="jam_selesai"]').value = jamSelesai;
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
