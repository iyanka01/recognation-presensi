@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Dashboard Analitik</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Ringkasan data presensi hari ini &mdash; {{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <a href="{{ route('presensi.scan') }}" target="_blank"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-[7px] text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            Buka Scanner
        </a>
    </div>

    <!-- Welcome Banner (TailwindAdmin Style) -->
    @php($currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->check() ? \Illuminate\Support\Facades\Auth::guard('guru')->user() : auth()->user())
    <div class="bg-gradient-to-r from-blue-50/90 via-sky-50/70 to-indigo-50/40 border border-blue-100/80 rounded-[7px] p-5 sm:p-6 shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[11px] font-semibold bg-blue-100 text-blue-700">
                    Sistem Presensi
                </span>
                <span class="text-xs text-slate-500 font-medium">{{ now()->isoFormat('D MMMM Y') }}</span>
            </div>
            <h2 class="text-lg font-bold text-slate-800">
                Selamat Datang Kembali, {{ $currentUser->nama_lengkap ?? 'Administrator' }}! 👋
            </h2>
            <p class="text-xs text-slate-500 max-w-2xl font-normal">
                Pantau kehadiran siswa secara real-time, analisis tren absensi mingguan, dan tindak lanjuti siswa yang sering terlambat dengan cepat dan efisien.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <div class="bg-white rounded-[7px] border border-blue-100/80 px-4 py-2.5 text-right shadow-sm hidden sm:block">
                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Status Sistem</div>
                <div class="text-xs font-bold text-emerald-600 flex items-center justify-end gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Aktif &amp; Terhubung
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Hari Ini (4 Stat Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Hadir -->
        <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 border-l-4 border-l-emerald-500 p-5 flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Hadir</p>
                <h3 class="text-xl lg:text-2xl font-bold text-slate-800 mt-1">{{ $statHariIni['Hadir'] }}</h3>
                <span class="inline-flex items-center text-[11px] font-medium text-emerald-600 mt-1">
                    Tepat Waktu
                </span>
            </div>
            <div class="w-12 h-12 rounded-[7px] bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 border-l-4 border-l-rose-500 p-5 flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terlambat</p>
                <h3 class="text-xl lg:text-2xl font-bold text-slate-800 mt-1">{{ $statHariIni['Terlambat'] }}</h3>
                <span class="inline-flex items-center text-[11px] font-medium text-rose-600 mt-1">
                    Lewat Batas Jam
                </span>
            </div>
            <div class="w-12 h-12 rounded-[7px] bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Izin / Sakit -->
        <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 border-l-4 border-l-amber-500 p-5 flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin / Sakit</p>
                <h3 class="text-xl lg:text-2xl font-bold text-slate-800 mt-1">{{ $statHariIni['IzinSakit'] }}</h3>
                <span class="inline-flex items-center text-[11px] font-medium text-amber-600 mt-1">
                    Keterangan Sah
                </span>
            </div>
            <div class="w-12 h-12 rounded-[7px] bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        <!-- Alpa -->
        <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 border-l-4 border-l-slate-500 p-5 flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alpa</p>
                <h3 class="text-xl lg:text-2xl font-bold text-slate-800 mt-1">{{ $statHariIni['Alpa'] }}</h3>
                <span class="inline-flex items-center text-[11px] font-medium text-slate-500 mt-1">
                    Tanpa Keterangan
                </span>
            </div>
            <div class="w-12 h-12 rounded-[7px] bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart: 7 Hari Terakhir (2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 p-5 lg:p-6 min-w-0">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-semibold text-slate-800 text-base">Tren Kehadiran 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-normal">Perbandingan jumlah hadir tepat waktu, terlambat, dan absen mingguan</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-[5px] text-xs font-semibold bg-slate-100 text-slate-600">
                    Mingguan
                </span>
            </div>
            <div class="h-[260px] w-full relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Komposisi Hari Ini (1/3 width) -->
        <div class="bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 p-5 lg:p-6 min-w-0 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-semibold text-slate-800 text-base">Komposisi Hari Ini</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-normal">Distribusi status presensi siswa hari ini</p>
                    </div>
                </div>
                <div class="h-[190px] w-full flex items-center justify-center relative">
                    <canvas id="todayChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-center gap-4 text-xs font-medium text-slate-600">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Terlambat
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Absen
                </div>
            </div>
        </div>
    </div>

    <!-- Table & System Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top 5 Terlambat (2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[7px] bg-rose-50 text-rose-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 text-base">5 Siswa Sering Terlambat</h3>
                            <p class="text-xs text-slate-500 mt-0.5 font-normal">Catatan akumulasi keterlambatan bulan {{ now()->isoFormat('MMMM Y') }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-[5px] bg-rose-50 text-rose-600 border border-rose-100 text-xs font-semibold">
                        Perlu Perhatian
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-16 text-center">Rank</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4 text-right">Frekuensi Terlambat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($topTerlambat as $idx => $t)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $idx == 0 ? 'bg-amber-100 text-amber-700' : ($idx == 1 ? 'bg-slate-200 text-slate-700' : ($idx == 2 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600')) }}">
                                        {{ $idx + 1 }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ $t->siswa->nama_lengkap ?? 'Unknown' }}</div>
                                    <div class="text-xs text-slate-500">NIS: {{ $t->siswa->nis ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[5px] bg-rose-50 border border-rose-100 text-xs font-semibold text-rose-600">
                                        <span class="font-bold text-sm">{{ $t->total }}</span> Kali
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 mb-2 text-emerald-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-slate-700 text-sm">Luar Biasa!</h4>
                                    <p class="text-xs text-slate-500 mt-1">Belum ada siswa yang terlambat bulan ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3.5 border-t border-slate-100 bg-slate-50/40 text-center">
                <span class="text-xs text-slate-500">Menampilkan 5 data peringkat teratas berdasarkan frekuensi kedatangan lewat batas jam operasional</span>
            </div>
        </div>

        <!-- System Info Card (1/3 width) -->
        <div class="bg-gradient-to-br from-sky-500 to-blue-700 rounded-[7px] shadow-[rgba(145,158,171,0.2)_0px_0px_2px_0px,rgba(145,158,171,0.12)_0px_12px_24px_-4px] border-0 overflow-hidden flex flex-col justify-between text-white">
            <div>
                <div class="p-5 border-b border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[7px] bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-4 h-4 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-white text-base">Informasi Sistem</h3>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10px] font-bold bg-white/20 text-white uppercase tracking-wider">
                        Online
                    </span>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-white/10">
                        <span class="text-xs font-medium text-sky-100 uppercase tracking-wider">Versi Aplikasi</span>
                        <span class="text-xs font-bold text-white bg-white/20 px-2.5 py-1 rounded-[5px]">v1.2.0</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-white/10">
                        <span class="text-xs font-medium text-sky-100 uppercase tracking-wider">Waktu Server</span>
                        <span class="text-sm font-semibold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-sm shadow-emerald-400"></span>
                            {{ date('H:i T') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-white/10">
                        <span class="text-xs font-medium text-sky-100 uppercase tracking-wider">Timezone</span>
                        <span class="text-xs font-semibold text-white">{{ config('app.timezone') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-sky-100 uppercase tracking-wider">Environment</span>
                        <span class="text-[10px] font-bold text-blue-800 bg-white px-2 py-0.5 rounded-[5px] uppercase tracking-wider">{{ app()->environment() }}</span>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-white/10 text-center bg-black/10">
                <p class="text-xs font-medium text-sky-100">Mayantara Presensi &mdash; Made with <span class="text-rose-300">❤️</span> by <span class="font-bold text-white">iyank</span></p>
            </div>
        </div>
    </div>

</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Bar Chart: Tren Kehadiran
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($grafikTanggal) !!},
            datasets: [
                {
                    label: 'Hadir Tepat Waktu',
                    data: {!! json_encode($grafikHadir) !!},
                    backgroundColor: '#10b981', // emerald-500
                    borderRadius: 4,
                    borderWidth: 0,
                    maxBarThickness: 36
                },
                {
                    label: 'Terlambat',
                    data: {!! json_encode($grafikTerlambat) !!},
                    backgroundColor: '#f43f5e', // rose-500
                    borderRadius: 4,
                    borderWidth: 0,
                    maxBarThickness: 36
                },
                {
                    label: 'Izin/Sakit/Alpa',
                    data: {!! json_encode($grafikAbsen) !!},
                    backgroundColor: '#f59e0b', // amber-500
                    borderRadius: 4,
                    borderWidth: 0,
                    maxBarThickness: 36
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 16,
                        font: { family: "'Inter', sans-serif", size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                    bodyFont: { family: "'Inter', sans-serif", size: 12 },
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { precision: 0, font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b' }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b' }
                }
            }
        }
    });

    // Pie Chart: Komposisi Hari Ini
    const todayCtx = document.getElementById('todayChart').getContext('2d');
    
    // Jika data hari ini kosong semua
    const totalToday = {{ $statHariIni['Hadir'] + $statHariIni['Terlambat'] + $statHariIni['IzinSakit'] + $statHariIni['Alpa'] }};
    const pieData = totalToday === 0 
        ? [1] // Fake data for empty state
        : [{{ $statHariIni['Hadir'] }}, {{ $statHariIni['Terlambat'] }}, {{ $statHariIni['IzinSakit'] + $statHariIni['Alpa'] }}];
        
    const pieColors = totalToday === 0 
        ? ['#f1f5f9'] // slate-100 empty state
        : ['#10b981', '#f43f5e', '#f59e0b'];

    new Chart(todayCtx, {
        type: 'doughnut',
        data: {
            labels: totalToday === 0 ? ['Belum Ada Data'] : ['Hadir', 'Terlambat', 'Absen'],
            datasets: [{
                data: pieData,
                backgroundColor: pieColors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: totalToday !== 0,
                    backgroundColor: '#1e293b',
                    padding: 12,
                    bodyFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + ' Siswa';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
