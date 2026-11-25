<?php

namespace App\Http\Controllers;

use App\Services\Laporan\LaporanKegiatanService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanKegiatanController extends Controller
{
    protected LaporanKegiatanService $laporanKegiatanService;

    public function __construct(LaporanKegiatanService $laporanKegiatanService)
    {
        $this->laporanKegiatanService = $laporanKegiatanService;
    }

    /**
     * Halaman Rekap Laporan Kegiatan Khusus
     */
    public function index(Request $request)
    {
        $data = $this->laporanKegiatanService->getIndexData($request->all());
        return view('laporan-kegiatan.index', $data);
    }

    /**
     * Cetak Laporan Kegiatan Khusus (Format Standar Sekolah / Siap Print to PDF)
     */
    public function print(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatan_khusus,id',
        ]);

        $data = $this->laporanKegiatanService->getPrintData($request->all());
        return view('laporan-kegiatan.print', $data);
    }

    /**
     * Unduh Laporan Microsoft Excel (.xlsx) Rapi Menggunakan PhpSpreadsheet
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatan_khusus,id',
        ]);

        $export = $this->laporanKegiatanService->exportExcel($request->all());
        $spreadsheet = $export['spreadsheet'];
        $fileName = $export['fileName'];

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
