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
        if (!Schema::hasTable('pengumuman')) {
            Schema::create('pengumuman', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('judul');
                $table->text('konten');
                $table->string('kategori')->default('Umum'); // Umum, Penting, Akademik, Libur
                $table->string('lampiran')->nullable();
                $table->string('target')->default('semua'); // semua, guru, siswa
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
