<?php

namespace App\Services\AbsensiMapel;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AbsensiMapelService
{
    const STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa'];

    /**
     * Cek apakah guru sudah melakukan absensi masuk pada hari ini
     */
    public function sudahAbsenMasukHariIni(string|int $guruId, string $todayDate): bool
    {
        return Presensi::where('user_type', 'guru')
            ->where('user_id', $guruId)
            ->whereDate('tanggal', $todayDate)
            ->whereNotNull('jam_masuk')
            ->exists();
    }

    /**
     * Mempersiapkan list jadwal mengajar guru pada tanggal tertentu beserta status waktunya
     */
    public function getJadwalMengajarList(Guru $guru, string $tanggal): array
    {
        $hari = Carbon::parse($tanggal)->locale('id')->dayName;
        $today = Carbon::today()->toDateString();
        $sudahAbsenMasuk = $this->sudahAbsenMasukHariIni($guru->id, $today);

        $jadwals = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        $now = Carbon::now();
        $tanggalObj = Carbon::parse($tanggal);
        $isToday = ($tanggal === $today);

        foreach ($jadwals as $j) {
            $j->total_terisi = PresensiMapel::where('jadwal_id', $j->id)->whereDate('tanggal', $tanggal)->count();
            $j->total_siswa = $j->kelas ? $j->kelas->siswa()->count() : 0;

            $start = $tanggalObj->copy()->setTimeFromTimeString($j->jam_mulai);
            $end   = $tanggalObj->copy()->setTimeFromTimeString($j->jam_selesai);

            if ($isToday) {
                $j->can_absen = $sudahAbsenMasuk && $now->between($start, $end);
                $j->belum_absen_masuk = !$sudahAbsenMasuk;
                $j->is_past   = $now->gt($end);
                $j->is_future = $now->lt($start);
            } elseif ($tanggalObj->startOfDay()->gt(Carbon::today())) {
                $j->can_absen = false;
                $j->belum_absen_masuk = false;
                $j->is_past   = false;
                $j->is_future = true;
            } else {
                $j->can_absen = false;
                $j->belum_absen_masuk = false;
                $j->is_past   = true;
                $j->is_future = false;
            }
        }

        return [
            'jadwals' => $jadwals,
            'tanggal' => $tanggal,
            'hari' => $hari,
            'sudahAbsenMasuk' => $sudahAbsenMasuk,
            'pengaturan' => PengaturanLembaga::first(),
        ];
    }

    /**
     * Evaluasi hak akses dan kelayakan waktu sesi absensi mapel
     */
    public function validateAccessAndTiming(JadwalPelajaran $jadwal, Guru $guru, string $tanggal): array
    {
        $isOwner = ($jadwal->guru_id === $guru->id);
        $isWaliKelas = ($jadwal->kelas && $jadwal->kelas->wali_kelas_id === $guru->id);

        if (!$isOwner && !$isWaliKelas) {
            return [
                'allowed' => false,
                'status_code' => 403,
                'error' => 'Anda tidak memiliki akses ke jadwal pelajaran ini.',
            ];
        }

        $isToday = ($tanggal === Carbon::today()->toDateString());
        if ($isToday && !$this->sudahAbsenMasukHariIni($guru->id, Carbon::today()->toDateString())) {
            return [
                'allowed' => false,
                'redirect_dashboard' => true,
                'error' => '⚠️ Akses Ditolak: Anda belum melakukan Absensi Masuk hari ini. Silakan tap "Jam Masuk" untuk absen terlebih dahulu sebelum mengisi absensi mata pelajaran!',
            ];
        }

        $hariPilihan = Carbon::parse($tanggal)->locale('id')->dayName;
        $now = Carbon::now();
        $tanggalObj = Carbon::parse($tanggal);
        $start = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_mulai);
        $end   = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_selesai);

        $isHariCocok = (strtolower($jadwal->hari) === strtolower($hariPilihan));
        $canAbsen = $isToday && $isHariCocok && $now->between($start, $end);

        if (!$canAbsen && !$isWaliKelas) {
            $jamRange = substr($jadwal->jam_mulai, 0, 5) . " - " . substr($jadwal->jam_selesai, 0, 5);
            if ($isToday && $now->lt($start)) {
                $pesan = "Akses Ditolak: Belum waktunya jam pelajaran {$jadwal->mataPelajaran?->nama_mapel} ({$jadwal->hari}, {$jamRange}). Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.";
            } else {
                $pesan = "Akses Ditolak: Anda sudah melewati batas waktu absensi untuk {$jadwal->mataPelajaran?->nama_mapel} ({$jadwal->hari}, {$jamRange}). Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.";
            }

            return [
                'allowed' => false,
                'redirect_index' => true,
                'error' => $pesan,
            ];
        }

        return [
            'allowed' => true,
            'canAbsen' => $canAbsen,
            'isWaliKelas' => $isWaliKelas,
        ];
    }

    /**
     * Mempersiapkan form absensi mapel
     */
    public function getFormData(JadwalPelajaran $jadwal, string $tanggal, bool $canAbsen, bool $isWaliKelas): array
    {
        $hasAbsen = PresensiMapel::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->exists();

        $existing = PresensiMapel::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $jurnal = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        return [
            'jadwal' => $jadwal,
            'canAbsen' => $canAbsen,
            'isWaliKelas' => $isWaliKelas,
            'hasAbsen' => $hasAbsen,
            'siswaList' => $jadwal->kelas ? $jadwal->kelas->siswa()->orderBy('nama_lengkap')->get() : collect(),
            'existing' => $existing,
            'jurnal' => $jurnal,
            'tanggal' => $tanggal,
            'statusList' => self::STATUS,
            'pengaturan' => PengaturanLembaga::first(),
        ];
    }

    /**
     * Simpan jurnal mengajar dan catatan absensi siswa
     */
    public function simpanAbsensiDanJurnal(JadwalPelajaran $jadwal, array $validated): void
    {
        JurnalMengajar::updateOrCreate(
            [
                'jadwal_id' => $jadwal->id,
                'tanggal' => $validated['tanggal'],
            ],
            [
                'jumlah_jam' => $validated['jumlah_jam'],
                'materi' => $validated['materi'],
                'materi_selanjutnya' => $validated['materi_selanjutnya'] ?? null,
                'keterangan' => $validated['keterangan_pembelajaran'],
            ]
        );

        foreach ($validated['absensi'] as $row) {
            PresensiMapel::updateOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'siswa_id' => $row['siswa_id'],
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'status' => $row['status'],
                    'keterangan' => $row['keterangan'] ?? null,
                ]
            );
        }
    }
}
