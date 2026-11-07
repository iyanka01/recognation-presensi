<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('guru')->check() || Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        if (Auth::guard('siswa')->check()) {
            return redirect()->route('siswa.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role'     => ['nullable', 'in:admin,guru,siswa'],
        ]);

        $usernameInput = trim($request->input('username'));
        $passwordInput = $request->input('password');
        $rolePreference = $request->input('role', 'admin');
        $remember = $request->boolean('remember');

        // Helper untuk mencoba login sebagai Guru (mencari via NIP maupun Username)
        $tryGuruLogin = function () use ($usernameInput, $passwordInput, $remember, $request) {
            $guru = Guru::where(function ($q) use ($usernameInput) {
                $q->where('username', $usernameInput)
                  ->orWhere('nip', $usernameInput);
            })->first();

            if ($guru && Hash::check($passwordInput, $guru->password)) {
                if (strtolower($guru->status) !== 'aktif') {
                    return [
                        'success' => false,
                        'error'   => 'Akun guru Anda berstatus "' . $guru->status . '". Silakan hubungi admin sekolah.',
                    ];
                }
                Auth::guard('guru')->login($guru, $remember);
                $request->session()->regenerate();
                return ['success' => true];
            }
            return null;
        };

        // Helper untuk mencoba login sebagai Admin
        $tryAdminLogin = function () use ($usernameInput, $passwordInput, $remember, $request) {
            $admin = AdminUser::where('username', $usernameInput)->first();
            if ($admin && Hash::check($passwordInput, $admin->password)) {
                Auth::guard('web')->login($admin, $remember);
                $request->session()->regenerate();
                return ['success' => true];
            }
            return null;
        };

        // Helper untuk mencoba login sebagai Siswa / Wali Murid
        $trySiswaLogin = function () use ($usernameInput, $passwordInput, $remember, $request) {
            $siswa = \App\Models\Siswa::where('nis', $usernameInput)
                ->orWhere('nisn', $usernameInput)
                ->first();

            if ($siswa) {
                if (!Hash::check($passwordInput, $siswa->password)) {
                    // Fallback jika password masih default siswa123
                    if ($passwordInput === 'siswa123' && ($siswa->password === 'siswa123' || empty($siswa->password))) {
                        $siswa->password = Hash::make('siswa123');
                        $siswa->save();
                    } else {
                        return null;
                    }
                }
                Auth::guard('siswa')->login($siswa, $remember);
                $request->session()->regenerate();
                return ['success' => true];
            }
            return null;
        };

        // 1. Coba login sesuai preferensi tombol role
        if ($rolePreference === 'siswa') {
            $res = $trySiswaLogin();
            if ($res && $res['success']) {
                return redirect()->route('siswa.dashboard');
            }
            return back()->withErrors([
                'username' => 'NIS/NISN atau kata sandi siswa tidak sesuai.',
            ])->onlyInput('username');
        } elseif ($rolePreference === 'guru') {
            $res = $tryGuruLogin();
            if ($res && $res['success']) {
                return redirect()->intended(route('dashboard'));
            } elseif ($res && isset($res['error'])) {
                return back()->withErrors(['username' => $res['error']])->onlyInput('username');
            }

            // Fallback otomatis: jika pengguna sebenarnya adalah admin
            $resAdmin = $tryAdminLogin();
            if ($resAdmin && $resAdmin['success']) {
                return redirect()->intended(route('dashboard'));
            }

            // Fallback otomatis: jika pengguna sebenarnya adalah siswa
            $resSiswa = $trySiswaLogin();
            if ($resSiswa && $resSiswa['success']) {
                return redirect()->route('siswa.dashboard');
            }
        } else {
            // Preferensi role admin
            $res = $tryAdminLogin();
            if ($res && $res['success']) {
                return redirect()->intended(route('dashboard'));
            }

            // Fallback otomatis: jika pengguna sebenarnya adalah guru
            $resGuru = $tryGuruLogin();
            if ($resGuru && $resGuru['success']) {
                return redirect()->intended(route('dashboard'));
            } elseif ($resGuru && isset($resGuru['error'])) {
                return back()->withErrors(['username' => $resGuru['error']])->onlyInput('username');
            }

            // Fallback otomatis: jika pengguna sebenarnya adalah siswa
            $resSiswa = $trySiswaLogin();
            if ($resSiswa && $resSiswa['success']) {
                return redirect()->route('siswa.dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'Username/NIP/NIS atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
