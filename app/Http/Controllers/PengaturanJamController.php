<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanJam;
use App\Models\Departemen;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class PengaturanJamController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanJam::with(['kelas', 'departemen'])->orderBy('tipe')->orderBy('hari')->get();
        
        $departemens = Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();
        $kelases = Kelas::with('departemen')->orderBy('tingkat')->orderBy('nama_rombel')->get();
        
        $allTargets = [];
        $allTargets[] = ['id' => 'guru', 'nama' => 'Semua Guru (Umum)', 'tipe' => 'guru'];

        // Tambah target Departemen
        foreach ($departemens as $d) {
            $allTargets[] = [
                'id' => 'dep_' . $d->id,
                'departemen_id' => $d->id,
                'nama' => 'Departemen: ' . $d->nama_departemen,
                'tipe' => 'departemen'
            ];
        }

        // Tambah target Kelas Rombel
        foreach ($kelases as $k) {
            $namaDep = $k->departemen ? (' (' . $k->departemen->nama_departemen . ')') : '';
            $allTargets[] = [
                'id' => $k->id,
                'nama' => 'Kelas ' . $k->nama_rombel . $namaDep,
                'tipe' => 'kelas'
            ];
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $matrix = [];
        $activeTargetIds = [];

        foreach ($pengaturan as $p) {
            if ($p->tipe === 'guru') {
                $key = 'guru';
            } elseif ($p->tipe === 'departemen') {
                $key = 'dep_' . $p->departemen_id;
            } else {
                $key = $p->kelas_id;
            }

            $activeTargetIds[$key] = true;
            
            if (!isset($matrix[$key])) {
                $matrix[$key] = [];
                foreach ($hariList as $h) {
                    $matrix[$key][$h] = null;
                }
            }
            $matrix[$key][$p->hari] = $p;
        }

        $activeTargets = array_filter($allTargets, function($t) use ($activeTargetIds) {
            return isset($activeTargetIds[$t['id']]);
        });

        return view('jam-absensi.index', compact('activeTargets', 'allTargets', 'departemens', 'kelases', 'hariList', 'matrix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_id' => 'required', // 'guru', 'dep_UUID', or UUID of kelas
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_terlambat' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);

        $targetId = $request->target_id;
        $targetsToUpdate = [];

        if ($targetId === 'guru') {
            $targetsToUpdate[] = [
                'tipe' => 'guru',
                'departemen_id' => null,
                'kelas_id' => null
            ];
        } elseif (str_starts_with($targetId, 'dep_')) {
            $depId = substr($targetId, 4);
            $targetsToUpdate[] = [
                'tipe' => 'departemen',
                'departemen_id' => $depId,
                'kelas_id' => null
            ];
        } else {
            $targetsToUpdate[] = [
                'tipe' => 'kelas',
                'departemen_id' => null,
                'kelas_id' => $targetId
            ];
        }

        // Handle copying to other classes
        if ($request->has('copy_to_classes') && is_array($request->copy_to_classes)) {
            foreach ($request->copy_to_classes as $classId) {
                if ($classId !== $targetId) {
                    $targetsToUpdate[] = [
                        'tipe' => 'kelas',
                        'departemen_id' => null,
                        'kelas_id' => $classId
                    ];
                }
            }
        }

        DB::beginTransaction();
        try {
            foreach ($targetsToUpdate as $target) {
                PengaturanJam::updateOrCreate(
                    [
                        'tipe' => $target['tipe'],
                        'departemen_id' => $target['departemen_id'],
                        'kelas_id' => $target['kelas_id'],
                        'hari' => $request->hari,
                    ],
                    [
                        'jam_mulai' => $request->jam_mulai,
                        'jam_terlambat' => $request->jam_terlambat,
                        'jam_pulang' => $request->jam_pulang,
                    ]
                );
            }
            DB::commit();
            return redirect()->route('jam-absensi.index')->with('success', 'Pengaturan jam absensi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $jam = PengaturanJam::findOrFail($id);

        $request->validate([
            'jam_mulai' => 'required|date_format:H:i',
            'jam_terlambat' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);

        $jam->update([
            'jam_mulai' => $request->jam_mulai,
            'jam_terlambat' => $request->jam_terlambat,
            'jam_pulang' => $request->jam_pulang,
        ]);

        return redirect()->route('jam-absensi.index')->with('success', "Jam absensi hari {$jam->hari} berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $jam = PengaturanJam::findOrFail($id);
        $hari = $jam->hari;
        $jam->delete();

        return redirect()->route('jam-absensi.index')->with('success', "Jadwal jam absensi hari {$hari} berhasil dihapus (dijadikan libur)!");
    }
}
