<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_khusus', function (Blueprint $table) {
            $table->boolean('kirim_notifikasi')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_khusus', function (Blueprint $table) {
            $table->dropColumn('kirim_notifikasi');
        });
    }
};
