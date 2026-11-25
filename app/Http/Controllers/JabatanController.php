<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jabatan\StoreJabatanRequest;
use App\Http\Requests\Jabatan\UpdateJabatanRequest;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::withCount('gurus')
            ->orderBy('kategori', 'asc')
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_jabatan', 'asc')
            ->paginate(20);

        return view('jabatan.index', compact('jabatans'));
    }

    public function store(StoreJabatanRequest $request)
    {
        $validated = $request->validated();
        $validated['urutan'] = $validated['urutan'] ?? 0;

        Jabatan::create($validated);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan baru berhasil ditambahkan!');
    }

    public function update(UpdateJabatanRequest $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $validated = $request->validated();
        $validated['urutan'] = $validated['urutan'] ?? 0;

        $jabatan->update($validated);

        return redirect()->route('jabatan.index')->with('success', 'Data jabatan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        if ($jabatan->gurus()->count() > 0) {
            return redirect()->route('jabatan.index')->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh pegawai!');
        }

        $jabatan->delete();

        return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil dihapus!');
    }
}
