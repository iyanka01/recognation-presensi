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
        Schema::create('peringatan_gurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->integer('jumlah_hari_alpa');
            $table->date('tanggal_kirim');
            $table->string('no_hp');
            $table->text('pesan');
            $table->string('status_kirim')->default('Terkirim'); // Terkirim / Gagal
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peringatan_gurus');
    }
};
