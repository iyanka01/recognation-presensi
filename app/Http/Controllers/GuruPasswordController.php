<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\PengaturanLembaga;

class GuruPasswordController extends Controller
{
    /**
     * Tampilkan form ubah password untuk guru
     */
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        $isDefaultPassword = Hash::check(GuruController::PASSWORD_DEFAULT, $guru->password);
        $pengaturan = PengaturanLembaga::first();

        return view('guru.password', compact('guru', 'isDefaultPassword', 'pengaturan'));
    }

    /**
     * Proses pembaruan password guru
     */
    public function update(Request $request)
    {
        $guruId = Auth::guard('guru')->id();
        $guru = \App\Models\Guru::findOrFail($guruId);

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dengan password saat ini.',
        ]);

        // Verifikasi password saat ini
        $isCurrentValid = false;
        try {
            $isCurrentValid = !empty($guru->password) && Hash::check($request->current_password, $guru->password);
        } catch (\Throwable $e) {
            $isCurrentValid = false;
        }

        if (!$isCurrentValid) {
            \App\Services\Security\SecurityLogService::log('password.update.failed', 'guru', $guru->id, $guru->username ?: $guru->nip, ['reason' => 'invalid_current_password']);
            return back()->withErrors([
                'current_password' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        // Simpan password baru langsung ke database
        $guru->password = Hash::make($request->password);
        $guru->save();

        \App\Services\Security\SecurityLogService::log('password.update.success', 'guru', $guru->id, $guru->username ?: $guru->nip);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diperbarui! Silakan gunakan password baru ini untuk login berikutnya.');
    }
}
