<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kelas\StoreKelasRequest;
use App\Http\Requests\Kelas\UpdateKelasRequest;
use App\Models\Departemen;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $departemens = Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();
        $selectedDepartemenId = $request->input('departemen_id');

        if (!$selectedDepartemenId && $departemens->isNotEmpty()) {
            $selectedDepartemenId = $departemens->first()->id;
        }

        $query = Kelas::with(['wali_kelas', 'departemen', 'tahunAjaran'])
            ->withCount('siswa');

        if ($selectedDepartemenId && $selectedDepartemenId !== 'all') {
            $query->where('departemen_id', $selectedDepartemenId);
        }

        $kelasList = $query->orderBy('tingkat')->orderBy('nama_rombel')->get();
        $groupedKelas = $kelasList->groupBy('tingkat');
        $gurus = Guru::where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        $tahunAjaranAktif = TahunAjaran::active()->first();

        return view('kelas.index', compact('groupedKelas', 'gurus', 'kelasList', 'departemens', 'selectedDepartemenId', 'tahunAjaranAktif'));
    }

    public function store(StoreKelasRequest $request)
    {
        $validated = $request->validated();
        $tahunAjaranAktif = TahunAjaran::active()->first();

        foreach ($validated['nama_rombel'] as $rombel) {
            Kelas::create([
                'departemen_id'   => $validated['departemen_id'],
                'tahun_ajaran_id' => $tahunAjaranAktif?->id,
                'tingkat'         => $validated['tingkat'],
                'nama_rombel'     => $rombel,
                'wali_kelas_id'   => $validated['wali_kelas_id'] ?? null,
            ]);
        }

        return redirect()->route('kelas.index', ['departemen_id' => $validated['departemen_id']])->with('success', 'Data kelas berhasil ditambahkan!');
    }

    public function edit(Kelas $kelas)
    {
        $gurus = Guru::all();
        $departemens = Departemen::where('status', 'Aktif')->get();
        return view('kelas.edit', compact('kelas', 'gurus', 'departemens'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $validated = $request->validated();

        $kelas->update([
            'departemen_id' => $validated['departemen_id'] ?? $kelas->departemen_id,
            'tingkat'       => $validated['tingkat'],
            'nama_rombel'   => $validated['nama_rombel'],
            'wali_kelas_id' => $validated['wali_kelas_id'] ?? null,
        ]);

        return redirect()->route('kelas.index', ['departemen_id' => $kelas->departemen_id])->with('success', 'Data kelas berhasil diperbarui!');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus!');
    }
}
