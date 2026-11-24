<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_lembagas', function (Blueprint $table) {
            $table->integer('batas_absen_guru_hari')->default(14)->after('wanesia_active');
            $table->boolean('notif_peringatan_guru_active')->default(true)->after('batas_absen_guru_hari');
            $table->text('template_peringatan_guru')->nullable()->after('notif_peringatan_guru_active');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_lembagas', function (Blueprint $table) {
            $table->dropColumn([
                'batas_absen_guru_hari',
                'notif_peringatan_guru_active',
                'template_peringatan_guru'
            ]);
        });
    }
};
