<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\HariLibur;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->mode ?? 'rentang'; // 'rentang' atau 'bulan'
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $dari = $request->dari ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->toDateString();
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';
        $departemen_id = $request->departemen_id ?? '';

        $departemens = \App\Models\Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();

        // Tentukan batas tanggal berdasarkan mode
        if ($mode === 'bulan') {
            $startDate = Carbon::parse($bulan . '-01')->startOfDay();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->endOfDay();
        } else {
            $startDate = Carbon::parse($dari)->startOfDay();
            $endDate = Carbon::parse($sampai)->endOfDay();
        }

        $query = Presensi::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_type', $tipe);

        $laporan = [];

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas.departemen')->orderBy('nama_lengkap');
            
            if ($departemen_id && $departemen_id !== 'all') {
                $siswaQuery->whereHas('kelas', function($q) use ($departemen_id) {
                    $q->where('departemen_id', $departemen_id);
                });
            }

            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        // Seluruh tanggal dalam rentang periode (untuk kolom tanggal matriks)
        $periodDates = $this->getAllDatesInRange($startDate, $endDate);

        // Daftar tanggal hari kerja efektif dalam periode terpilih (untuk pembagi % kehadiran)
        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        // Daftar hari libur dalam periode untuk styling kolom
        $hariLiburList = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('keterangan', 'tanggal')
            ->toArray();

        // Fetch all presensi for these users in this period to avoid N+1 per user
        $allPresensi = $query->get()->groupBy('user_id');

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            $presensiByDate = $userPresensi->keyBy(function($item) {
                return is_string($item->tanggal) ? substr($item->tanggal, 0, 10) : Carbon::parse($item->tanggal)->toDateString();
            });
            
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
            
            $persen = round(($hadir / $denominator) * 100, 1);

            // Log jam per tanggal untuk matriks kalender
            $dailyAttendance = [];
            foreach ($periodDates as $d) {
                $p = $presensiByDate->get($d['date']);
                $isLibur = isset($hariLiburList[$d['date']]) || $d['is_sunday'];
                
                $dailyAttendance[$d['date']] = [
                    'presensi' => $p,
                    'status' => $p ? $p->status : ($isLibur ? 'Libur' : ($d['is_past'] && $tipe === 'guru' ? 'Alpa' : null)),
                    'jam_masuk' => $p && $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : null,
                    'jam_pulang' => $p && $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : null,
                    'is_libur' => $isLibur,
                    'libur_keterangan' => $hariLiburList[$d['date']] ?? ($d['is_sunday'] ? 'Minggu' : null),
                ];
            }
            
            $rekap = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen,
                'daily' => $dailyAttendance,
            ];
            
            $laporan[] = $rekap;
        }

        $kelasQuery = Kelas::orderBy('nama_rombel');
        if ($departemen_id && $departemen_id !== 'all') {
            $kelasQuery->where('departemen_id', $departemen_id);
        }
        $kelasList = $kelasQuery->get();

        return view('laporan.index', compact(
            'laporan', 'kelasList', 'bulan', 'dari', 'sampai', 'mode', 'tipe', 'kelas_id', 'departemens', 'departemen_id', 'totalHariEfektif', 'periodDates'
        ));
    }

    public function print(Request $request)
    {
        $mode = $request->mode ?? 'rentang';
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $dari = $request->dari ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->toDateString();
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';
        $departemen_id = $request->departemen_id ?? '';

        if ($mode === 'bulan') {
            $startDate = Carbon::parse($bulan . '-01')->startOfDay();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->endOfDay();
            $periodeStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        } else {
            $startDate = Carbon::parse($dari)->startOfDay();
            $endDate = Carbon::parse($sampai)->endOfDay();
            $periodeStr = Carbon::parse($dari)->locale('id')->isoFormat('D MMMM YYYY') . ' s/d ' . Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM YYYY');
        }

        if ($mode === 'bulan') {
            $startDate = Carbon::parse($bulan . '-01')->startOfDay();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->endOfDay();
            $periodeStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        } else {
            $startDate = Carbon::parse($dari)->startOfDay();
            $endDate = Carbon::parse($sampai)->endOfDay();
            $periodeStr = Carbon::parse($dari)->locale('id')->isoFormat('D MMMM YYYY') . ' s/d ' . Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM YYYY');
        }

        $query = Presensi::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_type', $tipe);

        $departemen_id = $request->departemen_id ?? '';

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas.departemen')->orderBy('nama_lengkap');

            if ($departemen_id && $departemen_id !== 'all') {
                $siswaQuery->whereHas('kelas', function($q) use ($departemen_id) {
                    $q->where('departemen_id', $departemen_id);
                });
            }

            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        $allPresensi = $query->get()->groupBy('user_id');
        $laporan = [];
        
        $periodDates = $this->getAllDatesInRange($startDate, $endDate);

        // Daftar tanggal hari kerja efektif yang sudah berlalu
        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        $hariLiburList = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('keterangan', 'tanggal')
            ->toArray();

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            $presensiByDate = $userPresensi->keyBy(function($item) {
                return is_string($item->tanggal) ? substr($item->tanggal, 0, 10) : Carbon::parse($item->tanggal)->toDateString();
            });
            
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
            
            $persen = round(($hadir / $denominator) * 100, 1);

            $dailyAttendance = [];
            foreach ($periodDates as $d) {
                $p = $presensiByDate->get($d['date']);
                $isLibur = isset($hariLiburList[$d['date']]) || $d['is_sunday'];
                
                $dailyAttendance[$d['date']] = [
                    'presensi' => $p,
                    'status' => $p ? $p->status : ($isLibur ? 'Libur' : ($d['is_past'] && $tipe === 'guru' ? 'Alpa' : null)),
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

        $kelasStr = 'Semua Kelas';
        $activeDepartemen = null;

        if ($departemen_id && $departemen_id !== 'all') {
            $activeDepartemen = \App\Models\Departemen::with('kepalaSekolah')->find($departemen_id);
        }

        if ($tipe == 'siswa' && $kelas_id) {
            $k = Kelas::with('departemen.kepalaSekolah')->find($kelas_id);
            $kelasStr = $k ? $k->nama_rombel : 'Semua Kelas';
            if (!$activeDepartemen && $k && $k->departemen) {
                $activeDepartemen = $k->departemen;
            }
        }

        $judul = "Laporan Presensi " . ucfirst($tipe) . " - " . $periodeStr;
        $pengaturan = \App\Models\PengaturanLembaga::first();

        return view('laporan.print', compact('laporan', 'judul', 'tipe', 'kelasStr', 'pengaturan', 'activeDepartemen', 'periodeStr', 'periodDates'));
    }

    public function printSiswa(Request $request)
    {
        $id = $request->id;
        $tipe = $request->tipe ?? 'siswa';
        $mode = $request->mode ?? 'rentang';
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $dari = $request->dari ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->toDateString();

        if ($tipe == 'siswa') {
            $user = Siswa::with('kelas')->findOrFail($id);
        } else {
            $user = Guru::findOrFail($id);
        }

        if ($mode === 'bulan') {
            $startDate = Carbon::parse($bulan . '-01')->startOfDay();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->endOfDay();
            $periodeStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        } else {
            $startDate = Carbon::parse($dari)->startOfDay();
            $endDate = Carbon::parse($sampai)->endOfDay();
            $periodeStr = Carbon::parse($dari)->locale('id')->isoFormat('D MMMM YYYY') . ' s/d ' . Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM YYYY');
        }

        $presensi = Presensi::where('user_type', $tipe)
            ->where('user_id', $id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('tanggal', 'asc')
            ->get();

        $judul = "Laporan Presensi Detail";
        $bulanStr = $periodeStr;

        // Hitung Hari Efektif
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

        $pengaturan = \App\Models\PengaturanLembaga::first();
        $activeDepartemen = null;

        if ($tipe === 'siswa') {
            $user->loadMissing('kelas.departemen.kepalaSekolah');
            $activeDepartemen = $user->kelas?->departemen;
        } else {
            $activeDepartemen = \App\Models\Departemen::with('kepalaSekolah')->first();
        }

        return view('laporan.print_siswa', compact('user', 'tipe', 'bulanStr', 'presensi', 'rekap', 'judul', 'pengaturan', 'activeDepartemen'));
    }

    public function export(Request $request)
    {
        $mode = $request->mode ?? 'rentang';
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $dari = $request->dari ?? Carbon::now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->toDateString();
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';
        $departemen_id = $request->departemen_id ?? '';

        if ($mode === 'bulan') {
            $startDate = Carbon::parse($bulan . '-01')->startOfDay();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->endOfDay();
            $periodeStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
            $filePeriod = $bulan;
        } else {
            $startDate = Carbon::parse($dari)->startOfDay();
            $endDate = Carbon::parse($sampai)->endOfDay();
            $periodeStr = Carbon::parse($dari)->locale('id')->isoFormat('D MMMM YYYY') . ' s/d ' . Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM YYYY');
            $filePeriod = "{$dari}_sd_{$sampai}";
        }

        $query = Presensi::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_type', $tipe);

        if ($tipe == 'siswa') {
            $siswaQuery = Siswa::with('kelas.departemen')->orderBy('nama_lengkap');
            if ($departemen_id && $departemen_id !== 'all') {
                $siswaQuery->whereHas('kelas', function($q) use ($departemen_id) {
                    $q->where('departemen_id', $departemen_id);
                });
            }
            if ($kelas_id) {
                $siswaQuery->where('kelas_id', $kelas_id);
            }
            $users = $siswaQuery->get();
        } else {
            $users = Guru::orderBy('nama_lengkap')->get();
        }

        $allPresensi = $query->get()->groupBy('user_id');

        // Gunakan PhpSpreadsheet untuk menghasilkan file Excel murni (.xlsx)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Rekap " . ucfirst($tipe), 0, 31));

        // Judul Laporan
        $judulLaporan = "REKAPITULASI PRESENSI " . strtoupper($tipe);
        $periodeText = "Periode: " . $periodeStr;

        $sheet->setCellValue('A1', $judulLaporan);
        $sheet->setCellValue('A2', $periodeText);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);

        // Header Kolom
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

        // Styling Header Kolom
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Dark slate modern
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // Hitung denominator hari efektif
        $effectiveDates = $this->getHariEfektifDatesByRange($startDate, $endDate);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        // Isi Data Baris
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
            
            // Format NIP/NIS sebagai teks agar angka 0 di depan tidak hilang
            $idVal = $tipe == 'siswa' ? ($user->nis ?? '-') : ($user->nip ?? '-');
            $sheet->getCell($c . $currRow)->setValueExplicit($idVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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

            // Striping zebra row warna halus
            if ($currRow % 2 == 0) {
                $sheet->getStyle("A{$currRow}:{$lastCol}{$currRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            // Alignment angka tengah
            $numStartCol = ($tipe == 'siswa') ? 'E' : 'D';
            $sheet->getStyle("{$numStartCol}{$currRow}:{$lastCol}{$currRow}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currRow}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $currRow++;
        }

        $dataEndRow = $currRow - 1;

        // Border keliling semua sel data
        if ($dataEndRow >= 4) {
            $sheet->getStyle("A4:{$lastCol}{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        // Auto-fit lebar seluruh kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "rekap_presensi_{$tipe}_{$filePeriod}.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Helper menghitung daftar tanggal hari kerja efektif yang sudah berlalu dalam rentang tanggal.
     * Hari libur sepenuhnya ditentukan oleh menu Hari Libur yang disetel di aplikasi.
     */
    protected function getHariEfektifDatesByRange(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // Jika akhir rentang lebih dari hari ini, batasi sampai hari ini untuk hari yang sudah berlalu
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
     * Helper menghasilkan seluruh tanggal dalam rentang periode (termasuk hari libur)
     * Digunakan untuk kolom matriks presensi kalender
     */
    protected function getAllDatesInRange(Carbon $startDate, Carbon $endDate): array
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
                'day_name' => $current->locale('id')->isoFormat('ddd'), // Sen, Sel, Rab, ...
                'day_number' => $current->format('d'),                 // 01, 02, ...
                'day_month' => $current->locale('id')->isoFormat('D MMM'), // 20 Nov
                'is_sunday' => $current->isSunday(),
                'is_today' => $current->isToday(),
                'is_past' => $current->lte($today),
            ];
            $current->addDay();
        }

        return $dates;
    }
}
