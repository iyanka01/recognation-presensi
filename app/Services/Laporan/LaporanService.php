<?php

namespace App\Services\Laporan;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\HariLibur;
use App\Models\PengaturanLembaga;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class LaporanService
{
    /**
     * Resolves start & end dates and label based on filters.
     */
    public function resolveDateRange(string $mode, ?string $bulan, ?string $dari, ?string $sampai): array
    {
        if ($mode === 'bulan') {
            $startDate = Carbon::parse(($bulan ?? Carbon::now()->format('Y-m')) . '-01')->startOfDay();
            $endDate = Carbon::parse(($bulan ?? Carbon::now()->format('Y-m')) . '-01')->endOfMonth()->endOfDay();
            $periodeStr = Carbon::parse(($bulan ?? Carbon::now()->format('Y-m')) . '-01')->locale('id')->isoFormat('MMMM YYYY');
            $filePeriod = $bulan ?? Carbon::now()->format('Y-m');
        } else {
            $startDate = Carbon::parse($dari ?? Carbon::now()->startOfMonth()->toDateString())->startOfDay();
            $endDate = Carbon::parse($sampai ?? Carbon::now()->toDateString())->endOfDay();
            $periodeStr = Carbon::parse($dari ?? Carbon::now()->startOfMonth()->toDateString())->locale('id')->isoFormat('D MMMM YYYY')
                . ' s/d '
                . Carbon::parse($sampai ?? Carbon::now()->toDateString())->locale('id')->isoFormat('D MMMM YYYY');
            $filePeriod = ($dari ?? Carbon::now()->startOfMonth()->toDateString()) . '_sd_' . ($sampai ?? Carbon::now()->toDateString());
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'label' => $periodeStr,
            'file_period' => $filePeriod,
        ];
    }

    /**
     * Helper menghasilkan seluruh tanggal dalam rentang periode (termasuk hari libur).
     */
    public function getAllDatesInRange(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();
        $today = Carbon::today()->endOfDay();

        $dates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $tglStr = $current->toDateString();
            $dates[] = [
                'date' => $tglStr,
                'day_name' => $current->locale('id')->isoFormat('ddd'),
                'day_number' => $current->format('d'),
                'day_month' => $current->locale('id')->isoFormat('D MMM'),
                'is_sunday' => $current->isSunday(),
                'is_today' => $current->isToday(),
                'is_past' => $current->lte($today),
            ];
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Helper menghitung daftar tanggal hari kerja efektif yang sudah berlalu dalam rentang tanggal.
     */
    public function getHariEfektifDatesByRange(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $today = Carbon::today()->endOfDay();
        if ($end->isAfter($today)) {
            $end = $today;
        }

        if ($start->isAfter($end)) {
            return [];
        }

        $hariLiburs = HariLibur::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->pluck('tanggal')
            ->toArray();

        $effectiveDates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $tglStr = $current->toDateString();
            if (!in_array($tglStr, $hariLiburs)) {
                $effectiveDates[] = $tglStr;
            }
            $current->addDay();
        }

        return $effectiveDates;
    }

    /**
     * Query data users (siswa/guru/staff) sesuai filter.
     */
    public function getUsers(string $tipe, ?string $departemen_id, ?string $kelas_id, ?string $jabatan_filter, $allJabatans)
    {
        if ($tipe === 'siswa') {
            $siswaQuery = Siswa::with('kelas.departemen')->orderBy('nama_lengkap');

            if ($departemen_id && $departemen_id !== 'all') {
                $siswaQuery->whereHas('kelas', function ($q) use ($departemen_id) {
                    $q->where('departemen_id', $departemen_id);
                });
            }

            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }

            return $siswaQuery->get();
        }

        $guruQuery = Guru::orderBy('nama_lengkap');
        $guruJabatans = $allJabatans->where('kategori', 'guru')->pluck('nama_jabatan')->toArray();
        $staffJabatans = $allJabatans->where('kategori', 'staff')->pluck('nama_jabatan')->toArray();

        if ($tipe === 'guru') {
            if ($jabatan_filter) {
                $guruQuery->where('jabatan', $jabatan_filter);
            } else {
                $guruQuery->where(function ($q) use ($guruJabatans, $staffJabatans) {
                    $q->whereIn('jabatan', $guruJabatans)
                        ->orWhere(function ($sub) use ($staffJabatans) {
                            $sub->whereNotIn('jabatan', $staffJabatans)->where('jabatan', 'LIKE', '%guru%');
                        })
                        ->orWhereNull('jabatan');
                });
            }
        } elseif ($tipe === 'staff') {
            if ($jabatan_filter) {
                $guruQuery->where('jabatan', $jabatan_filter);
            } else {
                $guruQuery->where(function ($q) use ($staffJabatans, $guruJabatans) {
                    $q->whereIn('jabatan', $staffJabatans)
                        ->orWhere(function ($sub) use ($guruJabatans) {
                            $sub->whereNotIn('jabatan', $guruJabatans)->where('jabatan', 'NOT LIKE', '%guru%');
                        });
                });
            }
        }

        return $guruQuery->get();
    }

    /**
     * Mempersiapkan data rekap presensi untuk index & print.
     */
    public function getRekapLaporan(array $filters): array
    {
        $mode = $filters['mode'] ?? 'rentang';
        $bulan = $filters['bulan'] ?? Carbon::now()->format('Y-m');
        $dari = $filters['dari'] ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $filters['sampai'] ?? Carbon::now()->toDateString();
        $tipe = $filters['tipe'] ?? 'siswa';
        $kelas_id = $filters['kelas_id'] ?? '';
        $departemen_id = $filters['departemen_id'] ?? '';
        $jabatan_filter = $filters['jabatan'] ?? '';

        $departemens = Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();
        $allJabatans = Jabatan::orderBy('urutan', 'asc')->orderBy('nama_jabatan', 'asc')->get();

        $dateRange = $this->resolveDateRange($mode, $bulan, $dari, $sampai);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $presensiUserType = $tipe === 'siswa' ? 'siswa' : 'guru';

        $query = Presensi::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_type', $presensiUserType);

        $users = $this->getUsers($tipe, $departemen_id, $kelas_id, $jabatan_filter, $allJabatans);

        $periodDates = $this->getAllDatesInRange($startDate, $endDate);
        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        $hariLiburList = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('keterangan', 'tanggal')
            ->toArray();

        $allPresensi = $query->get()->groupBy('user_id');
        $laporan = [];

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            $presensiByDate = $userPresensi->keyBy(function ($item) {
                return is_string($item->tanggal) ? substr($item->tanggal, 0, 10) : Carbon::parse($item->tanggal)->toDateString();
            });

            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();

            if ($tipe === 'guru' || $tipe === 'staff') {
                $recordedDates = $userPresensi->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();
                $alpa = 0;
                foreach ($effectiveDates as $effDate) {
                    if (!in_array($effDate, $recordedDates)) {
                        $alpa++;
                    }
                }
                $alpa += $userPresensi->where('status', 'Alpa')->count();
            } else {
                $alpa = $userPresensi->where('status', 'Alpa')->count();
            }

            $persen = round(($hadir / $denominator) * 100, 1);

            $dailyAttendance = [];
            foreach ($periodDates as $d) {
                $p = $presensiByDate->get($d['date']);
                $isLibur = isset($hariLiburList[$d['date']]) || $d['is_sunday'];

                $dailyAttendance[$d['date']] = [
                    'presensi' => $p,
                    'status' => $p ? $p->status : ($isLibur ? 'Libur' : ($d['is_past'] && ($tipe === 'guru' || $tipe === 'staff') ? 'Alpa' : null)),
                    'jam_masuk' => $p && $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : null,
                    'jam_pulang' => $p && $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : null,
                    'is_libur' => $isLibur,
                    'libur_keterangan' => $hariLiburList[$d['date']] ?? ($d['is_sunday'] ? 'Minggu' : null),
                ];
            }

            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen,
                'daily' => $dailyAttendance,
            ];
        }

        $kelasQuery = Kelas::orderBy('nama_rombel');
        if ($departemen_id && $departemen_id !== 'all') {
            $kelasQuery->where('departemen_id', $departemen_id);
        }
        $kelasList = $kelasQuery->get();

        return [
            'laporan' => $laporan,
            'kelasList' => $kelasList,
            'bulan' => $bulan,
            'dari' => $dari,
            'sampai' => $sampai,
            'mode' => $mode,
            'tipe' => $tipe,
            'kelas_id' => $kelas_id,
            'departemens' => $departemens,
            'departemen_id' => $departemen_id,
            'totalHariEfektif' => $totalHariEfektif,
            'periodDates' => $periodDates,
            'allJabatans' => $allJabatans,
            'jabatan_filter' => $jabatan_filter,
            'periodeStr' => $dateRange['label'],
        ];
    }

    /**
     * Mempersiapkan data laporan print/cetak.
     */
    public function getPrintLaporan(array $filters): array
    {
        $data = $this->getRekapLaporan($filters);

        $kelasStr = 'Semua Kelas';
        $activeDepartemen = null;

        if ($data['departemen_id'] && $data['departemen_id'] !== 'all') {
            $activeDepartemen = Departemen::with('kepalaSekolah')->find($data['departemen_id']);
        }

        if ($data['tipe'] == 'siswa' && $data['kelas_id']) {
            $k = Kelas::with('departemen.kepalaSekolah')->find($data['kelas_id']);
            $kelasStr = $k ? $k->nama_rombel : 'Semua Kelas';
            if (!$activeDepartemen && $k && $k->departemen) {
                $activeDepartemen = $k->departemen;
            }
        }

        $judul = "Laporan Presensi " . ucfirst($data['tipe']) . " - " . $data['periodeStr'];
        $pengaturan = PengaturanLembaga::first();

        return array_merge($data, [
            'kelasStr' => $kelasStr,
            'activeDepartemen' => $activeDepartemen,
            'judul' => $judul,
            'pengaturan' => $pengaturan,
        ]);
    }

    /**
     * Detail print per siswa / guru tunggal.
     */
    public function getPrintSiswaDetail(array $filters): array
    {
        $id = $filters['id'] ?? null;
        $tipe = $filters['tipe'] ?? 'siswa';
        $mode = $filters['mode'] ?? 'rentang';
        $bulan = $filters['bulan'] ?? Carbon::now()->format('Y-m');
        $dari = $filters['dari'] ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $filters['sampai'] ?? Carbon::now()->toDateString();

        if ($tipe == 'siswa') {
            $user = Siswa::with('kelas')->findOrFail($id);
        } else {
            $user = Guru::findOrFail($id);
        }

        $dateRange = $this->resolveDateRange($mode, $bulan, $dari, $sampai);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $periodeStr = $dateRange['label'];

        $presensi = Presensi::where('user_type', $tipe)
            ->where('user_id', $id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('tanggal', 'asc')
            ->get();

        $judul = "Laporan Presensi Detail";
        $bulanStr = $periodeStr;

        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        $hadir = $presensi->where('status', 'Hadir')->count();
        $terlambat = $presensi->where('status', 'Terlambat')->count();
        $izin = $presensi->where('status', 'Izin')->count();
        $sakit = $presensi->where('status', 'Sakit')->count();

        if ($tipe === 'guru') {
            $recordedDates = $presensi->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();
            $alpa = 0;
            foreach ($effectiveDates as $effDate) {
                if (!in_array($effDate, $recordedDates)) {
                    $alpa++;
                }
            }
            $alpa += $presensi->where('status', 'Alpa')->count();
        } else {
            $alpa = $presensi->where('status', 'Alpa')->count();
        }

        $persen = round(($hadir / $denominator) * 100, 1);

        $rekap = [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'persen' => $persen
        ];

        $pengaturan = PengaturanLembaga::first();
        $activeDepartemen = null;

        if ($tipe === 'siswa') {
            $user->loadMissing('kelas.departemen.kepalaSekolah');
            $activeDepartemen = $user->kelas?->departemen;
        } else {
            $activeDepartemen = Departemen::with('kepalaSekolah')->first();
        }

        return compact('user', 'tipe', 'bulanStr', 'presensi', 'rekap', 'judul', 'pengaturan', 'activeDepartemen');
    }

    /**
     * Menghasilkan Spreadsheet Excel untuk ekspor laporan.
     */
    public function exportExcel(array $filters): array
    {
        $mode = $filters['mode'] ?? 'rentang';
        $bulan = $filters['bulan'] ?? Carbon::now()->format('Y-m');
        $dari = $filters['dari'] ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $filters['sampai'] ?? Carbon::now()->toDateString();
        $tipe = $filters['tipe'] ?? 'siswa';
        $kelas_id = $filters['kelas_id'] ?? '';
        $departemen_id = $filters['departemen_id'] ?? '';
        $jabatan_filter = $filters['jabatan'] ?? '';

        $dateRange = $this->resolveDateRange($mode, $bulan, $dari, $sampai);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $periodeStr = $dateRange['label'];
        $filePeriod = $dateRange['file_period'];

        $presensiUserType = $tipe === 'siswa' ? 'siswa' : 'guru';

        $query = Presensi::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_type', $presensiUserType);

        $allJabatans = Jabatan::orderBy('urutan', 'asc')->orderBy('nama_jabatan', 'asc')->get();
        $users = $this->getUsers($tipe, $departemen_id, $kelas_id, $jabatan_filter, $allJabatans);
        $allPresensi = $query->get()->groupBy('user_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Rekap " . ucfirst($tipe), 0, 31));

        $judulLaporan = "REKAPITULASI PRESENSI " . strtoupper($tipe);
        $periodeText = "Periode: " . $periodeStr;

        $sheet->setCellValue('A1', $judulLaporan);
        $sheet->setCellValue('A2', $periodeText);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);

        $headers = ['No', 'Nama Lengkap', 'Identitas (NIS/NIP)'];
        if ($tipe == 'siswa') {
            $headers[] = 'Kelas / Rombel';
        }
        $headers = array_merge($headers, ['Jam Masuk', 'Jam Pulang', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', '% Kehadiran']);

        $colChar = 'A';
        $headerRow = 4;
        foreach ($headers as $h) {
            $sheet->setCellValue($colChar . $headerRow, $h);
            $colChar++;
        }
        $lastCol = chr(ord($colChar) - 1);

        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        $currRow = 5;
        $no = 1;
        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());

            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();

            if ($tipe === 'guru') {
                $recordedDates = $userPresensi->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();
                $alpa = 0;
                foreach ($effectiveDates as $effDate) {
                    if (!in_array($effDate, $recordedDates)) {
                        $alpa++;
                    }
                }
                $alpa += $userPresensi->where('status', 'Alpa')->count();
            } else {
                $alpa = $userPresensi->where('status', 'Alpa')->count();
            }

            $persen = round(($hadir / $denominator) * 100, 1) . '%';

            $latestHadir = $userPresensi->whereNotNull('jam_masuk')->sortByDesc('tanggal')->first();
            $jamMasuk = $latestHadir ? substr($latestHadir->jam_masuk, 0, 5) : '-';

            $latestPulang = $userPresensi->whereNotNull('jam_pulang')->sortByDesc('tanggal')->first();
            $jamPulang = $latestPulang ? substr($latestPulang->jam_pulang, 0, 5) : '-';

            $c = 'A';
            $sheet->setCellValue($c++ . $currRow, $no++);
            $sheet->setCellValue($c++ . $currRow, $user->nama_lengkap);

            $idVal = $tipe == 'siswa' ? ($user->nis ?? '-') : ($user->nip ?? '-');
            $sheet->getCell($c . $currRow)->setValueExplicit($idVal, DataType::TYPE_STRING);
            $c++;

            if ($tipe == 'siswa') {
                $sheet->setCellValue($c++ . $currRow, $user->kelas->nama_rombel ?? '-');
            }

            $sheet->setCellValue($c++ . $currRow, $jamMasuk);
            $sheet->setCellValue($c++ . $currRow, $jamPulang);
            $sheet->setCellValue($c++ . $currRow, $hadir ?: 0);
            $sheet->setCellValue($c++ . $currRow, $terlambat ?: 0);
            $sheet->setCellValue($c++ . $currRow, $izin ?: 0);
            $sheet->setCellValue($c++ . $currRow, $sakit ?: 0);
            $sheet->setCellValue($c++ . $currRow, $alpa ?: 0);
            $sheet->setCellValue($c++ . $currRow, $persen);

            if ($currRow % 2 == 0) {
                $sheet->getStyle("A{$currRow}:{$lastCol}{$currRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $numStartCol = ($tipe == 'siswa') ? 'E' : 'D';
            $sheet->getStyle("{$numStartCol}{$currRow}:{$lastCol}{$currRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currRow++;
        }

        $dataEndRow = $currRow - 1;

        if ($dataEndRow >= 4) {
            $sheet->getStyle("A4:{$lastCol}{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "rekap_presensi_{$tipe}_{$filePeriod}.xlsx";

        return [
            'spreadsheet' => $spreadsheet,
            'fileName' => $fileName,
        ];
    }
}
