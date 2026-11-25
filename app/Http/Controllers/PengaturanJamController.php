<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengaturanJam\StorePengaturanJamRequest;
use App\Http\Requests\PengaturanJam\UpdatePengaturanJamRequest;
use App\Models\PengaturanJam;
use App\Services\PengaturanJam\PengaturanJamService;
use Illuminate\Http\Request;

class PengaturanJamController extends Controller
{
    protected PengaturanJamService $pengaturanJamService;

    public function __construct(PengaturanJamService $pengaturanJamService)
    {
        $this->pengaturanJamService = $pengaturanJamService;
    }

    public function index()
    {
        $data = $this->pengaturanJamService->getMatrixData();
        return view('jam-absensi.index', $data);
    }

    public function store(StorePengaturanJamRequest $request)
    {
        try {
            $this->pengaturanJamService->saveSchedule($request->validated());
            return redirect()->route('jam-absensi.index')->with('success', 'Pengaturan jam absensi berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(UpdatePengaturanJamRequest $request, $id)
    {
        $jam = PengaturanJam::findOrFail($id);
        $this->pengaturanJamService->updateSchedule($jam, $request->validated());

        return redirect()->route('jam-absensi.index')->with('success', "Jam absensi hari {$jam->hari} berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $jam = PengaturanJam::findOrFail($id);
        $hari = $jam->hari;
        $this->pengaturanJamService->deleteSchedule($jam);

        return redirect()->route('jam-absensi.index')->with('success', "Jadwal jam absensi hari {$hari} berhasil dihapus (dijadikan libur)!");
    }
}
