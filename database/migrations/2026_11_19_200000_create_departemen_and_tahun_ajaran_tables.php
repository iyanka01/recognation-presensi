<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Departemen / Instansi Unit Sekolah
        Schema::create('departemen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_departemen')->unique(); // No / Kode Departemen
            $table->string('nama_departemen');          // Nama Sekolah / Instansi
            $table->string('tingkatan');                // Tingkatan: SMK, SMA, SMP, SD, dll
            $table->foreignUuid('kepala_sekolah_id')->nullable()->constrained('guru')->nullOnDelete(); // Otomatis dari data guru
            $table->string('logo')->nullable();         // Logo spesifik unit sekolah
            $table->text('alamat')->nullable();         // Alamat unit (opsional)
            $table->string('no_telp')->nullable();      // Telepon unit (opsional)
            $table->string('status')->default('Aktif'); // 'Aktif' | 'Tidak Aktif'
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Tahun Ajaran
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tahun_ajaran');             // Contoh: '2026/2027'
            $table->date('tanggal_mulai');              // Tanggal Mulai
            $table->date('tanggal_akhir');              // Tanggal Akhir
            $table->string('status')->default('Aktif'); // 'Aktif' | 'Tidak Aktif'
            $table->timestamps();
        });

        // 3. Tambahkan foreign key departemen_id dan tahun_ajaran_id ke tabel kelas (aman / nullable agar data lama utuh)
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignUuid('departemen_id')->nullable()->after('id')->constrained('departemen')->nullOnDelete();
            $table->foreignUuid('tahun_ajaran_id')->nullable()->after('departemen_id')->constrained('tahun_ajaran')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['departemen_id']);
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn(['departemen_id', 'tahun_ajaran_id']);
        });

        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('departemen');
    }
};
