<?php

namespace App\Services\Jadwal;

use App\Models\Departemen;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JadwalService
{
    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /**
     * Mempersiapkan data untuk view index jadwal
     */
    public function getIndexData(?string $departemenId, ?string $kelasId, ?string $hari): array
    {
        $departemens = Departemen::orderBy('nama_departemen')->get();

        if (!$departemenId && $departemens->isNotEmpty()) {
            $departemenId = $departemens->first()->id;
        }

        $kelasList = collect();
        if ($departemenId && $departemenId !== 'all') {
            $kelasList = Kelas::where('departemen_id', $departemenId)->orderBy('tingkat')->orderBy('nama_rombel')->get();
        } else {
            $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get();
        }

        if ($kelasId && !$kelasList->contains('id', $kelasId)) {
            $kelasId = null;
        }

        $jadwals = JadwalPelajaran::with(['kelas.departemen', 'mataPelajaran', 'guru'])
            ->when($departemenId && $departemenId !== 'all', function ($q) use ($departemenId) {
                $q->whereHas('kelas', fn($k) => $k->where('departemen_id', $departemenId));
            })
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($hari, fn($q) => $q->where('hari', $hari))
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        $namaHariIni = Carbon::now()->locale('id')->dayName;
        $activeHari = $hari ?: (in_array($namaHariIni, self::HARI) && $namaHariIni !== 'Minggu' ? $namaHariIni : 'Senin');

        return [
            'jadwals' => $jadwals,
            'departemens' => $departemens,
            'filterDepartemen' => $departemenId,
            'kelasList' => $kelasList,
            'mapels' => MataPelajaran::orderBy('nama_mapel')->get(),
            'gurus' => Guru::where('status', 'Aktif')->orderBy('nama_lengkap')->get(),
            'hariList' => self::HARI,
            'filterKelas' => $kelasId,
            'filterHari' => $hari,
            'activeHari' => $activeHari,
        ];
    }

    /**
     * Cek jadwal bentrok: kelas atau guru yang sama pada hari & rentang jam yang tumpang tindih
     */
    public function cekBentrok(array $data, ?string $ignoreId = null): ?string
    {
        $query = JadwalPelajaran::where('hari', $data['hari'])
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->where(function ($q) use ($data) {
                $q->where('kelas_id', $data['kelas_id']);
                if (!empty($data['guru_id'])) {
                    $q->orWhere('guru_id', $data['guru_id']);
                }
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $bentrok = $query->with(['kelas', 'mataPelajaran'])->first();
        if (!$bentrok) {
            return null;
        }

        if ($bentrok->guru_id === ($data['guru_id'] ?? null) && $bentrok->kelas_id !== $data['kelas_id']) {
            $target = 'Guru pengajar tersebut sudah mengajar di kelas lain';
        } else {
            $target = "Kelas {$bentrok->kelas->tingkat} {$bentrok->kelas->nama_rombel}";
        }

        return "{$target} sudah memiliki jadwal {$bentrok->mataPelajaran->nama_mapel} pada hari {$bentrok->hari}, "
            . substr($bentrok->jam_mulai, 0, 5) . ' - ' . substr($bentrok->jam_selesai, 0, 5)
            . '. Silakan pilih waktu lain.';
    }

    /**
     * Simpan jadwal baru
     */
    public function store(array $validated): JadwalPelajaran
    {
        return JadwalPelajaran::create($validated);
    }

    /**
     * Update jadwal yang ada
     */
    public function update(JadwalPelajaran $jadwal, array $validated): bool
    {
        return $jadwal->update($validated);
    }

    /**
     * Hapus jadwal
     */
    public function delete(JadwalPelajaran $jadwal): ?bool
    {
        return $jadwal->delete();
    }
}
