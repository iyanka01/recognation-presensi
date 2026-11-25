<?php

namespace App\Services\Laporan;

use App\Models\Guru;
use App\Models\KegiatanKhusus;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PresensiKegiatanKhusus;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class LaporanKegiatanService
{
    /**
     * Helper untuk format teks sasaran peserta yang informatif
     */
    public function getSasaranText(?KegiatanKhusus $kegiatan): string
    {
        if (!$kegiatan) return '-';

        if ($kegiatan->sasaran_peserta === 'Pilih Kelas') {
            $kelasIds = $kegiatan->kelas_ids ?? [];
            if (!empty($kelasIds)) {
                $kelasList = Kelas::whereIn('id', $kelasIds)->get();
                $names = $kelasList->map(function ($k) {
                    $tingkat = Str::startsWith(strtolower($k->tingkat), 'kelas') ? $k->tingkat : 'Kelas ' . $k->tingkat;
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
     * Ambil target peserta sesuai sasaran peserta kegiatan
     */
    public function getTargetUsers(KegiatanKhusus $kegiatan): Collection
    {
        $targetUsers = collect();

        if (in_array($kegiatan->sasaran_peserta, ['Semua Siswa', 'Semua'])) {
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
        } elseif ($kegiatan->sasaran_peserta === 'Pilih Kelas') {
            $allowedClasses = $kegiatan->kelas_ids ?? [];
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

        if (in_array($kegiatan->sasaran_peserta, ['Khusus Guru', 'Semua'])) {
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

        return $targetUsers;
    }

    /**
     * Rekap data laporan kegiatan untuk halaman index
     */
    public function getIndexData(array $filters): array
    {
        Carbon::setLocale('id');

        $kegiatanList = KegiatanKhusus::orderByRaw("CASE WHEN tipe_kegiatan = 'rutin' THEN 0 ELSE 1 END")
            ->orderBy('tanggal', 'desc')
            ->get();
        $selectedKegiatanId = $filters['kegiatan_id'] ?? ($kegiatanList->first()->id ?? null);

        $selectedKegiatan = null;
        $sasaranText = '-';
        $pesertaList = collect();
        $tanggalFilter = $filters['tanggal'] ?? null;
        $daftarTanggal = collect();

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

                $daftarTanggal = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $selectedKegiatan->id)
                    ->whereNotNull('tanggal_presensi')
                    ->select('tanggal_presensi')
                    ->distinct()
                    ->orderBy('tanggal_presensi', 'desc')
                    ->pluck('tanggal_presensi');

                if ($selectedKegiatan->tipe_kegiatan === 'rutin' && !$tanggalFilter) {
                    $tanggalFilter = $daftarTanggal->first() ?? Carbon::today()->toDateString();
                }

                $presensiQuery = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $selectedKegiatan->id);
                if ($selectedKegiatan->tipe_kegiatan === 'rutin' && $tanggalFilter) {
                    $presensiQuery->where('tanggal_presensi', $tanggalFilter);
                }

                $allPresensi = $presensiQuery->get()
                    ->keyBy(function ($item) {
                        return $item->user_type . '_' . $item->user_id;
                    });

                $targetUsers = $this->getTargetUsers($selectedKegiatan);

                foreach ($targetUsers as $target) {
                    $key = $target['tipe'] . '_' . $target['id'];
                    $log = $allPresensi->get($key);

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

        return compact(
            'kegiatanList',
            'selectedKegiatan',
            'selectedKegiatanId',
            'sasaranText',
            'pesertaList',
            'rekap',
            'tanggalFilter',
            'daftarTanggal'
        );
    }

    /**
     * Mempersiapkan data untuk print cetak PDF / HTML
     */
    public function getPrintData(array $filters): array
    {
        Carbon::setLocale('id');

        $kegiatanId = $filters['kegiatan_id'] ?? null;
        $tanggalFilter = $filters['tanggal'] ?? null;
        $kegiatan = KegiatanKhusus::findOrFail($kegiatanId);
        $pengaturan = PengaturanLembaga::first();
        $sasaranText = $this->getSasaranText($kegiatan);

        $presensiQuery = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id);
        if ($kegiatan->tipe_kegiatan === 'rutin' && $tanggalFilter) {
            $presensiQuery->where('tanggal_presensi', $tanggalFilter);
        }

        $allPresensi = $presensiQuery->get()
            ->keyBy(function ($item) {
                return $item->user_type . '_' . $item->user_id;
            });

        $targetUsers = $this->getTargetUsers($kegiatan);
        $pesertaList = collect();
        $rekap = ['total' => 0, 'hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'belum_absen' => 0];

        foreach ($targetUsers as $target) {
            $key = $target['tipe'] . '_' . $target['id'];
            $log = $allPresensi->get($key);
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

        $tglPakai = ($kegiatan->tipe_kegiatan === 'rutin' && $tanggalFilter) ? $tanggalFilter : ($kegiatan->tanggal ?? now());
        $tanggalIndo = Carbon::parse($tglPakai)->locale('id')->translatedFormat('l, d F Y');

        return compact('kegiatan', 'pengaturan', 'sasaranText', 'pesertaList', 'rekap', 'tanggalIndo');
    }

    /**
     * Mempersiapkan data Excel Spreadsheet
     */
    public function exportExcel(array $filters): array
    {
        Carbon::setLocale('id');

        $kegiatanId = $filters['kegiatan_id'] ?? null;
        $tanggalFilter = $filters['tanggal'] ?? null;
        $kegiatan = KegiatanKhusus::findOrFail($kegiatanId);
        $sasaranText = $this->getSasaranText($kegiatan);

        $presensiQuery = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id);
        if ($kegiatan->tipe_kegiatan === 'rutin' && $tanggalFilter) {
            $presensiQuery->where('tanggal_presensi', $tanggalFilter);
        }

        $allPresensi = $presensiQuery->get()
            ->keyBy(function ($item) {
                return $item->user_type . '_' . $item->user_id;
            });

        $targetUsers = $this->getTargetUsers($kegiatan);

        $tglPakai = ($kegiatan->tipe_kegiatan === 'rutin' && $tanggalFilter) ? $tanggalFilter : ($kegiatan->tanggal ?? now());
        $tanggalIndo = Carbon::parse($tglPakai)->locale('id')->translatedFormat('l, d F Y');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Kegiatan');

        $sheet->setCellValue('A1', 'REKAPITULASI PRESENSI KEGIATAN KHUSUS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

        $headers = ['No', 'Nomor Induk (NIS/NIP)', 'Nama Lengkap', 'Tipe', 'Kelas / Jabatan', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran'];
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        $rowIdx = 7;
        foreach ($headers as $k => $h) {
            $sheet->setCellValue($colLetters[$k] . $rowIdx, $h);
        }

        $headerRange = 'A' . $rowIdx . ':H' . $rowIdx;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E40AF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($rowIdx)->setRowHeight(24);

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
            $sheet->setCellValueExplicit('B' . $currentRow, $target['nomor_induk'] ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $currentRow, $target['nama']);
            $sheet->setCellValue('D' . $currentRow, $target['tipe'] === 'guru' ? 'Guru' : 'Siswa');
            $sheet->setCellValue('E' . $currentRow, $target['info']);
            $sheet->setCellValue('F' . $currentRow, $jamMasuk);
            $sheet->setCellValue('G' . $currentRow, $jamPulang);
            $sheet->setCellValue('H' . $currentRow, $status);

            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $currentRow++;
        }

        $lastRow = max($currentRow - 1, $dataStartRow);
        $tableRange = 'A' . $rowIdx . ':H' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFCBD5E1'));

        foreach ($colLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kegiatan->nama_kegiatan);
        $fileName = "Laporan_{$kegiatan->kode_kegiatan}_{$cleanTitle}.xlsx";

        return [
            'spreadsheet' => $spreadsheet,
            'fileName' => $fileName,
        ];
    }
}
