<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_khusus', function (Blueprint $table) {
            $table->string('tipe_kegiatan')->default('sekali')->after('nama_kegiatan'); // 'sekali' atau 'rutin'
            $table->json('hari')->nullable()->after('tipe_kegiatan'); // e.g. ["Senin", "Selasa", "Rabu"]
            $table->date('tanggal')->nullable()->change(); // jika rutin, tanggal bisa nullable / default
        });

        // Pada presensi_kegiatan_khusus, tambahkan kolom tanggal_presensi dan perbarui unique index
        Schema::table('presensi_kegiatan_khusus', function (Blueprint $table) {
            $table->date('tanggal_presensi')->nullable()->after('user_id');
        });

        // Isi default tanggal_presensi dari jam_masuk yang sudah ada
        \DB::table('presensi_kegiatan_khusus')->whereNull('tanggal_presensi')->update([
            'tanggal_presensi' => \DB::raw('DATE(jam_masuk)')
        ]);

        Schema::table('presensi_kegiatan_khusus', function (Blueprint $table) {
            // Drop unique index lama [kegiatan_khusus_id, user_type, user_id]
            // agar kegiatan rutin bisa diabsen di tanggal/hari berbeda
            $table->dropUnique('presensi_kegiatan_unique');
            $table->unique(['kegiatan_khusus_id', 'user_type', 'user_id', 'tanggal_presensi'], 'presensi_kegiatan_harian_unique');
        });
    }

    public function down(): void
    {
        Schema::table('presensi_kegiatan_khusus', function (Blueprint $table) {
            $table->dropUnique('presensi_kegiatan_harian_unique');
            $table->unique(['kegiatan_khusus_id', 'user_type', 'user_id'], 'presensi_kegiatan_unique');
            $table->dropColumn('tanggal_presensi');
        });

        Schema::table('kegiatan_khusus', function (Blueprint $table) {
            $table->dropColumn(['tipe_kegiatan', 'hari']);
        });
    }
};
