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
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (!$request->boolean('force')) {
        $currentHost = $request->getHost();
        $isLocalDev = in_array($currentHost, ['localhost', '127.0.0.1']);
        $isNewDomain = ($currentHost === 'presensi.sabiilunnaja.web.id');

        if (!$isNewDomain && !$isLocalDev) {
            return view('landing.pengalihan');
        }
    }
    return redirect()->route('login');
});

// Route Preview Langsung Halaman Pengalihan Satu Pintu
Route::get('/pengalihan', function () {
    return view('landing.pengalihan');
})->name('landing.pengalihan');

// Autentikasi Pengguna (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
});

// Logout Pengguna (Diwajibkan POST dengan CSRF, akses GET dialihkan aman ke login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function () {
    return redirect()->route('login');
});

// Rute Bersama (Shared Admin & Guru)
Route::middleware('auth:web,guru')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Monitoring KBM Realtime
    Route::get('monitoring-kbm', [MonitoringKBMController::class, 'index'])->name('monitoring-kbm.index');

    // Rekap Absensi Mata Pelajaran (Admin & Guru)
    Route::get('rekap-mapel', [RekapMapelController::class, 'index'])->name('rekap-mapel.index');
    Route::get('rekap-mapel/print', [RekapMapelController::class, 'print'])->name('rekap-mapel.print');
    Route::get('rekap-mapel/preview-wa', [RekapMapelController::class, 'previewWhatsApp'])->name('rekap-mapel.preview-wa');
    Route::post('rekap-mapel/send-wa', [RekapMapelController::class, 'sendWhatsApp'])->name('rekap-mapel.send-wa');
});

// Fallback aman untuk melayani file upload storage publik jika symlink OS tidak tersedia
Route::get('/storage/{path}', function ($path) {
    // Sanitasi dan cegah path traversal
    $cleanPath = str_replace(['..', '\\'], ['', '/'], $path);
    $cleanPath = ltrim($cleanPath, '/');

    $publicStorage = realpath(storage_path('app/public'));
    $filePath = realpath(storage_path('app/public/' . $cleanPath));

    if (!$filePath || !str_starts_with($filePath, $publicStorage) || !file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');

// Endpoint API Ekspor Data Migrasi Sekolah (Dilindungi Token Env & Rate Limiter)
Route::middleware('throttle:10,1')->match(['get', 'post'], '/api/export-migrasi-sekolah', function (\Illuminate\Http\Request $request) {
    $configuredToken = config('services.migration.token', env('MIGRATION_API_TOKEN', 'mayantara-migrasi-2026'));

    // Dukung header Authorization: Bearer <token> atau query api_token (backward compatibility)
    $authHeader = $request->bearerToken();
    $providedToken = $authHeader ?: $request->input('api_token', $request->query('api_token'));

    if (empty($configuredToken) || !hash_equals($configuredToken, (string) $providedToken)) {
        \App\Services\Security\SecurityLogService::log('migration.export.denied', 'api', null, 'anonymous', [
            'reason' => 'invalid_or_missing_token',
        ], $request);

        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak: Token API tidak valid atau tidak memiliki otoritas.'
        ], 403);
    }

    \App\Services\Security\SecurityLogService::log('migration.export.success', 'api', null, 'migrator', [
        'action' => 'export_all_records',
    ], $request);

    // Ambil data tanpa mengekspos hash password siswa & guru
    $gurus = \App\Models\Guru::all()->makeHidden(['password', 'remember_token']);
    $siswas = \App\Models\Siswa::all()->makeHidden(['password', 'remember_token']);

    return response()->json([
        'success'   => true,
        'timestamp' => now()->toDateTimeString(),
        'data'      => [
            'kelas'           => \App\Models\Kelas::all(),
            'guru'            => $gurus,
            'siswa'           => $siswas,
            'mapel'           => \App\Models\MataPelajaran::all(),
            'jadwal'          => \App\Models\JadwalPelajaran::all(),
            'presensi'        => \App\Models\Presensi::latest()->take(20000)->get(),
            'presensi_mapel'  => \App\Models\PresensiMapel::latest()->take(30000)->get(),
            'jurnal_mengajar' => \App\Models\JurnalMengajar::latest()->take(10000)->get(),
            'pengaturan_jam'  => \App\Models\PengaturanJam::all(),
            'pengaturan_lembaga' => \App\Models\PengaturanLembaga::first(),
        ]
    ]);
});

