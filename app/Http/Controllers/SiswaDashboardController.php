<?php

namespace App\Http\Controllers;

use App\Models\JadwalPelajaran;
use App\Models\PengaturanLembaga;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaDashboardController extends Controller
{
    /**
     * Dashboard Utama Portal Siswa & Wali Murid
     */
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();
        if ($siswa->kelas_id) {
            $siswa->load('kelas.waliKelas');
        }

        $today = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->locale('id')->dayName;

        // Presensi harian hari ini
        $presensiHariIni = Presensi::where('user_type', 'siswa')
            ->where('user_id', $siswa->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Statistik presensi bulan berjalan
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth   = Carbon::now()->endOfMonth()->toDateString();

        $presensiBulanIni = Presensi::where('user_type', 'siswa')
            ->where('user_id', $siswa->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $hadir     = $presensiBulanIni->where('status', 'Hadir')->count();
        $terlambat = $presensiBulanIni->where('status', 'Terlambat')->count();
        $sakit     = $presensiBulanIni->where('status', 'Sakit')->count();
        $izin      = $presensiBulanIni->where('status', 'Izin')->count();
        $alpa      = $presensiBulanIni->where('status', 'Alpa')->count();
        $totalRecord = $presensiBulanIni->count();

        $persentaseKehadiran = $totalRecord > 0
            ? round((($hadir + $terlambat) / $totalRecord) * 100, 1)
            : 100;

        // Pengumuman sekolah aktif terbaru
        $pengumuman = Pengumuman::where('is_aktif', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Jadwal pelajaran hari ini
        $jadwalHariIni = $siswa->kelas_id
            ? JadwalPelajaran::where('kelas_id', $siswa->kelas_id)
                ->where('hari', $hariIni)
                ->with(['mataPelajaran', 'guru'])
                ->orderBy('jam_mulai')
                ->get()
            : collect();

        // Cek apakah siswa belum pernah ganti password (masih default siswa123)
        $needChangePassword = !$siswa->is_password_changed;

        return view('siswa-portal.dashboard', [
            'siswa'               => $siswa,
            'presensiHariIni'     => $presensiHariIni,
            'stat'                => [
                'hadir'      => $hadir,
                'terlambat'  => $terlambat,
                'sakit'      => $sakit,
                'izin'       => $izin,
                'alpa'       => $alpa,
                'persentase' => $persentaseKehadiran,
            ],
            'pengumuman'          => $pengumuman,
            'jadwalHariIni'       => $jadwalHariIni,
            'hariIni'             => $hariIni,
            'needChangePassword'  => $needChangePassword,
            'lembaga'             => PengaturanLembaga::first(),
        ]);
    }

    /**
     * Riwayat Lengkap Presensi Harian & Mapel Siswa
     */
    public function presensi(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        if ($siswa->kelas_id) {
            $siswa->load('kelas.waliKelas');
        }

        // Filter Rentang Tanggal (default: awal tahun / bulan berjalan s.d hari ini)
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        if (!$dari || !$sampai) {
            $dari = Carbon::now()->startOfYear()->toDateString();
            $sampai = Carbon::now()->toDateString();
        }

        // Riwayat presensi harian
        $riwayatHarian = Presensi::where('user_type', 'siswa')
            ->where('user_id', $siswa->id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Riwayat presensi mata pelajaran (E-KBM)
        $riwayatMapel = PresensiMapel::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->with(['jadwal.mataPelajaran', 'jadwal.guru', 'jadwal.kelas'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('siswa-portal.presensi', [
            'siswa'          => $siswa,
            'riwayatHarian'  => $riwayatHarian,
            'riwayatMapel'   => $riwayatMapel,
            'dari'           => $dari,
            'sampai'         => $sampai,
            'lembaga'        => PengaturanLembaga::first(),
        ]);
    }

    /**
     * Cetak Laporan Kehadiran EKBM Siswa (Format Resmi Kop & Tanda Tangan)
     */
    public function cetak(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        if ($siswa->kelas_id) {
            $siswa->load('kelas.waliKelas');
        }

        $dari = $request->query('dari', Carbon::now()->startOfYear()->toDateString());
        $sampai = $request->query('sampai', Carbon::now()->toDateString());

        // Ambil data kehadiran E-KBM
        $dataMapel = PresensiMapel::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->with(['jadwal.mataPelajaran', 'jadwal.guru', 'jadwal.kelas'])
            ->orderBy('tanggal', 'asc')
            ->get();

        $lembaga = PengaturanLembaga::first();

        return view('siswa-portal.cetak-ekbm', [
            'siswa'     => $siswa,
            'dataMapel' => $dataMapel,
            'dari'      => $dari,
            'sampai'    => $sampai,
            'lembaga'   => $lembaga,
        ]);
    }

    /**
     * Tampilkan Kartu Nama / ID Card Siswa
     */
    public function kartuNama()
    {
        $user = Auth::guard('siswa')->user();
        if ($user->kelas_id) {
            $user->load('kelas');
        }
        $type = 'siswa';
        return view('id-card', compact('user', 'type'));
    }


    /**
     * Jadwal Pelajaran Mingguan Kelas Siswa
     */
    public function jadwal()
    {
        $siswa = Auth::guard('siswa')->user();
        if ($siswa->kelas_id) {
            $siswa->load('kelas');
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $jadwalMingguan = $siswa->kelas_id
            ? JadwalPelajaran::where('kelas_id', $siswa->kelas_id)
                ->with(['mataPelajaran', 'guru'])
                ->orderBy('jam_mulai')
                ->get()
                ->groupBy('hari')
            : collect();

        return view('siswa-portal.jadwal', [
            'siswa'          => $siswa,
            'hariList'       => $hariList,
            'jadwalMingguan' => $jadwalMingguan,
            'lembaga'        => PengaturanLembaga::first(),
        ]);
    }
}
