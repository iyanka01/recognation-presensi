<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    const PASSWORD_DEFAULT = 'guru123';

    public function index()
    {
        $guru = Guru::latest()->get();
        return view('guru.index', compact('guru'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|unique:guru,nip',
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->hasFile('foto_guru')) {
            $fotoPath = $request->file('foto_guru')->store('foto_guru', 'public');
            $validated['foto_guru'] = $fotoPath;
        }

        // Auto-generate akun login guru (username = NIP, password custom atau default)
        if (!empty($validated['nip'])) {
            $validated['username'] = $validated['nip'];
            $passwordRaw = $request->filled('password') ? $request->password : self::PASSWORD_DEFAULT;
            $validated['password'] = Hash::make($passwordRaw);
        }

        Guru::create($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan!');
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|unique:guru,nip,' . $guru->id,
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Aktif,Nonaktif,Pensiun,Pindah',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('foto_guru')) {
            if ($guru->foto_guru) {
                Storage::disk('public')->delete($guru->foto_guru);
            }
            $fotoPath = $request->file('foto_guru')->store('foto_guru', 'public');
            $validated['foto_guru'] = $fotoPath;
        }

        // Sinkronkan akun login guru dengan NIP & update password jika diisi
        if (!empty($validated['nip'])) {
            $validated['username'] = $validated['nip'];
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } elseif (empty($guru->password)) {
                $validated['password'] = Hash::make(self::PASSWORD_DEFAULT);
            } else {
                unset($validated['password']);
            }
        } else {
            $validated['username'] = null;
            $validated['password'] = null;
        }

        $guru->update($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui!');
    }

    public function destroy(Guru $guru)
    {
        if ($guru->foto_guru) {
            Storage::disk('public')->delete($guru->foto_guru);
        }
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus!');
    }

    public function printIdCard($id)
    {
        $user = Guru::findOrFail($id);
        $type = 'guru';
        return view('id-card', compact('user', 'type'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:guru,id',
        ]);

        $gurus = Guru::whereIn('id', $request->ids)->get();
        foreach ($gurus as $guru) {
            if ($guru->foto_guru) {
                Storage::disk('public')->delete($guru->foto_guru);
            }
            $guru->delete();
        }

        return redirect()->route('guru.index')->with('success', count($request->ids) . ' data guru berhasil dihapus!');
    }
}
