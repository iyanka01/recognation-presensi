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
            $table->string('telegram_bot_token')->nullable()->after('logo');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('no_hp_wali');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_lembagas', function (Blueprint $table) {
            $table->dropColumn('telegram_bot_token');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
        });
    }
};
