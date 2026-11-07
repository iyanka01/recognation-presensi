<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|in:Umum,Penting,Akademik,Kegiatan,Libur',
            'target' => 'required|in:semua,guru,siswa',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,docx|max:10240',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('pengumuman', 'public');
        }

        Pengumuman::create([
            'judul' => $request->judul,
            'konten' => $request->konten,
            'kategori' => $request->kategori,
            'target' => $request->target,
            'lampiran' => $lampiranPath,
            'is_aktif' => true,
        ]);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diterbitkan!');
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|in:Umum,Penting,Akademik,Kegiatan,Libur',
            'target' => 'required|in:semua,guru,siswa',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,docx|max:10240',
        ]);

        $lampiranPath = $pengumuman->lampiran;
        if ($request->hasFile('lampiran')) {
            if ($lampiranPath && Storage::disk('public')->exists($lampiranPath)) {
                Storage::disk('public')->delete($lampiranPath);
            }
            $lampiranPath = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $pengumuman->update([
            'judul' => $request->judul,
            'konten' => $request->konten,
            'kategori' => $request->kategori,
            'target' => $request->target,
            'lampiran' => $lampiranPath,
            'is_aktif' => $request->has('is_aktif') ? (bool) $request->is_aktif : true,
        ]);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        if ($pengumuman->lampiran && Storage::disk('public')->exists($pengumuman->lampiran)) {
            Storage::disk('public')->delete($pengumuman->lampiran);
        }
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
