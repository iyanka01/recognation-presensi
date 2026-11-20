<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (!$request->boolean('force')) {
            $currentHost = $request->getHost();
            $isLocalDev = in_array($currentHost, ['localhost', '127.0.0.1']);
            $isNewDomain = ($currentHost === 'presensi.sabiilunnaja.web.id');

            if (!$isNewDomain && !$isLocalDev) {
                return view('landing.pengalihan');
            }
        }

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
            'role'     => ['nullable', 'in:guru,siswa'],
        ]);

        $usernameInput = trim($request->input('username'));
        $passwordInput = $request->input('password');
        $rolePreference = $request->input('role', 'guru');
        $remember = $request->boolean('remember');

        // Helper login Guru (NIP / Username)
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

        // Helper login Siswa (NIS / NISN)
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

        // Cek jika akun yang dimasukkan adalah akun administrator
        $cekAdmin = \App\Models\AdminUser::where('username', $usernameInput)->first();
        if ($cekAdmin && Hash::check($passwordInput, $cekAdmin->password)) {
            return back()->withErrors([
                'username' => 'Akun Administrator tidak dapat login di halaman ini. Silakan login melalui Panel Administrator di /admin/login.',
            ])->onlyInput('username');
        }

        if ($rolePreference === 'siswa') {
            $res = $trySiswaLogin();
            if ($res && $res['success']) {
                return redirect()->route('siswa.dashboard');
            }
            return back()->withErrors([
                'username' => 'NIS/NISN atau kata sandi siswa tidak sesuai.',
            ])->onlyInput('username');
        } else {
            // Default role: Guru
            $res = $tryGuruLogin();
            if ($res && $res['success']) {
                return redirect()->intended(route('dashboard'));
            } elseif ($res && isset($res['error'])) {
                return back()->withErrors(['username' => $res['error']])->onlyInput('username');
            }

            // Fallback: Jika pengguna ternyata siswa yang mengetik di tab guru
            $resSiswa = $trySiswaLogin();
            if ($resSiswa && $resSiswa['success']) {
                return redirect()->route('siswa.dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'NIP/NIS atau kata sandi yang Anda masukkan salah.',
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
