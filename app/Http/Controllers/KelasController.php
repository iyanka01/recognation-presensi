<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $departemens = \App\Models\Departemen::where('status', 'Aktif')->orderBy('nama_departemen')->get();
        $selectedDepartemenId = $request->input('departemen_id');

        // Jika tidak ada filter dan ada departemen, default ke departemen pertama
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
        $tahunAjaranAktif = \App\Models\TahunAjaran::active()->first();

        return view('kelas.index', compact('groupedKelas', 'gurus', 'kelasList', 'departemens', 'selectedDepartemenId', 'tahunAjaranAktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'departemen_id' => 'required|exists:departemen,id',
            'tingkat'       => 'required|string',
            'nama_rombel'   => 'required|array|min:1',
            'nama_rombel.*' => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);
        
        $tahunAjaranAktif = \App\Models\TahunAjaran::active()->first();

        foreach ($request->nama_rombel as $rombel) {
            Kelas::create([
                'departemen_id'   => $request->departemen_id,
                'tahun_ajaran_id' => $tahunAjaranAktif?->id,
                'tingkat'         => $request->tingkat,
                'nama_rombel'     => $rombel,
                'wali_kelas_id'   => $request->wali_kelas_id,
            ]);
        }
        
        return redirect()->route('kelas.index', ['departemen_id' => $request->departemen_id])->with('success', 'Data kelas berhasil ditambahkan!');
    }

    public function edit(Kelas $kelas)
    {
        $gurus = Guru::all();
        $departemens = \App\Models\Departemen::where('status', 'Aktif')->get();
        return view('kelas.edit', compact('kelas', 'gurus', 'departemens'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'departemen_id' => 'nullable|exists:departemen,id',
            'tingkat'       => 'required|string',
            'nama_rombel'   => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);
        
        $kelas->update([
            'departemen_id' => $request->departemen_id ?: $kelas->departemen_id,
            'tingkat'       => $request->tingkat,
            'nama_rombel'   => $request->nama_rombel,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);
        
        return redirect()->route('kelas.index', ['departemen_id' => $kelas->departemen_id])->with('success', 'Data kelas berhasil diperbarui!');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus!');
    }
}
