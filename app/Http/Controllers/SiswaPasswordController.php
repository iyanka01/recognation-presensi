<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLembaga;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SiswaPasswordController extends Controller
{
    /**
     * Halaman Menu Pengaturan / Akun Siswa (Sesuai Desain Gambar)
     */
    public function akun()
    {
        $siswa = Auth::guard('siswa')->user();
        if ($siswa->kelas_id) {
            $siswa->load('kelas.waliKelas');
        }

        return view('siswa-portal.akun', [
            'siswa'   => $siswa,
            'lembaga' => PengaturanLembaga::first(),
        ]);
    }

    /**
     * Update Foto Profil Siswa
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto_siswa' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $siswa = Siswa::findOrFail(Auth::guard('siswa')->user()->id);

        if ($request->hasFile('foto_siswa')) {
            $path = $request->file('foto_siswa')->store('foto_siswa', 'public');
            $siswa->foto_siswa = $path;
            $siswa->save();
        }

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    /**
     * Tampilkan form ganti password Siswa / Wali Murid
     */
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();

        return view('siswa-portal.password', [
            'siswa'   => $siswa,
            'lembaga' => PengaturanLembaga::first(),
        ]);
    }

    /**
     * Proses perbaruan password
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $siswa = Siswa::findOrFail(Auth::guard('siswa')->user()->id);

        // Verifikasi password saat ini
        $currentValid = Hash::check($request->current_password, $siswa->password)
            || ($request->current_password === 'siswa123' && ($siswa->password === 'siswa123' || empty($siswa->password)));

        if (!$currentValid) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.'])->onlyInput('current_password');
        }

        $siswa->password = Hash::make($request->password);
        $siswa->is_password_changed = true;
        $siswa->save();

        return redirect()->route('siswa.dashboard')->with('success', 'Kata sandi berhasil diperbarui! Silakan gunakan kata sandi baru ini untuk login berikutnya.');
    }
}
