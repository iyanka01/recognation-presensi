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
        Schema::create('pengaturan_lembagas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_yayasan')->nullable();
            $table->string('nama_lembaga');
            $table->string('logo')->nullable();
            $table->enum('akreditasi', ['A', 'B', 'C', 'D'])->nullable();
            $table->string('website')->nullable();
            $table->string('nama_kepala');
            $table->string('nip')->nullable();
            $table->string('no_telp')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_lembagas');
    }
};
