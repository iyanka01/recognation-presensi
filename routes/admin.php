<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\CetakKartuController;
use App\Http\Controllers\PengaturanJamController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\PengaturanLembagaController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\RekapJamGuruController;
use App\Http\Controllers\WhatsAppBroadcastController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Rute khusus panel admin/operator sekolah (master data, pengaturan,
| perizinan, laporan, presensi, backup, dan pengumuman).
|
*/

// Halaman Khusus Login Admin (Akses via /admin atau /admin/login)
Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::get('admin', function () {
    if (auth()->guard('web')->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('admin.login');
})->name('admin.index');

Route::middleware('auth:web')->group(function () {
    Route::match(['get', 'post'], 'admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Master Departemen / Instansi
    Route::resource('departemen', \App\Http\Controllers\DepartemenController::class)->except(['create', 'show']);

    // Master Tahun Ajaran
    Route::resource('tahun-ajaran', \App\Http\Controllers\TahunAjaranController::class)->except(['create', 'show']);

    // Master Kelas
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas'])->except(['create', 'show']);
    
    // Master Siswa
    Route::post('siswa/bulk-delete', [SiswaController::class, 'bulkDestroy'])->name('siswa.bulkDestroy');
    Route::get('siswa/{id}/id-card', [SiswaController::class, 'printIdCard'])->name('siswa.id-card');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::get('siswa/download-template', [SiswaController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');
    Route::resource('siswa', SiswaController::class)->whereUuid('siswa')->except(['show', 'index']);
    
    // Master Guru
    Route::post('guru/bulk-delete', [GuruController::class, 'bulkDestroy'])->name('guru.bulkDestroy');
    Route::get('guru/{id}/id-card', [GuruController::class, 'printIdCard'])->name('guru.id-card');
    Route::post('guru/{id}/reset-face', [GuruController::class, 'resetFace'])->name('guru.reset-face');
    Route::post('guru/{id}/reset-device', [GuruController::class, 'resetDevice'])->name('guru.reset-device');
    Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
    Route::get('guru/download-template', [GuruController::class, 'downloadTemplate'])->name('guru.downloadTemplate');
    Route::resource('guru', GuruController::class)->whereUuid('guru')->except(['show']);
    
    // Pengaturan Jam Absensi
    Route::get('jam-absensi', [PengaturanJamController::class, 'index'])->name('jam-absensi.index');
    Route::post('jam-absensi', [PengaturanJamController::class, 'store'])->name('jam-absensi.store');
    Route::put('jam-absensi/{id}', [PengaturanJamController::class, 'update'])->name('jam-absensi.update');
    Route::delete('jam-absensi/{id}', [PengaturanJamController::class, 'destroy'])->name('jam-absensi.destroy');
    
    // Hari Libur
    Route::get('hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
    Route::post('hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
    Route::delete('hari-libur/{id}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');

    // Pengaturan Lembaga
    Route::get('pengaturan-lembaga', [PengaturanLembagaController::class, 'index'])->name('pengaturan-lembaga.index');
    Route::post('pengaturan-lembaga', [PengaturanLembagaController::class, 'store'])->name('pengaturan-lembaga.store');
    Route::post('pengaturan-lembaga/test-wa', [PengaturanLembagaController::class, 'testWhatsApp'])->name('pengaturan-lembaga.test-wa');

    // Pengaturan Akun
    Route::get('pengaturan-akun', [AkunController::class, 'index'])->name('pengaturan-akun.index');
    Route::post('pengaturan-akun', [AkunController::class, 'update'])->name('pengaturan-akun.update');
    
    // Perizinan Siswa / Guru (Admin Input, Approval & Monitoring)
    Route::get('izin', [IzinController::class, 'index'])->name('izin.index');
    Route::post('izin', [IzinController::class, 'store'])->name('izin.store');
    Route::post('izin/{id}/approve', [IzinController::class, 'approve'])->name('izin.approve');
    Route::post('izin/{id}/reject', [IzinController::class, 'reject'])->name('izin.reject');
    Route::delete('izin/{id}', [IzinController::class, 'destroy'])->name('izin.destroy');
    
    // Laporan & Rekapitulasi Presensi
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    Route::get('laporan/print-siswa', [LaporanController::class, 'printSiswa'])->name('laporan.print_siswa');
    Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

    // Rekap Jumlah Jam Mengajar Guru (Jurnal & KBM)
    Route::get('rekap-jam-guru', [RekapJamGuruController::class, 'index'])->name('rekap-jam-guru.index');
    Route::get('rekap-jam-guru/print', [RekapJamGuruController::class, 'print'])->name('rekap-jam-guru.print');
    Route::get('rekap-jam-guru/print/{guru_id}', [RekapJamGuruController::class, 'printDetail'])->name('rekap-jam-guru.print-detail');

    // Backup & Restore Database
    Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('backup/create', [BackupController::class, 'create'])->name('backup.create');
    Route::get('backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');
    Route::post('backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    Route::post('backup/tarik-api', [BackupController::class, 'tarikApi'])->name('backup.tarikApi');

    // Notifikasi Telegram
    Route::get('telegram/get-updates', [TelegramController::class, 'fetchUpdates'])->name('telegram.getUpdates');
    Route::post('telegram/sync-bulk', [TelegramController::class, 'syncBulk'])->name('telegram.syncBulk');
    
    // Broadcast & Laporan WhatsApp (Wanesia Gateway)
    Route::get('whatsapp-broadcast', [WhatsAppBroadcastController::class, 'index'])->name('whatsapp-broadcast.index');
    Route::post('whatsapp-broadcast/send', [WhatsAppBroadcastController::class, 'send'])->name('whatsapp-broadcast.send');
    Route::post('whatsapp-broadcast/send-manual', [WhatsAppBroadcastController::class, 'sendManualBroadcast'])->name('whatsapp-broadcast.sendManual');
    Route::post('whatsapp-broadcast/send-peringatan-guru', [WhatsAppBroadcastController::class, 'kirimPeringatanGuruManual'])->name('whatsapp-broadcast.sendPeringatanGuru');
    Route::get('whatsapp-broadcast/preview', [WhatsAppBroadcastController::class, 'previewMessage'])->name('whatsapp-broadcast.preview');
    
    // Cetak Kartu ID
    Route::get('cetak-kartu', [CetakKartuController::class, 'index'])->name('cetak-kartu.index');
    Route::get('cetak-kartu/get-siswa/{kelas_id}', [CetakKartuController::class, 'getSiswa'])->name('cetak-kartu.getSiswa');
    Route::post('cetak-kartu/print', [CetakKartuController::class, 'print'])->name('cetak-kartu.print');
    
    // Presensi Harian & Scanner QR
    Route::get('scan', [PresensiController::class, 'scan'])->name('presensi.scan');
    Route::post('scan/process', [PresensiController::class, 'processScan'])->name('presensi.process');
    Route::post('presensi/bulk-delete', [PresensiController::class, 'bulkDestroy'])->name('presensi.bulkDestroy');
    Route::post('presensi/{id}/unlock', [PresensiController::class, 'unlock'])->name('presensi.unlock');
    Route::get('presensi', [PresensiController::class, 'index'])->name('presensi.index');

    // Mata Pelajaran
    Route::resource('mapel', MapelController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['mapel' => 'mapel']);

    // Jadwal Pelajaran
    Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::put('jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');

    // Pengumuman Sekolah
    Route::resource('pengumuman', PengumumanController::class);

    // Kegiatan Khusus (CRUD Admin)
    Route::get('kegiatan-khusus', [\App\Http\Controllers\KegiatanKhususController::class, 'index'])->name('kegiatan-khusus.index');
    Route::post('kegiatan-khusus', [\App\Http\Controllers\KegiatanKhususController::class, 'store'])->name('kegiatan-khusus.store');
    Route::put('kegiatan-khusus/{id}', [\App\Http\Controllers\KegiatanKhususController::class, 'update'])->name('kegiatan-khusus.update');
    Route::delete('kegiatan-khusus/{id}', [\App\Http\Controllers\KegiatanKhususController::class, 'destroy'])->name('kegiatan-khusus.destroy');
    Route::get('kegiatan-khusus/{id}/peserta', [\App\Http\Controllers\KegiatanKhususController::class, 'peserta'])->name('kegiatan-khusus.peserta');

    // Laporan Kegiatan Khusus
    Route::get('laporan-kegiatan', [\App\Http\Controllers\LaporanKegiatanController::class, 'index'])->name('laporan-kegiatan.index');
    Route::get('laporan-kegiatan/print', [\App\Http\Controllers\LaporanKegiatanController::class, 'print'])->name('laporan-kegiatan.print');
    Route::get('laporan-kegiatan/export-excel', [\App\Http\Controllers\LaporanKegiatanController::class, 'exportExcel'])->name('laporan-kegiatan.export-excel');
});

// Terminal Scanner Kegiatan Khusus (Bisa diakses Admin & Guru)
Route::middleware('auth:web,guru')->group(function () {
    Route::get('kegiatan-khusus/{id}/scan', [\App\Http\Controllers\KegiatanKhususController::class, 'scan'])->name('kegiatan-khusus.scan');
    Route::post('kegiatan-khusus/{id}/scan/process', [\App\Http\Controllers\KegiatanKhususController::class, 'processScan'])->name('kegiatan-khusus.scan.process');
});
