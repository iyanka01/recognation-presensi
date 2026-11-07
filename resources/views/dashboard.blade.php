@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Dashboard Analitik</h1>
            <p class="text-xs text-sky-600/70 mt-0.5 font-medium">Ringkasan data presensi hari ini &mdash; {{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <a href="{{ route('presensi.scan') }}" target="_blank"
           class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-sky-300/40 hover:shadow-sky-400/50 hover:-translate-y-0.5 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            Buka Scanner
        </a>
    </div>

    <!-- Quick Stats Hari Ini -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Hadir -->
        <div class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm border border-sky-100 flex items-center gap-3 group hover:shadow-md hover:border-emerald-200 hover:-translate-y-0.5 transition-all duration-200">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-emerald-300/40 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Hadir</p>
                <h3 class="text-2xl font-black text-slate-800 leading-none mt-0.5">{{ $statHariIni['Hadir'] }}</h3>
            </div>
        </div>
        <!-- Terlambat -->
        <div class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm border border-sky-100 flex items-center gap-3 group hover:shadow-md hover:border-rose-200 hover:-translate-y-0.5 transition-all duration-200">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-rose-300/40 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Terlambat</p>
                <h3 class="text-2xl font-black text-slate-800 leading-none mt-0.5">{{ $statHariIni['Terlambat'] }}</h3>
            </div>
        </div>
        <!-- Izin / Sakit -->
        <div class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm border border-sky-100 flex items-center gap-3 group hover:shadow-md hover:border-amber-200 hover:-translate-y-0.5 transition-all duration-200">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-amber-300/40 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Izin / Sakit</p>
                <h3 class="text-2xl font-black text-slate-800 leading-none mt-0.5">{{ $statHariIni['IzinSakit'] }}</h3>
            </div>
        </div>
        <!-- Alpa -->
        <div class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm border border-sky-100 flex items-center gap-3 group hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-slate-300/40 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alpa</p>
                <h3 class="text-2xl font-black text-slate-800 leading-none mt-0.5">{{ $statHariIni['Alpa'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Bar Chart: 7 Hari Terakhir -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-sky-100 min-w-0 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800">Tren Kehadiran 7 Hari Terakhir</h3>
                <span class="text-[10px] font-bold text-sky-600 bg-sky-50 border border-sky-200 px-2.5 py-1 rounded-full uppercase tracking-widest">Mingguan</span>
            </div>
            <div class="h-[240px] w-full relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        
        <!-- Pie Chart: Komposisi Hari Ini -->
        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-sky-100 min-w-0 overflow-hidden">
            <h3 class="text-sm font-bold text-slate-800 mb-4 text-center">Komposisi Hari Ini</h3>
            <div class="h-[190px] w-full flex items-center justify-center relative">
                <canvas id="todayChart"></canvas>
            </div>
            <div class="mt-4 flex justify-center gap-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Terlambat</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Absen</div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Top Terlambat -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
            <div class="p-4 border-b border-sky-50 flex items-center justify-between bg-sky-50/30">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-rose-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">5 Siswa Sering Terlambat (Bulan Ini)</h3>
                </div>
                <span class="px-2 py-1 rounded-lg bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider">Perlu Perhatian</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-sky-50/50 text-sm">
                        @forelse($topTerlambat as $idx => $t)
                        <tr class="hover:bg-sky-50/30 transition-colors">
                            <td class="p-3 w-12 text-center">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $idx == 0 ? 'bg-amber-100 text-amber-600' : ($idx == 1 ? 'bg-slate-200 text-slate-600' : 'bg-orange-100 text-orange-600') }}">
                                    {{ $idx + 1 }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800 text-sm">{{ $t->siswa->nama_lengkap ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500">NIS: {{ $t->siswa->nis ?? '-' }}</div>
                            </td>
                            <td class="p-3 text-right">
                                <div class="inline-flex items-center gap-1.5 bg-rose-50 border border-rose-100 rounded-lg px-2.5 py-1">
                                    <span class="text-sm font-black text-rose-600">{{ $t->total }}</span>
                                    <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Kali</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-50 mb-2 text-emerald-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">Luar Biasa!</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Belum ada siswa yang terlambat bulan ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Info Card -->
        <div class="bg-gradient-to-br from-sky-500 to-blue-700 rounded-2xl shadow-lg shadow-sky-300/30 overflow-hidden flex flex-col justify-between text-white">
            <div>
                <div class="p-4 border-b border-white/10 flex items-center gap-2">
                    <svg class="w-5 h-5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-sm font-bold text-white">Informasi Sistem</h3>
                </div>
                <div class="p-5 space-y-3.5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-sky-200 uppercase tracking-wider">Versi Aplikasi</span>
                        <span class="text-sm font-black text-white bg-white/20 px-2.5 py-1 rounded-lg">v1.2.0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-sky-200 uppercase tracking-wider">Waktu Server</span>
                        <span class="text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-sm shadow-emerald-400"></span>
                            {{ date('H:i T') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-sky-200 uppercase tracking-wider">Timezone</span>
                        <span class="text-sm font-bold text-white">{{ config('app.timezone') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-sky-200 uppercase tracking-wider">Environment</span>
                        <span class="text-[10px] font-black text-sky-700 bg-white px-2 py-0.5 rounded-md uppercase tracking-widest">{{ app()->environment() }}</span>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-white/10 text-center bg-white/5">
                <p class="text-xs font-semibold text-sky-200">Made with <span class="text-rose-300">❤️</span> by <span class="font-extrabold text-white">iyank</span></p>
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
                    maxBarThickness: 40
                },
                {
                    label: 'Terlambat',
                    data: {!! json_encode($grafikTerlambat) !!},
                    backgroundColor: '#f43f5e', // rose-500
                    borderRadius: 4,
                    borderWidth: 0,
                    maxBarThickness: 40
                },
                {
                    label: 'Izin/Sakit/Alpa',
                    data: {!! json_encode($grafikAbsen) !!},
                    backgroundColor: '#f59e0b', // amber-500
                    borderRadius: 4,
                    borderWidth: 0,
                    maxBarThickness: 40
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
                        font: { family: "'Inter', sans-serif", size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { family: "'Inter', sans-serif", size: 13 },
                    bodyFont: { family: "'Inter', sans-serif", size: 13 },
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { precision: 0, font: { family: "'Inter', sans-serif" } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: "'Inter', sans-serif" } }
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
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: totalToday !== 0,
                    backgroundColor: '#1e293b',
                    padding: 12,
                    bodyFont: { family: "'Inter', sans-serif", size: 14, weight: 'bold' },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + ' Orang';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
