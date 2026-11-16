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
        Schema::table('pengaturan_lembagas', function (Blueprint $table) {
            $table->uuid('kepala_sekolah_id')->nullable()->after('akreditasi');
            $table->string('wanesia_token')->nullable()->after('telegram_bot_token');
            $table->boolean('wanesia_active')->default(true)->after('wanesia_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_lembagas', function (Blueprint $table) {
            $table->dropColumn(['kepala_sekolah_id', 'wanesia_token', 'wanesia_active']);
        });
    }
};
