<?php

namespace App\Services\WhatsAppBroadcast;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PeringatanGuru;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WhatsAppBroadcastService
{
    /**
     * Menghasilkan data statistik kehadiran siswa per kelas/tanggal
     */
    public function generateAttendanceData(string $tanggal, string $kelasId): array
    {
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();
        $defaultNama = $lembaga && !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Sistem Presensi Sekolah');

        $siswaQuery = Siswa::with('kelas');
        $targetKelas = null;

        if ($kelasId !== 'all') {
            $siswaQuery->where('kelas_id', $kelasId);
            $targetKelas = Kelas::with(['waliKelas', 'departemen'])->find($kelasId);
        }

        $namaLembaga = $targetKelas?->departemen?->nama_departemen ?: $defaultNama;

        $allSiswa = $siswaQuery->get();
        $totalSiswa = $allSiswa->count();
        $siswaIds = $allSiswa->pluck('id')->toArray();

        $presensiList = Presensi::where('user_type', 'siswa')
            ->whereIn('user_id', $siswaIds)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('user_id');

        $hadirList = [];
        $terlambatList = [];
        $sakitList = [];
        $izinList = [];
        $alpaList = [];

        foreach ($allSiswa as $siswa) {
            if (isset($presensiList[$siswa->id])) {
                $p = $presensiList[$siswa->id];
                $st = strtolower($p->status);

                if ($st === 'hadir') {
                    $hadirList[] = $siswa;
                } elseif ($st === 'terlambat') {
                    $terlambatList[] = $siswa;
                } elseif ($st === 'sakit') {
                    $sakitList[] = [
                        'siswa' => $siswa,
                        'keterangan' => $p->keterangan ?? 'Sakit'
                    ];
                } elseif (in_array($st, ['izin', 'cuti', 'dispensasi', 'dinas luar'])) {
                    $izinList[] = [
                        'siswa' => $siswa,
                        'keterangan' => $p->keterangan ?? ucfirst($p->status)
                    ];
                } else {
                    $alpaList[] = $siswa;
                }
            } else {
                $alpaList[] = $siswa;
            }
        }

        return [
            'tanggal'        => $tanggal,
            'kelasId'        => $kelasId,
            'targetKelas'    => $targetKelas,
            'namaLembaga'    => $namaLembaga,
            'stats'          => [
                'total'      => $totalSiswa,
                'hadir'      => count($hadirList),
                'terlambat'  => count($terlambatList),
                'sakit'      => count($sakitList),
                'izin'       => count($izinList),
                'alpa'       => count($alpaList),
            ],
            'hadirList'      => $hadirList,
            'terlambatList'  => $terlambatList,
            'sakitList'      => $sakitList,
            'izinList'       => $izinList,
            'alpaList'       => $alpaList,
        ];
    }

    /**
     * Membangun format teks pesan WhatsApp yang rapi
     */
    public function buildWhatsAppMessage(array $data, string $customNote = ''): string
    {
        $tglFormatted = Carbon::parse($data['tanggal'])->locale('id')->isoFormat('dddd, D MMMM Y');
        $namaSekolah = $data['namaLembaga'];

        $namaTarget = "Semua Kelas";
        if (!empty($data['targetKelas'])) {
            $namaTarget = "Kelas {$data['targetKelas']->tingkat} {$data['targetKelas']->nama_rombel}";
            if ($data['targetKelas']->waliKelas) {
                $namaTarget .= " (Wali Kelas: {$data['targetKelas']->waliKelas->nama_lengkap})";
            }
        }

        $msg = "📊 *LAPORAN REKAP KEHADIRAN SISWA*\n";
        $msg .= "🏫 *{$namaSekolah}*\n";
        $msg .= "📅 Tanggal: *{$tglFormatted}*\n";
        $msg .= "🎯 Sasaran: *{$namaTarget}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

        $s = $data['stats'];
        $msg .= "📈 *RINGKASAN STATISTIK:*\n";
        $msg .= "• Total Siswa: *{$s['total']}*\n";
        $msg .= "• Hadir Tepat Waktu: *{$s['hadir']}*\n";
        $msg .= "• Terlambat: *{$s['terlambat']}*\n";
        $msg .= "• Sakit (S): *{$s['sakit']}*\n";
        $msg .= "• Izin (I): *{$s['izin']}*\n";
        $msg .= "• Tanpa Keterangan (A): *{$s['alpa']}*\n\n";

        if (!empty($data['sakitList'])) {
            $msg .= "🏥 *DAFTAR SISWA SAKIT (" . count($data['sakitList']) . "):*\n";
            foreach ($data['sakitList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item['siswa']->kelas ? " ({$item['siswa']->kelas->tingkat} {$item['siswa']->kelas->nama_rombel})" : "";
                $ket = !empty($item['keterangan']) ? " - _{$item['keterangan']}_" : "";
                $msg .= "{$no}. {$item['siswa']->nama_lengkap}{$kls}{$ket}\n";
            }
            $msg .= "\n";
        }

        if (!empty($data['izinList'])) {
            $msg .= "📝 *DAFTAR SISWA IZIN (" . count($data['izinList']) . "):*\n";
            foreach ($data['izinList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item['siswa']->kelas ? " ({$item['siswa']->kelas->tingkat} {$item['siswa']->kelas->nama_rombel})" : "";
                $ket = !empty($item['keterangan']) ? " - _{$item['keterangan']}_" : "";
                $msg .= "{$no}. {$item['siswa']->nama_lengkap}{$kls}{$ket}\n";
            }
            $msg .= "\n";
        }

        if (!empty($data['alpaList'])) {
            $msg .= "⚠️ *DAFTAR SISWA ALPA / TANPA KETERANGAN (" . count($data['alpaList']) . "):*\n";
            foreach ($data['alpaList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item->kelas ? " ({$item->kelas->tingkat} {$item->kelas->nama_rombel})" : "";
                $msg .= "{$no}. {$item->nama_lengkap}{$kls}\n";
            }
            $msg .= "\n";
        }

        if (!empty(trim($customNote))) {
            $msg .= "📌 *CATATAN TAMBAHAN:*\n";
            $msg .= trim($customNote) . "\n\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "_Pesan otomatis dikirim melalui Sistem Presensi {$namaSekolah}_";

        return $msg;
    }

    /**
     * Mempersiapkan monitoring kedisiplinan guru 30 hari terakhir
     */
    public function getMonitoringGurus(Collection $gurus, int $batasHari): Collection
    {
        $startDate = Carbon::today()->subDays(30);

        $hariLiburs30 = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), Carbon::today()->toDateString()])
            ->pluck('tanggal')
            ->toArray();

        $effectiveDates30 = [];
        $tempDay = $startDate->copy();
        while ($tempDay->lte(Carbon::today())) {
            if (!in_array($tempDay->toDateString(), $hariLiburs30)) {
                $effectiveDates30[] = $tempDay->toDateString();
            }
            $tempDay->addDay();
        }

        $guruPresensis = Presensi::where('user_type', 'guru')
            ->where('tanggal', '>=', $startDate->toDateString())
            ->get()
            ->groupBy('user_id');

        $latestPeringatan = PeringatanGuru::latest()
            ->get()
            ->groupBy('guru_id')
            ->map(fn($items) => $items->first());

        return $gurus->map(function ($g) use ($guruPresensis, $effectiveDates30, $latestPeringatan, $batasHari) {
            $userPres = $guruPresensis->get($g->id, collect());
            $recordedDates = $userPres->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();

            $alpa = 0;
            foreach ($effectiveDates30 as $effDate) {
                if (!in_array($effDate, $recordedDates)) {
                    $alpa++;
                }
            }
            $alpa += $userPres->where('status', 'Alpa')->count();

            $peringatan = $latestPeringatan->get($g->id);

            return (object) [
                'id' => $g->id,
                'nama_lengkap' => $g->nama_lengkap,
                'nip' => $g->nip,
                'no_hp' => $g->no_hp,
                'total_alpa' => $alpa,
                'batas_hari' => $batasHari,
                'is_over_limit' => ($alpa >= $batasHari),
                'terakhir_kirim' => $peringatan ? Carbon::parse($peringatan->tanggal_kirim)->translatedFormat('d M Y') : null,
                'status_kirim' => $peringatan ? $peringatan->status_kirim : null,
            ];
        })->sortByDesc('total_alpa')->values();
    }
}
