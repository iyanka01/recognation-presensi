<?php

namespace App\Http\Controllers;

use App\Models\JadwalPelajaran;
use App\Models\PengaturanLembaga;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruJadwalController extends Controller
{
    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(Request $request)
    {
        $guru = Auth::guard('guru')->user();
        $hariIni = Carbon::now()->locale('id')->dayName;

        // Ambil filter hari aktif dari request, default hari ini jika ada jadwal, atau hari pertama yang memiliki jadwal
        $selectedHari = $request->query('hari', $hariIni);
        if (!in_array($selectedHari, self::HARI)) {
            $selectedHari = $hariIni;
        }

        // Ambil seluruh jadwal guru ini
        $allJadwals = JadwalPelajaran::with(['kelas.siswa', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->orderBy('jam_mulai')
            ->get();

        // Kelompokkan per hari
        $jadwalPerHari = [];
        foreach (self::HARI as $h) {
            $jadwalPerHari[$h] = $allJadwals->where('hari', $h)->values();
        }

        $now = Carbon::now();
        $todayStr = Carbon::today()->toDateString();

        // Tambahkan metadata status jam untuk hari terpilih jika hari terpilih adalah hari ini
        foreach ($jadwalPerHari[$selectedHari] as $j) {
            $j->total_terisi = PresensiMapel::where('jadwal_id', $j->id)->whereDate('tanggal', $todayStr)->count();
            $j->total_siswa = $j->kelas ? $j->kelas->siswa->count() : 0;

            if ($selectedHari === $hariIni) {
                $start = Carbon::today()->setTimeFromTimeString($j->jam_mulai);
                $end   = Carbon::today()->setTimeFromTimeString($j->jam_selesai);
                $j->can_absen = $now->between($start, $end);
                $j->is_past   = $now->gt($end);
                $j->is_future = $now->lt($start);
            } else {
                $j->can_absen = false;
                $j->is_past = false;
                $j->is_future = false;
            }
        }

        // Ringkasan statistik guru
        $totalJadwal = $allJadwals->count();
        $kelasCount = $allJadwals->pluck('kelas_id')->unique()->count();
        $mapelCount = $allJadwals->pluck('mata_pelajaran_id')->unique()->count();

        return view('guru.jadwal.index', [
            'guru' => $guru,
            'hariList' => self::HARI,
            'hariIni' => $hariIni,
            'selectedHari' => $selectedHari,
            'jadwalPerHari' => $jadwalPerHari,
            'currentJadwals' => $jadwalPerHari[$selectedHari],
            'totalJadwal' => $totalJadwal,
            'kelasCount' => $kelasCount,
            'mapelCount' => $mapelCount,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }
}
