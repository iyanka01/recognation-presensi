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
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';
        $departemen_id = $request->departemen_id ?? '';

        $departemens = \App\Models\Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->where('user_type', $tipe);

        $laporan = [];
        $hariDalamBulan = Carbon::parse($bulan . '-01')->daysInMonth;

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

        // Daftar tanggal hari kerja efektif (Senin-Sabtu) yang sudah berlalu
        $effectiveDates = $this->getHariEfektifDates($bulan);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        // Fetch all presensi for these users in this month to avoid N+1 per user per day
        $allPresensi = $query->get()->groupBy('user_id');

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            
            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();

            if ($tipe === 'guru') {
                // Untuk Guru: Setiap hari kerja efektif yang tidak ada presensi (Hadir/Telat/Izin/Sakit) dihitung ALPA
                $recordedDates = $userPresensi->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();
                $alpa = 0;
                foreach ($effectiveDates as $effDate) {
                    if (!in_array($effDate, $recordedDates)) {
                        $alpa++;
                    }
                }
                // Tambahkan juga jika ada data berstatus 'Alpa' di database
                $alpa += $userPresensi->where('status', 'Alpa')->count();
            } else {
                $alpa = $userPresensi->where('status', 'Alpa')->count();
            }
            
            $persen = round(($hadir / $denominator) * 100, 1);
            
            $rekap = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen
            ];
            
            $laporan[] = $rekap;
        }

        $kelasQuery = Kelas::orderBy('nama_rombel');
        if ($departemen_id && $departemen_id !== 'all') {
            $kelasQuery->where('departemen_id', $departemen_id);
        }
        $kelasList = $kelasQuery->get();

        return view('laporan.index', compact('laporan', 'kelasList', 'bulan', 'tipe', 'kelas_id', 'departemens', 'departemen_id'));
    }

    public function print(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
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
        
        // Daftar tanggal hari kerja efektif (Senin-Sabtu) yang sudah berlalu
        $effectiveDates = $this->getHariEfektifDates($bulan);
        $totalHariEfektif = count($effectiveDates);
        $denominator = max($totalHariEfektif, 1);

        foreach ($users as $user) {
            $userPresensi = $allPresensi->get($user->id, collect());
            
            $hadir = $userPresensi->where('status', 'Hadir')->count();
            $terlambat = $userPresensi->where('status', 'Terlambat')->count();
            $izin = $userPresensi->where('status', 'Izin')->count();
            $sakit = $userPresensi->where('status', 'Sakit')->count();

            if ($tipe === 'guru') {
                // Untuk Guru: Setiap hari kerja efektif yang tidak ada presensi dihitung ALPA
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
            
            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'persen' => $persen
            ];
        }

        $kelasStr = 'Semua Kelas';
        $departemen_id = $request->departemen_id ?? '';
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

        $judul = "Laporan Presensi " . ucfirst($tipe) . " - " . Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        $pengaturan = \App\Models\PengaturanLembaga::first();

        return view('laporan.print', compact('laporan', 'judul', 'tipe', 'kelasStr', 'pengaturan', 'activeDepartemen'));
    }

    public function printSiswa(Request $request)
    {
        $id = $request->id;
        $tipe = $request->tipe ?? 'siswa';
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');

        if ($tipe == 'siswa') {
            $user = Siswa::with('kelas')->findOrFail($id);
        } else {
            $user = Guru::findOrFail($id);
        }

        $presensi = Presensi::where('user_type', $tipe)
            ->where('user_id', $id)
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->orderBy('tanggal', 'asc')
            ->get();

        $judul = "Laporan Presensi Detail";
        $bulanStr = Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');

        // Hitung Hari Efektif
        $effectiveDates = $this->getHariEfektifDates($bulan);
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

        return view('laporan.print_siswa', compact('user', 'tipe', 'bulanStr', 'presensi', 'rekap', 'judul', 'pengaturan'));
    }

    public function export(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $tipe = $request->tipe ?? 'siswa';
        $kelas_id = $request->kelas_id ?? '';

        $query = Presensi::whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
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

        $allPresensi = $query->get()->groupBy('user_id');

        // Gunakan PhpSpreadsheet untuk menghasilkan file Excel murni (.xlsx) yang rapi, berkolom rapi, dan tidak berantakan
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Rekap " . ucfirst($tipe), 0, 31));

        // Judul Laporan
        $judulLaporan = "REKAPITULASI PRESENSI " . strtoupper($tipe);
        $periodeStr = "Periode: " . \Carbon\Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');

        $sheet->setCellValue('A1', $judulLaporan);
        $sheet->setCellValue('A2', $periodeStr);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);

        // Header Kolom
        $headers = ['No', 'Nama Lengkap', 'Identitas (NIS/NIP)'];
        if ($tipe == 'siswa') {
            $headers[] = 'Kelas / Rombel';
        }
        $headers = array_merge($headers, ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', '% Kehadiran']);

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
        $effectiveDates = $this->getHariEfektifDates($bulan);
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

        $fileName = "rekap_presensi_{$tipe}_{$bulan}.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Helper menghitung daftar tanggal hari kerja efektif (Senin-Sabtu) yang sudah berlalu dalam satu bulan,
     * tidak termasuk hari Minggu dan hari libur nasional/sekolah.
     */
    protected function getHariEfektifDates(string $bulan): array
    {
        $year = (int) substr($bulan, 0, 4);
        $month = (int) substr($bulan, 5, 2);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $daysInMonth = $startOfMonth->daysInMonth;
        $endOfScope = Carbon::createFromDate($year, $month, $daysInMonth)->endOfDay();

        // Jika bulan yang dipilih adalah bulan berjalan, batasi sampai hari ini
        $today = Carbon::today()->endOfDay();
        if ($endOfScope->isAfter($today)) {
            $endOfScope = $today;
        }

        // Ambil daftar tanggal hari libur yang ada di database pada rentang ini
        $hariLiburs = HariLibur::whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfScope->toDateString()])
            ->pluck('tanggal')
            ->toArray();

        $effectiveDates = [];
        $current = $startOfMonth->copy();

        while ($current->lte($endOfScope)) {
            // Abaikan hari Minggu (0)
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                $tglStr = $current->toDateString();
                // Abaikan jika terdaftar sebagai hari libur
                if (!in_array($tglStr, $hariLiburs)) {
                    $effectiveDates[] = $tglStr;
                }
            }
            $current->addDay();
        }

        return $effectiveDates;
    }
}
