<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HariLibur;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    public function index()
    {
        $liburs = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('hari-libur.index', compact('liburs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255'
        ], [
            'tanggal.unique' => 'Tanggal ini sudah diatur sebagai hari libur.'
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();
        
        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
