<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KegiatanKhusus;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PresensiKegiatanKhusus;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanKegiatanController extends Controller
{
    /**
     * Helper untuk format teks sasaran peserta yang informatif
     */
    private function getSasaranText($kegiatan)
    {
        if (!$kegiatan) return '-';

        if ($kegiatan->sasaran_peserta === 'Pilih Kelas') {
            $kelasIds = $kegiatan->kelas_ids ?? [];
            if (!empty($kelasIds)) {
                $kelasList = Kelas::whereIn('id', $kelasIds)->get();
                $names = $kelasList->map(function ($k) {
                    $tingkat = \Illuminate\Support\Str::startsWith(strtolower($k->tingkat), 'kelas') ? $k->tingkat : 'Kelas ' . $k->tingkat;
                    return $tingkat . ' ' . $k->nama_rombel;
                })->toArray();
                return !empty($names) ? implode(', ', $names) : 'Kelas Terpilih';
            }
            return 'Pilih Kelas';
        }

        if ($kegiatan->sasaran_peserta === 'Semua Siswa') {
            return 'Semua Siswa';
        }

        if ($kegiatan->sasaran_peserta === 'Khusus Guru') {
            return 'Khusus Guru / Tendik';
        }

        return 'Semua (Siswa & Guru)';
    }

    /**
     * Halaman Rekap Laporan Kegiatan Khusus
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $kegiatanList = KegiatanKhusus::orderBy('tanggal', 'desc')->get();
        $selectedKegiatanId = $request->kegiatan_id ?? ($kegiatanList->first()->id ?? null);

        $selectedKegiatan = null;
        $sasaranText = '-';
        $pesertaList = collect();
        $rekap = [
            'total' => 0,
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'belum_absen' => 0,
            'persen' => 0,
        ];

        if ($selectedKegiatanId) {
            $selectedKegiatan = KegiatanKhusus::find($selectedKegiatanId);

            if ($selectedKegiatan) {
                $sasaranText = $this->getSasaranText($selectedKegiatan);

                // Ambil semua presensi kegiatan ini
                $allPresensi = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $selectedKegiatan->id)
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->user_type . '_' . $item->user_id;
                    });

                // Target peserta
                $targetUsers = collect();

                if (in_array($selectedKegiatan->sasaran_peserta, ['Semua Siswa', 'Semua'])) {
                    $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
                    foreach ($siswas as $s) {
                        $targetUsers->push([
                            'id' => $s->id,
                            'tipe' => 'siswa',
                            'nomor_induk' => $s->nis,
                            'nama' => $s->nama_lengkap,
                            'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-'),
                        ]);
                    }
                } elseif ($selectedKegiatan->sasaran_peserta === 'Pilih Kelas') {
                    $allowedClasses = $selectedKegiatan->kelas_ids ?? [];
                    $siswas = Siswa::with('kelas')->whereIn('kelas_id', $allowedClasses)->orderBy('nama_lengkap')->get();
                    foreach ($siswas as $s) {
                        $targetUsers->push([
                            'id' => $s->id,
                            'tipe' => 'siswa',
                            'nomor_induk' => $s->nis,
                            'nama' => $s->nama_lengkap,
                            'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-'),
                        ]);
                    }
                }

                if (in_array($selectedKegiatan->sasaran_peserta, ['Khusus Guru', 'Semua'])) {
                    $gurus = Guru::orderBy('nama_lengkap')->get();
                    foreach ($gurus as $g) {
                        $targetUsers->push([
                            'id' => $g->id,
                            'tipe' => 'guru',
                            'nomor_induk' => $g->nip,
                            'nama' => $g->nama_lengkap,
                            'info' => $g->jabatan ?? 'Guru / Tendik',
                        ]);
                    }
                }

                // Gabungkan target peserta dengan data presensi
                foreach ($targetUsers as $target) {
                    $key = $target['tipe'] . '_' . $target['id'];
                    $log = $allPresensi->get($key);

                    // Sesuai permintaan: status default "-" jika belum absen
                    $status = $log ? $log->status : '-';
                    $jamMasuk = $log && $log->jam_masuk ? $log->jam_masuk->format('H:i:s') : '-';
                    $jamPulang = $log && $log->jam_pulang ? $log->jam_pulang->format('H:i:s') : '-';

                    $pesertaList->push([
                        'nomor_induk' => $target['nomor_induk'],
                        'nama' => $target['nama'],
                        'tipe' => $target['tipe'] === 'guru' ? 'Guru' : 'Siswa',
                        'info' => $target['info'],
                        'status' => $status,
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'metode' => $log ? $log->metode : '-',
                    ]);

                    if ($status === 'Hadir') $rekap['hadir']++;
                    elseif ($status === 'Terlambat') $rekap['terlambat']++;
                    elseif ($status === 'Izin') $rekap['izin']++;
                    elseif ($status === 'Sakit') $rekap['sakit']++;
                    else $rekap['belum_absen']++;
                }

                $rekap['total'] = $pesertaList->count();
                $rekap['persen'] = $rekap['total'] > 0 
                    ? round((($rekap['hadir'] + $rekap['terlambat']) / $rekap['total']) * 100, 1) 
                    : 0;
            }
        }

        return view('laporan-kegiatan.index', compact('kegiatanList', 'selectedKegiatan', 'selectedKegiatanId', 'sasaranText', 'pesertaList', 'rekap'));
    }

    /**
     * Cetak Laporan Kegiatan Khusus (Format Standar Sekolah / Siap Print to PDF)
     */
    public function print(Request $request)
    {
        Carbon::setLocale('id');

        $kegiatanId = $request->kegiatan_id;
        $kegiatan = KegiatanKhusus::findOrFail($kegiatanId);
        $pengaturan = PengaturanLembaga::first();
        $sasaranText = $this->getSasaranText($kegiatan);

        // Data Peserta
        $allPresensi = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->user_type . '_' . $item->user_id;
            });

        $pesertaList = collect();
        $targetUsers = collect();

        if (in_array($kegiatan->sasaran_peserta, ['Semua Siswa', 'Semua'])) {
            $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
            foreach ($siswas as $s) {
                $targetUsers->push(['id' => $s->id, 'tipe' => 'siswa', 'nomor_induk' => $s->nis, 'nama' => $s->nama_lengkap, 'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-')]);
            }
        } elseif ($kegiatan->sasaran_peserta === 'Pilih Kelas') {
            $allowedClasses = $kegiatan->kelas_ids ?? [];
            $siswas = Siswa::with('kelas')->whereIn('kelas_id', $allowedClasses)->orderBy('nama_lengkap')->get();
            foreach ($siswas as $s) {
                $targetUsers->push(['id' => $s->id, 'tipe' => 'siswa', 'nomor_induk' => $s->nis, 'nama' => $s->nama_lengkap, 'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-')]);
            }
        }

        if (in_array($kegiatan->sasaran_peserta, ['Khusus Guru', 'Semua'])) {
            $gurus = Guru::orderBy('nama_lengkap')->get();
            foreach ($gurus as $g) {
                $targetUsers->push(['id' => $g->id, 'tipe' => 'guru', 'nomor_induk' => $g->nip, 'nama' => $g->nama_lengkap, 'info' => $g->jabatan ?? 'Guru / Tendik']);
            }
        }

        $rekap = ['total' => 0, 'hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'belum_absen' => 0];

        foreach ($targetUsers as $target) {
            $key = $target['tipe'] . '_' . $target['id'];
            $log = $allPresensi->get($key);
            // Default status "-" jika belum absen
            $status = $log ? $log->status : '-';

            $pesertaList->push([
                'nomor_induk' => $target['nomor_induk'],
                'nama' => $target['nama'],
                'tipe' => $target['tipe'] === 'guru' ? 'Guru' : 'Siswa',
                'info' => $target['info'],
                'status' => $status,
                'jam_masuk' => $log && $log->jam_masuk ? $log->jam_masuk->format('H:i:s') : '-',
                'jam_pulang' => $log && $log->jam_pulang ? $log->jam_pulang->format('H:i:s') : '-',
            ]);

            if ($status === 'Hadir') $rekap['hadir']++;
            elseif ($status === 'Terlambat') $rekap['terlambat']++;
            elseif ($status === 'Izin') $rekap['izin']++;
            elseif ($status === 'Sakit') $rekap['sakit']++;
            else $rekap['belum_absen']++;
        }
        $rekap['total'] = $pesertaList->count();

        // Format tanggal bahasa Indonesia (misal: "Rabu, 18 November 2026")
        $tanggalIndo = Carbon::parse($kegiatan->tanggal)->locale('id')->translatedFormat('l, d F Y');

        return view('laporan-kegiatan.print', compact('kegiatan', 'pengaturan', 'sasaranText', 'pesertaList', 'rekap', 'tanggalIndo'));
    }

    /**
     * Unduh Laporan Microsoft Excel (.xlsx) Rapi Menggunakan PhpSpreadsheet
     */
    public function exportExcel(Request $request)
    {
        Carbon::setLocale('id');

        $kegiatanId = $request->kegiatan_id;
        $kegiatan = KegiatanKhusus::findOrFail($kegiatanId);
        $sasaranText = $this->getSasaranText($kegiatan);

        $allPresensi = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->user_type . '_' . $item->user_id;
            });

        $targetUsers = collect();

        if (in_array($kegiatan->sasaran_peserta, ['Semua Siswa', 'Semua'])) {
            $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
            foreach ($siswas as $s) {
                $targetUsers->push(['id' => $s->id, 'tipe' => 'siswa', 'nomor_induk' => $s->nis, 'nama' => $s->nama_lengkap, 'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-')]);
            }
        } elseif ($kegiatan->sasaran_peserta === 'Pilih Kelas') {
            $allowedClasses = $kegiatan->kelas_ids ?? [];
            $siswas = Siswa::with('kelas')->whereIn('kelas_id', $allowedClasses)->orderBy('nama_lengkap')->get();
            foreach ($siswas as $s) {
                $targetUsers->push(['id' => $s->id, 'tipe' => 'siswa', 'nomor_induk' => $s->nis, 'nama' => $s->nama_lengkap, 'info' => 'Kelas ' . ($s->kelas->nama_rombel ?? '-')]);
            }
        }

        if (in_array($kegiatan->sasaran_peserta, ['Khusus Guru', 'Semua'])) {
            $gurus = Guru::orderBy('nama_lengkap')->get();
            foreach ($gurus as $g) {
                $targetUsers->push(['id' => $g->id, 'tipe' => 'guru', 'nomor_induk' => $g->nip, 'nama' => $g->nama_lengkap, 'info' => $g->jabatan ?? 'Guru']);
            }
        }

        // Tanggal Indonesia
        $tanggalIndo = Carbon::parse($kegiatan->tanggal)->locale('id')->translatedFormat('l, d F Y');

        // Buat Dokumen Excel Native
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Kegiatan');

        // Judul Laporan
        $sheet->setCellValue('A1', 'REKAPITULASI PRESENSI KEGIATAN KHUSUS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata Kegiatan
        $sheet->setCellValue('A3', 'Nama Kegiatan');
        $sheet->setCellValue('B3', ': ' . $kegiatan->nama_kegiatan);
        $sheet->setCellValue('E3', 'Tanggal Kegiatan');
        $sheet->setCellValue('F3', ': ' . $tanggalIndo);

        $sheet->setCellValue('A4', 'Kode Kegiatan');
        $sheet->setCellValue('B4', ': ' . $kegiatan->kode_kegiatan);
        $sheet->setCellValue('E4', 'Waktu');
        $sheet->setCellValue('F4', ': ' . $kegiatan->jam_range . ' WIB');

        $sheet->setCellValue('A5', 'Mode Presensi');
        $sheet->setCellValue('B5', ': ' . $kegiatan->mode_absen);
        $sheet->setCellValue('E5', 'Sasaran Peserta');
        $sheet->setCellValue('F5', ': ' . $sasaranText);

        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('E3:E5')->getFont()->setBold(true);

        // Header Tabel
        $headers = ['No', 'Nomor Induk (NIS/NIP)', 'Nama Lengkap', 'Tipe', 'Kelas / Jabatan', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran'];
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        $rowIdx = 7;
        foreach ($headers as $k => $h) {
            $sheet->setCellValue($colLetters[$k] . $rowIdx, $h);
        }

        // Style Header Tabel
        $headerRange = 'A' . $rowIdx . ':H' . $rowIdx;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E40AF'); // Navy Blue
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($rowIdx)->setRowHeight(24);

        // Data Rows
        $no = 1;
        $dataStartRow = 8;
        $currentRow = $dataStartRow;

        foreach ($targetUsers as $target) {
            $key = $target['tipe'] . '_' . $target['id'];
            $log = $allPresensi->get($key);
            $status = $log ? $log->status : '-';
            $jamMasuk = $log && $log->jam_masuk ? $log->jam_masuk->format('H:i:s') : '-';
            $jamPulang = $log && $log->jam_pulang ? $log->jam_pulang->format('H:i:s') : '-';

            $sheet->setCellValue('A' . $currentRow, $no++);
            $sheet->setCellValueExplicit('B' . $currentRow, $target['nomor_induk'] ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $currentRow, $target['nama']);
            $sheet->setCellValue('D' . $currentRow, $target['tipe'] === 'guru' ? 'Guru' : 'Siswa');
            $sheet->setCellValue('E' . $currentRow, $target['info']);
            $sheet->setCellValue('F' . $currentRow, $jamMasuk);
            $sheet->setCellValue('G' . $currentRow, $jamPulang);
            $sheet->setCellValue('H' . $currentRow, $status);

            // Alignment
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $currentRow++;
        }

        $lastRow = max($currentRow - 1, $dataStartRow);

        // Border Table
        $tableRange = 'A' . $rowIdx . ':H' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));

        // Auto Width Columns
        foreach ($colLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Clean filename
        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kegiatan->nama_kegiatan);
        $fileName = "Laporan_{$kegiatan->kode_kegiatan}_{$cleanTitle}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
