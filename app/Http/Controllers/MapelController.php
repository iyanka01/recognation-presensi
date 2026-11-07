<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = MataPelajaran::withCount('jadwal')->latest()->get();
        return view('mapel.index', compact('mapels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,' . $mapel->id,
            'nama_mapel' => 'required|string|max:100',
        ]);

        $mapel->update($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $mapel = MataPelajaran::findOrFail($id);
        if ($mapel->jadwal()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus! Mata pelajaran masih dipakai di jadwal pelajaran.');
        }
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus!');
    }
}
