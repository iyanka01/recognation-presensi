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

// Route inisialisasi darurat untuk Admin
Route::get('/init-admin', function () {
    try {
        $admin = \App\Models\AdminUser::firstOrNew(['username' => 'admin']);
        if (!$admin->exists) {
            $admin->id = (string) \Illuminate\Support\Str::uuid();
            $admin->nama_lengkap = 'Administrator';
            $admin->role = 'admin';
        }
        $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
        $admin->save();

        $totalAdmins = \App\Models\AdminUser::count();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();

        return response()->json([
            'status' => 'success',
            'message' => 'Akun admin berhasil dibuat / direset!',
            'username' => 'admin',
            'password' => 'admin123',
            'database' => $dbName,
            'total_admin_in_db' => $totalAdmins,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 500);
    }
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
