<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiMapelController;
use App\Http\Controllers\GuruJadwalController;
use App\Http\Controllers\GuruIzinController;
use App\Http\Controllers\GuruInformasiController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\GuruPasswordController;

/*
|--------------------------------------------------------------------------
| Guru Routes
|--------------------------------------------------------------------------
|
| Rute khusus portal guru (absensi mapel, jadwal mengajar, pengajuan izin,
| pengumuman guru, face recognition, absensi masuk/pulang, dan ganti password).
|
*/

Route::middleware('auth:guru')->group(function () {
    // Absensi Mata Pelajaran
    Route::get('absensi-mapel', [AbsensiMapelController::class, 'index'])->name('absensi-mapel.index');
    Route::get('absensi-mapel/{id}', [AbsensiMapelController::class, 'show'])
        ->whereUuid('id')->name('absensi-mapel.show');
    Route::post('absensi-mapel/{id}', [AbsensiMapelController::class, 'store'])
        ->whereUuid('id')->name('absensi-mapel.store');

    // Jadwal Mengajar Lengkap Guru
    Route::get('guru/jadwal', [GuruJadwalController::class, 'index'])->name('guru.jadwal.index');

    // Pengajuan Izin / Sakit / Dinas / Cuti Mandiri Guru
    Route::get('guru/izin', [GuruIzinController::class, 'index'])->name('guru.izin.index');
    Route::post('guru/izin', [GuruIzinController::class, 'store'])->name('guru.izin.store');

    // Informasi / Pengumuman Sekolah untuk Guru
    Route::get('guru/informasi', [GuruInformasiController::class, 'index'])->name('guru.informasi.index');
    Route::get('guru/informasi/{id}', [GuruInformasiController::class, 'show'])->name('guru.informasi.show');

    // Face Recognition (Absensi Wajah Guru)
    Route::get('face-scan', [FaceRecognitionController::class, 'scan'])->name('guru.face-scan');
    Route::get('face-descriptors', [FaceRecognitionController::class, 'getDescriptors'])->name('guru.face-descriptors');
    Route::post('face-process', [FaceRecognitionController::class, 'processFaceScan'])->name('guru.face-process');
    Route::post('face-save', [FaceRecognitionController::class, 'saveFaceDescriptor'])->name('guru.face-save');

    // Absensi Manual Masuk/Pulang dari Dashboard
    Route::post('absen-manual', [FaceRecognitionController::class, 'absenManual'])->name('guru.absen-manual');

    // Ubah Password Akun Guru
    Route::get('guru/password', [GuruPasswordController::class, 'index'])->name('guru.password');
    Route::put('guru/password', [GuruPasswordController::class, 'update'])->name('guru.password.update');
});
