@extends(Auth::guard('guru')->check() ? 'layouts.guru' : 'layouts.app')

@section('title', 'Rekap Absensi Mapel')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Rekap Absensi Mata Pelajaran</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi kehadiran siswa per mata pelajaran dalam rentang tanggal.</p>
        </div>
        @if($filterKelas)
        <a href="{{ route('rekap-mapel.print', request()->only(['kelas_id', 'mapel_id', 'dari', 'sampai'])) }}" target="_blank"
           class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </a>
        @endif
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('rekap-mapel.index') }}"
          class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 p-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kelas</label>
            <select name="kelas_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>Kelas {{ $k->tingkat }} {{ $k->nama_rombel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Mapel</label>
            <select name="mapel_id" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                <option value="">Semua Mapel</option>
                @foreach($mapels as $m)
                <option value="{{ $m->id }}" {{ $filterMapel == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari</label>
                <input type="date" name="dari" value="{{ $dari }}" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
            </div>
        </div>
        <button type="submit" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-bold rounded-xl transition-all shadow-sm">Tampilkan Rekap</button>
    </form>

    <!-- Tabel Rekap -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-12">No</th>
                        <th class="p-4 font-semibold">Nama Siswa</th>
                        <th class="p-4 font-semibold text-center bg-emerald-50/40">Hadir</th>
                        <th class="p-4 font-semibold text-center bg-blue-50/40">Izin</th>
                        <th class="p-4 font-semibold text-center bg-amber-50/40">Sakit</th>
                        <th class="p-4 font-semibold text-center bg-rose-50/40">Alpa</th>
                        <th class="p-4 font-semibold text-center">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="p-4 font-medium text-slate-800">{{ $r->siswa->nama_lengkap }}</td>
                        <td class="p-4 text-center font-bold text-emerald-600">{{ $r->hadir }}</td>
                        <td class="p-4 text-center font-bold text-blue-600">{{ $r->izin }}</td>
                        <td class="p-4 text-center font-bold text-amber-600">{{ $r->sakit }}</td>
                        <td class="p-4 text-center font-bold text-rose-600">{{ $r->alpa }}</td>
                        <td class="p-4 text-center">
                            @if($r->persen !== null)
                            <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $r->persen >= 90 ? 'bg-emerald-100 text-emerald-700' : ($r->persen >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                {{ $r->persen }}%
                            </span>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            Pilih kelas lalu klik "Tampilkan Rekap" untuk melihat data.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
