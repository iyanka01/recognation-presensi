<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::withCount('kelas')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('tahun-ajaran.index', compact('tahunAjarans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran'   => ['required', 'string', 'max:50'],
            'tanggal_mulai'  => ['required', 'date'],
            'tanggal_akhir'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status'         => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        if ($validated['status'] === 'Aktif') {
            TahunAjaran::where('status', 'Aktif')->update(['status' => 'Tidak Aktif']);
        }

        TahunAjaran::create($validated);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $ta = TahunAjaran::findOrFail($id);

        $validated = $request->validate([
            'tahun_ajaran'   => ['required', 'string', 'max:50'],
            'tanggal_mulai'  => ['required', 'date'],
            'tanggal_akhir'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status'         => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        if ($validated['status'] === 'Aktif') {
            TahunAjaran::where('id', '!=', $ta->id)->where('status', 'Aktif')->update(['status' => 'Tidak Aktif']);
        }

        $ta->update($validated);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $ta = TahunAjaran::withCount('kelas')->findOrFail($id);

        if ($ta->kelas_count > 0) {
            return redirect()->route('tahun-ajaran.index')->with('error', 'Tahun ajaran tidak dapat dihapus karena masih terkait dengan data kelas!');
        }

        $ta->delete();

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran berhasil dihapus!');
    }
}
