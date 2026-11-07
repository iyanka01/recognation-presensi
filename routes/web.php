<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringKBMController;
use App\Http\Controllers\RekapMapelController;

/*
|--------------------------------------------------------------------------
| Web Routes (Landing, Auth & Shared)
|--------------------------------------------------------------------------
|
| Rute umum/landing page, autentikasi (login/logout), dan rute bersama
| antara Admin dan Guru (Dashboard, Monitoring KBM, Rekap Mapel).
|
| Rute spesifik telah dipisahkan ke:
| - routes/admin.php (Khusus Panel Admin)
| - routes/guru.php  (Khusus Portal Guru)
|
*/

// Halaman Utama / Landing Page
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi Pengguna (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Pengguna (Mendukung GET & POST, aman dipanggil kapan saja)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Bersama (Shared Admin & Guru)
Route::middleware('auth:web,guru')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Monitoring KBM Realtime
    Route::get('monitoring-kbm', [MonitoringKBMController::class, 'index'])->name('monitoring-kbm.index');

    // Rekap Absensi Mata Pelajaran (Admin & Guru)
    Route::get('rekap-mapel', [RekapMapelController::class, 'index'])->name('rekap-mapel.index');
    Route::get('rekap-mapel/print', [RekapMapelController::class, 'print'])->name('rekap-mapel.print');
});
