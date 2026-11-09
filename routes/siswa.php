<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaAuthController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SiswaIzinController;
use App\Http\Controllers\SiswaPasswordController;

/*
|--------------------------------------------------------------------------
| Siswa & Wali Murid Routes
|--------------------------------------------------------------------------
|
| Rute khusus portal siswa dan orang tua / wali murid (autentikasi NIS/NISN,
| dashboard kehadiran harian, statistik presensi, jadwal pelajaran,
| pengajuan perizinan mandiri, dan ganti kata sandi akun).
|
*/

// Rute pintar /siswa:
// - Jika Admin yang mengakses -> Tampilkan Master Data Siswa (siswa.index)
// - Jika Siswa yang mengakses -> Arahkan ke Dashboard Siswa (siswa.dashboard)
// - Jika Guru yang mengakses -> Arahkan ke Dashboard Guru
// - Jika Belum Login (Tamu / Wali Murid) -> Langsung diarahkan ke Login Portal Siswa (siswa.login)
Route::get('/siswa', function () {
    if (auth()->guard('web')->check()) {
        return app(\App\Http\Controllers\SiswaController::class)->index(request());
    }
    if (auth()->guard('siswa')->check()) {
        return redirect()->route('siswa.dashboard');
    }
    if (auth()->guard('guru')->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('siswa.login');
})->name('siswa.index');

// Alias /portal-siswa
Route::get('/portal-siswa', function () {
    if (auth()->guard('siswa')->check()) {
        return redirect()->route('siswa.dashboard');
    }
    return redirect()->route('siswa.login');
});



// Autentikasi Siswa (Guest)
Route::middleware('guest:siswa')->group(function () {
    Route::get('/siswa/login', [SiswaAuthController::class, 'showLoginForm'])->name('siswa.login');
    Route::post('/siswa/login', [SiswaAuthController::class, 'login'])->name('siswa.login.post');
});

// Portal Siswa Terproteksi
Route::middleware('auth:siswa')->prefix('siswa')->name('siswa.')->group(function () {
    Route::match(['get', 'post'], 'logout', [SiswaAuthController::class, 'logout'])->name('logout');

    // Dashboard Utama
    Route::get('dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    // Riwayat Presensi (Harian & Mapel)
    Route::get('presensi', [SiswaDashboardController::class, 'presensi'])->name('presensi');
    Route::get('presensi/cetak', [SiswaDashboardController::class, 'cetak'])->name('presensi.cetak');

    // Kartu Pelajar Siswa
    Route::get('kartu-nama', [SiswaDashboardController::class, 'kartuNama'])->name('kartu-nama');

    // Pengajuan Izin / Sakit Mandiri Wali Murid
    Route::get('izin', [SiswaIzinController::class, 'index'])->name('izin');
    Route::post('izin', [SiswaIzinController::class, 'store'])->name('izin.store');

    // Pengaturan Akun & Profil Siswa
    Route::get('akun', [SiswaPasswordController::class, 'akun'])->name('akun');
    Route::post('foto', [SiswaPasswordController::class, 'updateFoto'])->name('foto.update');

    // Ubah Password Akun Siswa / Wali Murid
    Route::get('password', [SiswaPasswordController::class, 'index'])->name('password');
    Route::put('password', [SiswaPasswordController::class, 'update'])->name('password.update');
});

