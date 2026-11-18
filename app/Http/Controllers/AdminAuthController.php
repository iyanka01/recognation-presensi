<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\PengaturanLembaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Tampilkan halaman login khusus Administrator
     */
    public function showLoginForm()
    {
        // Jika sudah login sebagai Admin, langsung ke dashboard
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $lembaga = PengaturanLembaga::first();

        return view('admin.login', compact('lembaga'));
    }

    /**
     * Proses autentikasi khusus Administrator
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username administrator wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $admin = AdminUser::where('username', $username)->first();

        if ($admin && Hash::check($password, $admin->password)) {
            Auth::guard('web')->login($admin, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi administrator salah.',
        ])->onlyInput('username');
    }

    /**
     * Logout Administrator
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil keluar dari panel administrator.');
    }
}
