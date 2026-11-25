<?php

namespace App\Services\Presensi;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\PengaturanJam;
use App\Models\Siswa;

class PresensiScheduleService
{
    /**
     * Cek apakah tanggal tertentu adalah hari libur.
     */
    public function cekHariLibur(string $tanggal): ?HariLibur
    {
        return HariLibur::where('tanggal', $tanggal)->first();
    }

    /**
     * Ambil pengaturan jam presensi berdasarkan hierarki:
     * Siswa: Kelas -> Departemen
     * Guru: Departemen -> Guru Umum
     */
    public function getPengaturanJam(Siswa|Guru $user, string $userType, string $hari): ?PengaturanJam
    {
        if ($userType === 'siswa') {
            $kelas = $user->kelas;
            $departemenId = $kelas?->departemen_id;

            $pengaturan = PengaturanJam::where('tipe', 'kelas')
                ->where('kelas_id', $user->kelas_id)
                ->where('hari', $hari)
                ->first();

            if (!$pengaturan && $departemenId) {
                $pengaturan = PengaturanJam::where('tipe', 'departemen')
                    ->where('departemen_id', $departemenId)
                    ->where('hari', $hari)
                    ->first();
            }

            return $pengaturan;
        }

        $departemenId = $user->departemen_id ?? null;
        $pengaturan = null;

        if ($departemenId) {
            $pengaturan = PengaturanJam::where('tipe', 'departemen')
                ->where('departemen_id', $departemenId)
                ->where('hari', $hari)
                ->first();
        }

        if (!$pengaturan) {
            $pengaturan = PengaturanJam::where('tipe', 'guru')
                ->where('hari', $hari)
                ->first() ?: PengaturanJam::where('tipe', 'guru')->first();
        }

        return $pengaturan;
    }
}
