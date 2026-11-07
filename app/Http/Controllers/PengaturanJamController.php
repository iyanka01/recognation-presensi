<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanJam;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class PengaturanJamController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanJam::with('kelas')->orderBy('tipe')->orderBy('hari')->get();
        
        $kelases = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get();
        
        $allTargets = [];
        $allTargets[] = ['id' => 'guru', 'nama' => 'Semua Guru', 'tipe' => 'guru'];
        foreach ($kelases as $k) {
            $allTargets[] = ['id' => $k->id, 'nama' => 'Kelas ' . $k->nama_rombel, 'tipe' => 'kelas'];
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $matrix = [];
        $activeTargetIds = [];

        foreach ($pengaturan as $p) {
            $key = $p->tipe === 'guru' ? 'guru' : $p->kelas_id;
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

        return view('jam-absensi.index', compact('activeTargets', 'allTargets', 'kelases', 'hariList', 'matrix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_id' => 'required', // 'guru' or uuid of kelas
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_terlambat' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);

        $targetsToUpdate = [];
        $tipeUtama = $request->target_id === 'guru' ? 'guru' : 'kelas';
        $kelasIdUtama = $request->target_id === 'guru' ? null : $request->target_id;

        $targetsToUpdate[] = [
            'tipe' => $tipeUtama,
            'kelas_id' => $kelasIdUtama
        ];

        // Handle copying to other classes
        if ($request->has('copy_to_classes') && is_array($request->copy_to_classes)) {
            foreach ($request->copy_to_classes as $classId) {
                // Prevent duplicate if the user accidentally checked the primary target
                if ($classId !== $kelasIdUtama) {
                    $targetsToUpdate[] = [
                        'tipe' => 'kelas',
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
}
