<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laporan\ReportFilterRequest;
use App\Services\Laporan\LaporanService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(ReportFilterRequest $request)
    {
        $data = $this->laporanService->getRekapLaporan($request->validated());
        return view('laporan.index', $data);
    }

    public function print(ReportFilterRequest $request)
    {
        $data = $this->laporanService->getPrintLaporan($request->validated());
        return view('laporan.print', $data);
    }

    public function printSiswa(ReportFilterRequest $request)
    {
        $data = $this->laporanService->getPrintSiswaDetail($request->validated());
        return view('laporan.print_siswa', $data);
    }

    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $exportData = $this->laporanService->exportExcel($request->validated());
        $writer = new Xlsx($exportData['spreadsheet']);
        $fileName = $exportData['fileName'];

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
