<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartemenController extends Controller
{
    public function index()
    {
        $departemens = Departemen::with('kepalaSekolah')
            ->withCount('kelas')
            ->latest()
            ->paginate(15);

        $gurus = Guru::where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();

        return view('departemen.index', compact('departemens', 'gurus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_departemen'    => ['required', 'string', 'max:50', 'unique:departemen,kode_departemen'],
            'nama_departemen'    => ['required', 'string', 'max:255'],
            'tingkatan'          => ['required', 'string', 'max:100'],
            'kepala_sekolah_id'  => ['nullable', 'exists:guru,id'],
            'status'             => ['required', 'in:Aktif,Tidak Aktif'],
            'logo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'alamat'             => ['nullable', 'string'],
            'no_telp'            => ['nullable', 'string', 'max:50'],
            'keterangan'         => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('departemen', 'public');
        }

        Departemen::create($validated);

        return redirect()->route('departemen.index')->with('success', 'Departemen baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $departemen = Departemen::findOrFail($id);

        $validated = $request->validate([
            'kode_departemen'    => ['required', 'string', 'max:50', 'unique:departemen,kode_departemen,' . $departemen->id],
            'nama_departemen'    => ['required', 'string', 'max:255'],
            'tingkatan'          => ['required', 'string', 'max:100'],
            'kepala_sekolah_id'  => ['nullable', 'exists:guru,id'],
            'status'             => ['required', 'in:Aktif,Tidak Aktif'],
            'logo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'alamat'             => ['nullable', 'string'],
            'no_telp'            => ['nullable', 'string', 'max:50'],
            'keterangan'         => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            if ($departemen->logo) {
                Storage::disk('public')->delete($departemen->logo);
            }
            $validated['logo'] = $request->file('logo')->store('departemen', 'public');
        }

        $departemen->update($validated);

        return redirect()->route('departemen.index')->with('success', 'Data Departemen berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $departemen = Departemen::withCount('kelas')->findOrFail($id);

        if ($departemen->kelas_count > 0) {
            return redirect()->route('departemen.index')->with('error', 'Departemen tidak dapat dihapus karena masih memiliki data kelas!');
        }

        if ($departemen->logo) {
            Storage::disk('public')->delete($departemen->logo);
        }

        $departemen->delete();

        return redirect()->route('departemen.index')->with('success', 'Departemen berhasil dihapus!');
    }
}
