<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mapel\StoreMapelRequest;
use App\Http\Requests\Mapel\UpdateMapelRequest;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = MataPelajaran::withCount('jadwal')->latest()->get();
        return view('mapel.index', compact('mapels'));
    }

    public function store(StoreMapelRequest $request)
    {
        MataPelajaran::create($request->validated());

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function update(UpdateMapelRequest $request, $id)
    {
        $mapel = MataPelajaran::findOrFail($id);
        $mapel->update($request->validated());

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
