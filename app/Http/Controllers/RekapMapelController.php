<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanLembaga;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapMapelController extends Controller
{
    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $mapelId = $request->query('mapel_id');
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());

        // Rekap agregat per siswa
        $rekap = collect();
        if ($kelasId) {
            $siswaList = Kelas::findOrFail($kelasId)->siswa()->orderBy('nama_lengkap')->get();

            $base = PresensiMapel::query()
                ->selectRaw('siswa_id,
                    SUM(status = "Hadir") as hadir,
                    SUM(status = "Izin") as izin,
                    SUM(status = "Sakit") as sakit,
                    SUM(status = "Alpa") as alpa,
                    COUNT(*) as total')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->when($mapelId, function ($q) use ($mapelId) {
                    $q->whereHas('jadwal', fn($j) => $j->where('mata_pelajaran_id', $mapelId));
                })
                ->groupBy('siswa_id');

            $data = $base->get()->keyBy('siswa_id');

            foreach ($siswaList as $s) {
                $row = $data->get($s->id);
                $total = (int) ($row->total ?? 0);
                $hadir = (int) ($row->hadir ?? 0);
                $rekap->push((object)[
                    'siswa' => $s,
                    'hadir' => $hadir,
                    'izin' => (int) ($row->izin ?? 0),
                    'sakit' => (int) ($row->sakit ?? 0),
                    'alpa' => (int) ($row->alpa ?? 0),
                    'total' => $total,
                    'persen' => $total > 0 ? round($hadir / $total * 100, 1) : null,
                ]);
            }
        }

        return view('rekap-mapel.index', [
            'rekap' => $rekap,
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get(),
            'mapels' => MataPelajaran::orderBy('nama_mapel')->get(),
            'filterKelas' => $kelasId,
            'filterMapel' => $mapelId,
            'dari' => $dari,
            'sampai' => $sampai,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    public function print(Request $request)
    {
        // Halaman cetak memakai logika rekap yang sama, tampilan print-friendly
        return view('rekap-mapel.print', $this->rekapData($request));
    }

    private function rekapData(Request $request): array
    {
        $kelasId = $request->query('kelas_id');
        $mapelId = $request->query('mapel_id');
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());

        $rekap = collect();
        $kelas = null;
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $siswaList = $kelas->siswa()->orderBy('nama_lengkap')->get();

            $data = PresensiMapel::query()
                ->selectRaw("siswa_id,
                    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa,
                    COUNT(*) as total")
                ->whereBetween('tanggal', [$dari, $sampai])
                ->when($mapelId, function ($q) use ($mapelId) {
                    $q->whereHas('jadwal', fn($j) => $j->where('mata_pelajaran_id', $mapelId));
                })
                ->groupBy('siswa_id')
                ->get()
                ->keyBy('siswa_id');

            foreach ($siswaList as $s) {
                $row = $data->get($s->id);
                $total = (int) ($row->total ?? 0);
                $hadir = (int) ($row->hadir ?? 0);
                $rekap->push((object)[
                    'siswa' => $s,
                    'hadir' => $hadir,
                    'izin' => (int) ($row->izin ?? 0),
                    'sakit' => (int) ($row->sakit ?? 0),
                    'alpa' => (int) ($row->alpa ?? 0),
                    'total' => $total,
                    'persen' => $total > 0 ? round($hadir / $total * 100, 1) : null,
                ]);
            }
        }

        return [
            'rekap' => $rekap,
            'kelas' => $kelas,
            'mapel' => $mapelId ? MataPelajaran::find($mapelId) : null,
            'dari' => $dari,
            'sampai' => $sampai,
            'pengaturan' => PengaturanLembaga::first(),
        ];
    }
}
