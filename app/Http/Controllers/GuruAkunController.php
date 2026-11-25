<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Guru;
use App\Models\PengaturanLembaga;
use App\Services\Security\SecurityLogService;

class GuruAkunController extends Controller
{
    /**
     * Tampilkan halaman pengaturan akun guru (Layar Penuh: Foto, Profil, Tanggal Lahir, Password)
     */
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        $pengaturan = PengaturanLembaga::first();

        return view('guru.akun', compact('guru', 'pengaturan'));
    }

    /**
     * Update data profil guru (tanggal lahir, tempat lahir, no HP)
     */
    public function updateProfil(Request $request)
    {
        $request->validate([
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'tempat_lahir'  => ['nullable', 'string', 'max:100'],
            'no_hp'         => ['nullable', 'string', 'max:20'],
        ], [
            'tanggal_lahir.date'            => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'tempat_lahir.max'              => 'Tempat lahir maksimal 100 karakter.',
            'no_hp.max'                     => 'Nomor HP maksimal 20 digit.',
        ]);

        $guru = Guru::findOrFail(Auth::guard('guru')->id());
        $guru->update($request->only(['tanggal_lahir', 'tempat_lahir', 'no_hp']));

        SecurityLogService::log('profile.update', 'guru', $guru->id, $guru->username ?: $guru->nip);

        return redirect()->route('guru.akun')->with('success', 'Data profil & tanggal lahir berhasil diperbarui!');
    }

    /**
     * Update foto profil guru
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto_guru' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'foto_guru.required' => 'Silakan pilih berkas foto.',
            'foto_guru.image'    => 'Berkas harus berupa gambar.',
            'foto_guru.mimes'    => 'Format gambar harus JPEG, PNG, JPG, atau WebP.',
            'foto_guru.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $guru = Guru::findOrFail(Auth::guard('guru')->id());

        if ($request->hasFile('foto_guru')) {
            if ($guru->foto_guru && Storage::disk('public')->exists($guru->foto_guru)) {
                Storage::disk('public')->delete($guru->foto_guru);
            }
            $path = $request->file('foto_guru')->store('foto_guru', 'public');
            $guru->foto_guru = $path;
            $guru->save();

            SecurityLogService::log('profile.photo.update', 'guru', $guru->id, $guru->username ?: $guru->nip);
        }

        return redirect()->route('guru.akun')->with('success', 'Foto profil berhasil diperbarui!');
    }
}
