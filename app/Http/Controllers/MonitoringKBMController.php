<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringKBMController extends Controller
{
    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());
        $hariPilihan = Carbon::parse($tanggal)->locale('id')->dayName;

        $filterKelas = $request->query('kelas_id');
        $filterGuru = $request->query('guru_id');

        $query = JadwalPelajaran::with(['kelas.siswa', 'mataPelajaran', 'guru'])
            ->where('hari', $hariPilihan)
            ->orderBy('jam_mulai');

        if ($filterKelas) {
            $query->where('kelas_id', $filterKelas);
        }

        if ($filterGuru) {
            $query->where('guru_id', $filterGuru);
        }

        $jadwals = $query->get();

        $now = Carbon::now();
        $isToday = ($tanggal === Carbon::today()->toDateString());
        $tanggalObj = Carbon::parse($tanggal);

        $liveCount = 0;
        $diisiCount = 0;
        $belumDiisiCount = 0;

        foreach ($jadwals as $j) {
            $start = $tanggalObj->copy()->setTimeFromTimeString($j->jam_mulai);
            $end   = $tanggalObj->copy()->setTimeFromTimeString($j->jam_selesai);

            $j->is_live = $isToday && $now->between($start, $end);
            $j->is_upcoming = $isToday && $now->lt($start);
            $j->is_finished = $isToday && $now->gt($end);

            if ($j->is_live) {
                $liveCount++;
            }

            // Data presensi mapel
            $presensiList = PresensiMapel::where('jadwal_id', $j->id)
                ->whereDate('tanggal', $tanggal)
                ->get();

            $j->hadir_count = $presensiList->where('status', 'Hadir')->count();
            $j->izin_count  = $presensiList->where('status', 'Izin')->count();
            $j->sakit_count = $presensiList->where('status', 'Sakit')->count();
            $j->alpa_count  = $presensiList->where('status', 'Alpa')->count();
            $j->total_terisi = $presensiList->count();
            $j->total_siswa  = $j->kelas ? $j->kelas->siswa->count() : 0;

            // Jurnal
            $j->jurnal = JurnalMengajar::where('jadwal_id', $j->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            $j->is_filled = ($j->total_terisi > 0 || $j->jurnal !== null);
            if ($j->is_filled) {
                $diisiCount++;
            } else {
                $belumDiisiCount++;
            }
        }

        return view('monitoring-kbm.index', [
            'jadwals' => $jadwals,
            'tanggal' => $tanggal,
            'hari' => $hariPilihan,
            'isToday' => $isToday,
            'liveCount' => $liveCount,
            'diisiCount' => $diisiCount,
            'belumDiisiCount' => $belumDiisiCount,
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get(),
            'guruList' => Guru::orderBy('nama_lengkap')->get(),
            'filterKelas' => $filterKelas,
            'filterGuru' => $filterGuru,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }
}
