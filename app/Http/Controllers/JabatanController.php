<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => ['required', 'string', 'max:255', 'unique:jabatans,nama_jabatan'],
            'kategori'     => ['required', 'in:guru,staff'],
            'keterangan'   => ['nullable', 'string'],
            'urutan'       => ['nullable', 'integer'],
        ]);

        $validated['urutan'] = $validated['urutan'] ?? 0;

        Jabatan::create($validated);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'nama_jabatan' => ['required', 'string', 'max:255', 'unique:jabatans,nama_jabatan,' . $jabatan->id],
            'kategori'     => ['required', 'in:guru,staff'],
            'keterangan'   => ['nullable', 'string'],
            'urutan'       => ['nullable', 'integer'],
        ]);

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
