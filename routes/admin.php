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
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\RekapJamGuruController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Rute khusus panel admin/operator sekolah (master data, pengaturan,
| perizinan, laporan, presensi, backup, dan pengumuman).
|
*/

Route::middleware('auth:web')->group(function () {
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
    Route::resource('guru', GuruController::class)->whereUuid('guru')->except(['show']);
    
    // Pengaturan Jam Absensi
    Route::get('jam-absensi', [PengaturanJamController::class, 'index'])->name('jam-absensi.index');
    Route::post('jam-absensi', [PengaturanJamController::class, 'store'])->name('jam-absensi.store');
    
    // Hari Libur
    Route::get('hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
    Route::post('hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
    Route::delete('hari-libur/{id}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');

    // Pengaturan Lembaga
    Route::get('pengaturan-lembaga', [PengaturanLembagaController::class, 'index'])->name('pengaturan-lembaga.index');
    Route::post('pengaturan-lembaga', [PengaturanLembagaController::class, 'store'])->name('pengaturan-lembaga.store');

    // Pengaturan Akun
    Route::get('pengaturan-akun', [AkunController::class, 'index'])->name('pengaturan-akun.index');
    Route::post('pengaturan-akun', [AkunController::class, 'update'])->name('pengaturan-akun.update');
    
    // Perizinan Siswa / Guru (Admin Input & Monitoring)
    Route::get('izin', [IzinController::class, 'index'])->name('izin.index');
    Route::post('izin', [IzinController::class, 'store'])->name('izin.store');
    
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

    // Notifikasi Telegram
    Route::get('telegram/get-updates', [TelegramController::class, 'fetchUpdates'])->name('telegram.getUpdates');
    Route::post('telegram/sync-bulk', [TelegramController::class, 'syncBulk'])->name('telegram.syncBulk');
    
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
});
