<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_mapel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'siswa_id', 'tanggal'], 'presensi_mapel_unique');
            $table->index(['tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_mapel');
    }
};
