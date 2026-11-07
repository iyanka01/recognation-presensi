<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe);

        $laporan = [];
        $hariDalamBulan = Carbon::parse($bulan . '-01')->daysInMonth;

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas')->orderBy('nama_lengkap');
            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        // Hitung Hari Efektif (Jumlah hari di mana ada presensi apa pun di bulan ini)
        $hariEfektif = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe)
            ->distinct('tanggal')
            ->count('tanggal');
            
        $denominator = max($hariEfektif, 1);

        // Fetch all presensi for these users in this month to avoid N+1 per user per day
        $allPresensi = $query->get()->groupBy('user_id');

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            
            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();
            $alpa = $userPresensi->where('status', 'Alpa')->count();
            
            $persen = round(($hadir / $denominator) * 100, 1);
            
            $rekap = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen
            ];
            
            $laporan[] = $rekap;
        }

        $kelasList = Kelas::orderBy('nama_rombel')->get();

        return view('laporan.index', compact('laporan', 'kelasList', 'bulan', 'tipe', 'kelas_id'));
    }

    public function print(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe);

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas')->orderBy('nama_lengkap');
            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        $allPresensi = $query->get()->groupBy('user_id');
        $laporan = [];
        
        // Hitung Hari Efektif (Jumlah hari di mana ada presensi apa pun di bulan ini)
        $hariEfektif = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe)
            ->distinct('tanggal')
            ->count('tanggal');
            
        $denominator = max($hariEfektif, 1);

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            
            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();
            $alpa = $userPresensi->where('status', 'Alpa')->count();
            
            $persen = round(($hadir / $denominator) * 100, 1);
            
            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen
            ];
        }

        $kelasStr = 'Semua Kelas';
        if ($tipe == 'siswa' && $kelas_id) {
            $k = Kelas::find($kelas_id);
            $kelasStr = $k ? $k->nama_rombel : 'Semua Kelas';
        }

        $judul = "Laporan Presensi " . ucfirst($tipe) . " - " . Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        $pengaturan = \App\Models\PengaturanLembaga::first();

        return view('laporan.print', compact('laporan', 'judul', 'tipe', 'kelasStr', 'pengaturan'));
    }

    public function printSiswa(Request $request)
    {
        $id = $request->id;
        $tipe = $request->tipe ?? 'siswa';
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');

        if ($tipe == 'siswa') {
            $user = Siswa::with('kelas')->findOrFail($id);
        } else {
            $user = Guru::findOrFail($id);
        }

        $presensi = Presensi::where('user_type', $tipe)
            ->where('user_id', $id)
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->orderBy('tanggal', 'asc')
            ->get();

        $judul = "Laporan Presensi Detail";
        $bulanStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');

        // Hitung Hari Efektif
        $hariEfektif = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe)
            ->distinct('tanggal')
            ->count('tanggal');
            
        $denominator = max($hariEfektif, 1);
        $hadir = $presensi->where('status', 'Hadir')->count();
        $persen = round(($hadir / $denominator) * 100, 1);

        $rekap = [
            'hadir' => $hadir,
            'terlambat' => $presensi->where('status', 'Terlambat')->count(),
            'izin' => $presensi->where('status', 'Izin')->count(),
            'sakit' => $presensi->where('status', 'Sakit')->count(),
            'alpa' => $presensi->where('status', 'Alpa')->count(),
            'persen' => $persen
        ];

        $pengaturan = \App\Models\PengaturanLembaga::first();

        return view('laporan.print_siswa', compact('user', 'tipe', 'bulanStr', 'presensi', 'rekap', 'judul', 'pengaturan'));
    }

    public function export(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe);

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas')->orderBy('nama_lengkap');
            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        $allPresensi = $query->get()->groupBy('user_id');

        $fileName = "rekap_presensi_{$tipe}_{$bulan}.csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nama Lengkap', 'Identitas (NIS/NIP)');
        if ($tipe == 'siswa') {
            $columns[] = 'Kelas';
        }
        $columns = array_merge($columns, ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa']);

        $callback = function() use($users, $allPresensi, $columns, $tipe) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $no = 1;
            foreach ($users as $user) {
                $userPresensi = $allPresensi->get($user->id, collect());
                
                $row = [
                    $no++,
                    $user->nama_lengkap,
                    $tipe == 'siswa' ? $user->nis : $user->nip
                ];
                
                if ($tipe == 'siswa') {
                    $row[] = $user->kelas->nama_rombel ?? '-';
                }

                $row[] = $userPresensi->where('status', 'Hadir')->count();
                $row[] = $userPresensi->where('status', 'Terlambat')->count();
                $row[] = $userPresensi->where('status', 'Izin')->count();
                $row[] = $userPresensi->where('status', 'Sakit')->count();
                $row[] = $userPresensi->where('status', 'Alpa')->count();

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
