<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_mengajar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jumlah_jam')->default(1);
            $table->string('materi', 500);
            $table->string('materi_selanjutnya', 500)->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->timestamps();

            // Satu jurnal per jadwal per tanggal
            $table->unique(['jadwal_id', 'tanggal'], 'jurnal_mengajar_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_mengajar');
    }
};
