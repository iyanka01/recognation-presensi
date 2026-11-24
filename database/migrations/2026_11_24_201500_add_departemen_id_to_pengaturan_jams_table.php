<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_jams', function (Blueprint $table) {
            $table->uuid('departemen_id')->nullable()->after('tipe');
        });

        // Ubah constraint unique agar support departemen_id
        Schema::table('pengaturan_jams', function (Blueprint $table) {
            $table->dropUnique(['tipe', 'kelas_id', 'hari']);
            $table->unique(['tipe', 'departemen_id', 'kelas_id', 'hari'], 'pengaturan_jams_target_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_jams', function (Blueprint $table) {
            $table->dropUnique('pengaturan_jams_target_unique');
            $table->unique(['tipe', 'kelas_id', 'hari']);
            $table->dropColumn('departemen_id');
        });
    }
};
