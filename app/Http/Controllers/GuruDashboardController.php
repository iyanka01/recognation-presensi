<?php

namespace App\Http\Controllers;

use App\Models\JadwalPelajaran;
use App\Models\PengaturanJam;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        $today = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->locale('id')->dayName;

        // Jadwal mengajar hari ini (sesuai hari saat ini)
        $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get();

        $now = Carbon::now();
        $tanggalObj = Carbon::today();
        foreach ($jadwalHariIni as $j) {
            $j->total_terisi = PresensiMapel::where('jadwal_id', $j->id)->whereDate('tanggal', $today)->count();
            $j->total_siswa  = $j->kelas ? $j->kelas->siswa()->count() : 0;

            // Cek apakah pas di rentang jam pelajaran hari ini
            $start = $tanggalObj->copy()->setTimeFromTimeString($j->jam_mulai);
            $end   = $tanggalObj->copy()->setTimeFromTimeString($j->jam_selesai);
            $j->can_absen = $now->between($start, $end);
            $j->is_past   = $now->gt($end);
            $j->is_future = $now->lt($start);
        }

        // Statistik ringkas
        $stat = [
            'jadwal_hari_ini' => $jadwalHariIni->count(),
            'total_jadwal'    => JadwalPelajaran::where('guru_id', $guru->id)->count(),
            'kelas_diajar'    => JadwalPelajaran::where('guru_id', $guru->id)->distinct('kelas_id')->count('kelas_id'),
            'sudah_diisi'     => $jadwalHariIni->where('total_terisi', '>', 0)->count(),
        ];

        // Presensi guru hari ini (masuk/pulang)
        $presensiHariIni = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Pengaturan jam sekolah guru hari ini (dengan fallback jika hari belum diatur)
        $pengaturanJam = PengaturanJam::where('tipe', 'guru')
            ->where('hari', $hariIni)
            ->first() ?: PengaturanJam::where('tipe', 'guru')->first();

        return view('dashboard-guru', [
            'guru'             => $guru,
            'jadwalHariIni'    => $jadwalHariIni,
            'hariIni'          => $hariIni,
            'stat'             => $stat,
            'pengaturan'       => PengaturanLembaga::first(),
            'presensiHariIni'  => $presensiHariIni,
            'pengaturanJam'    => $pengaturanJam,
        ]);
    }
}

