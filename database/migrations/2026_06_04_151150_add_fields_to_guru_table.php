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
        Schema::table('guru', function (Blueprint $table) {
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('tempat_lahir')->nullable()->after('agama');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('jabatan')->nullable()->after('tanggal_lahir');
            $table->string('foto_guru')->nullable()->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['agama', 'tempat_lahir', 'tanggal_lahir', 'jabatan', 'foto_guru']);
        });
    }
};
