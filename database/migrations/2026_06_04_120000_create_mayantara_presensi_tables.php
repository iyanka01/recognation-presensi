<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('nama_lengkap');
            $table->string('role')->default('admin');
            $table->timestamps();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip')->unique()->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('status')->default('Aktif');
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tingkat');
            $table->string('nama_rombel');
            $table->foreignUuid('wali_kelas_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nis')->unique();
            $table->string('nisn')->unique()->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('agama');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('no_hp_wali')->nullable();
            $table->string('foto_siswa')->nullable();
            $table->string('status')->default('Aktif');
            $table->foreignUuid('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('admin_users');
    }
};
