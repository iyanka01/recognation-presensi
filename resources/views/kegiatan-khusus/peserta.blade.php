@extends('layouts.app')

@section('title', 'Daftar Peserta: ' . $kegiatan->nama_kegiatan)

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('kegiatan-khusus.index') }}" class="w-8 h-8 rounded-[7px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-colors">
                    <i class="ti ti-arrow-left text-base"></i>
                </a>
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $kegiatan->nama_kegiatan }}</h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 ml-10">
                Kode: <strong class="text-slate-700 dark:text-slate-200">{{ $kegiatan->kode_kegiatan }}</strong> &bull;
                Tanggal: <strong class="text-slate-700 dark:text-slate-200">{{ $kegiatan->tanggal->isoFormat('D MMMM Y') }}</strong> &bull;
                Waktu: <strong class="text-slate-700 dark:text-slate-200">{{ $kegiatan->jam_range }} WIB</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('kegiatan-khusus.scan', $kegiatan->id) }}" target="_blank"
               class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-[7px] flex items-center gap-1.5 shadow-sm transition-all">
                <i class="ti ti-qrcode text-base"></i>
                <span>Buka Scanner</span>
            </a>
            <a href="{{ route('laporan-kegiatan.index', ['kegiatan_id' => $kegiatan->id]) }}"
               class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center gap-1.5 shadow-sm transition-all">
                <i class="ti ti-report text-base"></i>
                <span>Lihat Rekap Laporan</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-users text-blue-600 text-base"></i>
                Daftar Kehadiran Peserta ({{ $presensis->total() }} Data)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Peserta</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Kelas / Jabatan</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Jam Pulang</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($presensis as $index => $p)
                    @php($user = $p->peserta)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-500">
                            {{ $presensis->firstItem() + $index }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">
                            {{ $user->nama_lengkap ?? '-' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $p->user_type === 'guru' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' }}">
                                {{ ucfirst($p->user_type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            @if($p->user_type === 'guru')
                                {{ $user->jabatan ?? 'Guru / Tendik' }}
                            @else
                                Kelas {{ $user->kelas->nama_rombel ?? '-' }}
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ $p->jam_masuk ? $p->jam_masuk->format('H:i:s') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                            {{ $p->jam_pulang ? $p->jam_pulang->format('H:i:s') : '-' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $p->status === 'Terlambat' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            {{ $p->metode }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                            <i class="ti ti-users-minus text-3xl mb-1 text-slate-300 dark:text-slate-600 block"></i>
                            Belum ada peserta yang melakukan presensi pada kegiatan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensis->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $presensis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
