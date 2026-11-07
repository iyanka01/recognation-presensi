<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Guru login → tampilkan dashboard guru
        if (Auth::guard('guru')->check()) {
            return app(\App\Http\Controllers\GuruDashboardController::class)->index();
        }

        $today = Carbon::today()->toDateString();
        
        // Statistik Hari Ini (Semua User)
        $statHariIni = [
            'Hadir' => Presensi::where('tanggal', $today)->where('status', 'Hadir')->count(),
            'Terlambat' => Presensi::where('tanggal', $today)->where('status', 'Terlambat')->count(),
            'IzinSakit' => Presensi::where('tanggal', $today)->whereIn('status', ['Izin', 'Sakit'])->count(),
            'Alpa' => Presensi::where('tanggal', $today)->where('status', 'Alpa')->count(),
        ];
        
        // Total Siswa & Guru Aktif
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();

        // Data Grafik: 7 Hari Terakhir
        $grafikTanggal = [];
        $grafikHadir = [];
        $grafikTerlambat = [];
        $grafikAbsen = []; // Izin/Sakit/Alpa
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            
            $grafikTanggal[] = $date->locale('id')->isoFormat('D MMM');
            
            // Query per hari
            $hadir = Presensi::where('tanggal', $dateStr)->where('status', 'Hadir')->count();
            $terlambat = Presensi::where('tanggal', $dateStr)->where('status', 'Terlambat')->count();
            $absen = Presensi::where('tanggal', $dateStr)->whereIn('status', ['Izin', 'Sakit', 'Alpa'])->count();
            
            $grafikHadir[] = $hadir;
            $grafikTerlambat[] = $terlambat;
            $grafikAbsen[] = $absen;
        }

        // Top 5 Siswa Sering Terlambat (Bulan Ini)
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        $topTerlambat = Presensi::where('user_type', 'siswa')
            ->where('status', 'Terlambat')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
            
        // Load data siswa untuk topTerlambat
        foreach($topTerlambat as $tt) {
            $tt->siswa = Siswa::find($tt->user_id);
        }

        return view('dashboard', compact('statHariIni', 'totalSiswa', 'totalGuru', 'grafikTanggal', 'grafikHadir', 'grafikTerlambat', 'grafikAbsen', 'topTerlambat'));
    }
}
