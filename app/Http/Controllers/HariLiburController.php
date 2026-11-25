<?php

namespace App\Http\Controllers;

use App\Http\Requests\HariLibur\StoreHariLiburRequest;
use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index()
    {
        $liburs = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('hari-libur.index', compact('liburs'));
    }

    public function store(StoreHariLiburRequest $request)
    {
        HariLibur::create($request->validated());

        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();

        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
