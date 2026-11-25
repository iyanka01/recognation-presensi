<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jadwal\StoreJadwalRequest;
use App\Http\Requests\Jadwal\UpdateJadwalRequest;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Services\Jadwal\JadwalService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    protected JadwalService $jadwalService;

    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    public function index(Request $request)
    {
        $departemenId = $request->query('departemen_id');
        $kelasId = $request->query('kelas_id');
        $hari = $request->query('hari');

        $data = $this->jadwalService->getIndexData($departemenId, $kelasId, $hari);

        return view('jadwal.index', $data);
    }

    public function store(StoreJadwalRequest $request)
    {
        $validated = $request->validated();

        if ($error = $this->jadwalService->cekBentrok($validated)) {
            return back()->withInput()->with('error', $error);
        }

        $kelas = Kelas::find($validated['kelas_id']);
        $departemenId = $kelas?->departemen_id;

        $this->jadwalService->store($validated);

        return redirect()->route('jadwal.index', array_filter([
            'departemen_id' => $departemenId,
            'kelas_id' => $validated['kelas_id'],
            'hari' => $validated['hari'],
        ]))->with('success', 'Jadwal pelajaran berhasil ditambahkan!');
    }

    public function update(UpdateJadwalRequest $request, $id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        $validated = $request->validated();

        if ($error = $this->jadwalService->cekBentrok($validated, $jadwal->id)) {
            return back()->withInput()->with('error', $error);
        }

        $this->jadwalService->update($jadwal, $validated);

        $kelas = Kelas::find($validated['kelas_id']);
        $departemenId = $kelas?->departemen_id;

        return redirect()->route('jadwal.index', array_filter([
            'departemen_id' => $departemenId,
            'kelas_id' => $validated['kelas_id'],
            'hari' => $validated['hari'],
        ]))->with('success', 'Jadwal pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        $this->jadwalService->delete($jadwal);

        return back()->with('success', 'Jadwal pelajaran berhasil dihapus!');
    }
}
