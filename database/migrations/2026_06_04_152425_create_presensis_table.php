<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('user_type', ['siswa', 'guru']);
            $table->uuid('user_id'); // We don't use foreign key constraints because it can point to two different tables
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Create index for faster querying
            $table->index(['user_type', 'user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
