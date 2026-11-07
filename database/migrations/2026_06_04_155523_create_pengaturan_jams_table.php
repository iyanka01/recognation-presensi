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
        Schema::create('pengaturan_jams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('tipe', ['guru', 'kelas'])->default('kelas');
            $table->uuid('kelas_id')->nullable(); // null if tipe is 'guru'
            $table->string('hari', 15); // Senin, Selasa, Rabu, dll.
            $table->time('jam_mulai');
            $table->time('jam_terlambat');
            $table->time('jam_pulang');
            $table->timestamps();

            // Unique constraint to prevent duplicate day config for the same class or guru
            // MySQL allows multiple nulls in unique constraints, so it works perfectly.
            $table->unique(['tipe', 'kelas_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_jams');
    }
};
