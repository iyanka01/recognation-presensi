<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\PengaturanLembaga;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapJamGuruController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getRekapData($request);
        return view('rekap-jam-guru.index', $data);
    }

    public function print(Request $request)
    {
        $data = $this->getRekapData($request);
        return view('rekap-jam-guru.print', $data);
    }

    public function printDetail(Request $request, $guru_id)
    {
        $guru = Guru::findOrFail($guru_id);
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());

        // Ambil riwayat pelaksanaan mengajar (jurnal)
        $jurnals = JurnalMengajar::with(['jadwal.mataPelajaran', 'jadwal.kelas'])
            ->whereHas('jadwal', function ($q) use ($guru_id) {
                $q->where('guru_id', $guru_id);
            })
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalJam = $jurnals->sum('jumlah_jam');
        $totalPertemuan = $jurnals->count();

        return view('rekap-jam-guru.print-detail', [
            'guru' => $guru,
            'jurnals' => $jurnals,
            'totalJam' => $totalJam,
            'totalPertemuan' => $totalPertemuan,
            'dari' => $dari,
            'sampai' => $sampai,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    private function getRekapData(Request $request): array
    {
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());
        $guruId = $request->query('guru_id');

        $guruQuery = Guru::query()->orderBy('nama_lengkap');
        if ($guruId) {
            $guruQuery->where('id', $guruId);
        }
        $gurus = $guruQuery->get();

        // Ambil semua jurnal mengajar dalam rentang tanggal
        $jurnals = JurnalMengajar::with(['jadwal.mataPelajaran', 'jadwal.kelas'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get();

        $rekap = collect();

        foreach ($gurus as $g) {
            // Filter jurnal milik guru ini
            $guruJurnals = $jurnals->filter(function ($j) use ($g) {
                return $j->jadwal && $j->jadwal->guru_id === $g->id;
            });

            // Jadwal aktif mingguan guru
            $jadwals = JadwalPelajaran::where('guru_id', $g->id)->with(['mataPelajaran', 'kelas'])->get();

            // Rincian per mapel yang diajar
            $mapelSummary = [];
            foreach ($guruJurnals as $jurnal) {
                $mapelNama = $jurnal->jadwal->mataPelajaran->nama_mapel ?? 'Mapel Lain';
                if (!isset($mapelSummary[$mapelNama])) {
                    $mapelSummary[$mapelNama] = [
                        'nama_mapel' => $mapelNama,
                        'total_jam' => 0,
                        'total_pertemuan' => 0,
                    ];
                }
                $mapelSummary[$mapelNama]['total_jam'] += (int) $jurnal->jumlah_jam;
                $mapelSummary[$mapelNama]['total_pertemuan'] += 1;
            }

            $totalJam = $guruJurnals->sum('jumlah_jam');
            $totalPertemuan = $guruJurnals->count();

            $rekap->push((object)[
                'guru' => $g,
                'total_jam' => $totalJam,
                'total_pertemuan' => $totalPertemuan,
                'total_jadwal_mingguan' => $jadwals->count(),
                'mapel_summary' => array_values($mapelSummary),
            ]);
        }

        return [
            'rekap' => $rekap,
            'gurus' => Guru::orderBy('nama_lengkap')->get(),
            'dari' => $dari,
            'sampai' => $sampai,
            'filterGuru' => $guruId,
            'totalSemuaJam' => $rekap->sum('total_jam'),
            'totalSemuaPertemuan' => $rekap->sum('total_pertemuan'),
            'pengaturan' => PengaturanLembaga::first(),
        ];
    }
}
