<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('wali_kelas')->orderBy('tingkat')->orderBy('nama_rombel')->get();
        $gurus = Guru::all();
        return view('kelas.index', compact('kelas', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|string',
            'nama_rombel' => 'required|array|min:1',
            'nama_rombel.*' => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);
        
        foreach ($request->nama_rombel as $rombel) {
            Kelas::create([
                'tingkat' => $request->tingkat,
                'nama_rombel' => $rombel,
                'wali_kelas_id' => $request->wali_kelas_id,
            ]);
        }
        
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil ditambahkan!');
    }

    public function edit(Kelas $kelas)
    {
        $gurus = Guru::all();
        return view('kelas.edit', compact('kelas', 'gurus'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'tingkat' => 'required|string',
            'nama_rombel' => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);
        
        $kelas->update([
            'tingkat' => $request->tingkat,
            'nama_rombel' => $request->nama_rombel,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);
        
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diperbarui!');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus!');
    }
}
