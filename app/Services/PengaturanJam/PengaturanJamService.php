<?php

namespace App\Services\PengaturanJam;

use App\Models\Departemen;
use App\Models\Kelas;
use App\Models\PengaturanJam;
use Illuminate\Support\Facades\DB;

class PengaturanJamService
{
    const HARI_LIST = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function getMatrixData(): array
    {
        $pengaturan = PengaturanJam::with(['kelas', 'departemen'])->orderBy('tipe')->orderBy('hari')->get();
        $departemens = Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();

        $targetDepartemen = [];
        foreach ($departemens as $d) {
            $targetDepartemen[] = [
                'id' => 'dep_' . $d->id,
                'departemen_id' => $d->id,
                'nama' => $d->nama_departemen,
                'tipe' => 'departemen'
            ];
        }

        $targetGuru = [
            ['id' => 'guru', 'nama' => 'Semua Guru (Umum)', 'tipe' => 'guru']
        ];

        $allTargets = array_merge($targetDepartemen, $targetGuru);

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
                foreach (self::HARI_LIST as $h) {
                    $matrix[$key][$h] = null;
                }
            }
            $matrix[$key][$p->hari] = $p;
        }

        $activeTargets = array_filter($allTargets, function ($t) use ($activeTargetIds) {
            return isset($activeTargetIds[$t['id']]);
        });

        return [
            'activeTargets' => $activeTargets,
            'allTargets' => $allTargets,
            'targetDepartemen' => $targetDepartemen,
            'targetGuru' => $targetGuru,
            'departemens' => $departemens,
            'hariList' => self::HARI_LIST,
            'matrix' => $matrix,
        ];
    }

    public function saveSchedule(array $validated): void
    {
        $targetId = $validated['target_id'];
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

        if (!empty($validated['copy_to_departemens']) && is_array($validated['copy_to_departemens'])) {
            $currentDepId = str_starts_with($targetId, 'dep_') ? substr($targetId, 4) : null;
            foreach ($validated['copy_to_departemens'] as $depId) {
                if ($depId !== $currentDepId) {
                    $targetsToUpdate[] = [
                        'tipe' => 'departemen',
                        'departemen_id' => $depId,
                        'kelas_id' => null
                    ];
                }
            }
        }

        if (!empty($validated['copy_to_classes']) && is_array($validated['copy_to_classes'])) {
            foreach ($validated['copy_to_classes'] as $classId) {
                if ($classId !== $targetId) {
                    $targetsToUpdate[] = [
                        'tipe' => 'kelas',
                        'departemen_id' => null,
                        'kelas_id' => $classId
                    ];
                }
            }
        }

        DB::transaction(function () use ($targetsToUpdate, $validated) {
            foreach ($targetsToUpdate as $target) {
                PengaturanJam::updateOrCreate(
                    [
                        'tipe' => $target['tipe'],
                        'departemen_id' => $target['departemen_id'],
                        'kelas_id' => $target['kelas_id'],
                        'hari' => $validated['hari'],
                    ],
                    [
                        'jam_mulai' => $validated['jam_mulai'],
                        'jam_terlambat' => $validated['jam_terlambat'],
                        'jam_pulang' => $validated['jam_pulang'],
                    ]
                );
            }
        });
    }

    public function updateSchedule(PengaturanJam $jam, array $validated): bool
    {
        return $jam->update([
            'jam_mulai' => $validated['jam_mulai'],
            'jam_terlambat' => $validated['jam_terlambat'],
            'jam_pulang' => $validated['jam_pulang'],
        ]);
    }

    public function deleteSchedule(PengaturanJam $jam): ?bool
    {
        return $jam->delete();
    }
}
