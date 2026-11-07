<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLembaga;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SiswaAuthController extends Controller
{
    /**
     * Tampilkan halaman login khusus Siswa & Wali Murid
     */
    public function showLoginForm()
    {
        if (Auth::guard('siswa')->check()) {
            return redirect()->route('siswa.dashboard');
        }

        $lembaga = PengaturanLembaga::first();

        return view('siswa-portal.login', [
            'lembaga' => $lembaga,
        ]);
    }

    /**
     * Proses autentikasi login Siswa/Wali Murid
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Nomor Induk Siswa (NIS/NISN) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim($request->input('username'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Cari siswa berdasarkan NIS atau NISN
        $siswa = Siswa::where('nis', $identifier)
            ->orWhere('nisn', $identifier)
            ->first();

        if (!$siswa) {
            return back()->withErrors([
                'username' => 'Data siswa dengan NIS/NISN tersebut tidak ditemukan.',
            ])->onlyInput('username');
        }

        // Cek apakah password cocok
        if (!Hash::check($password, $siswa->password)) {
            // Fallback: Jika password belum ter-hash atau masih string biasa 'siswa123'
            if ($password === 'siswa123' && ($siswa->password === 'siswa123' || empty($siswa->password))) {
                $siswa->password = Hash::make('siswa123');
                $siswa->save();
            } else {
                return back()->withErrors([
                    'password' => 'Kata sandi yang Anda masukkan salah. Password awal default adalah "siswa123".',
                ])->onlyInput('username');
            }
        }

        Auth::guard('siswa')->login($siswa, $remember);
        $request->session()->regenerate();

        return redirect()->route('siswa.dashboard');
    }

    /**
     * Logout Siswa/Wali Murid
     */
    public function logout(Request $request)
    {
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('siswa.login')->with('success', 'Anda telah berhasil keluar dari portal siswa.');
    }
}
