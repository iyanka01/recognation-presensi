<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_khusus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_kegiatan')->unique();
            $table->string('nama_kegiatan');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('mode_absen')->default('Datang Saja'); // 'Datang Saja' | 'Datang & Pulang'
            $table->string('sasaran_peserta')->default('Semua Siswa'); // 'Semua Siswa' | 'Pilih Kelas' | 'Khusus Guru' | 'Semua'
            $table->json('kelas_ids')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Aktif'); // 'Aktif' | 'Selesai'
            $table->timestamps();
        });

        Schema::create('presensi_kegiatan_khusus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kegiatan_khusus_id')->constrained('kegiatan_khusus')->cascadeOnDelete();
            $table->enum('user_type', ['siswa', 'guru'])->default('siswa');
            $table->uuid('user_id');
            $table->dateTime('jam_masuk');
            $table->dateTime('jam_pulang')->nullable();
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
            $table->string('metode')->default('QR Scan');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['kegiatan_khusus_id', 'user_type', 'user_id'], 'presensi_kegiatan_unique');
            $table->index(['kegiatan_khusus_id', 'tanggal_masuk' => 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_kegiatan_khusus');
        Schema::dropIfExists('kegiatan_khusus');
    }
};
