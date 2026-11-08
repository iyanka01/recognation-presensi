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
        Schema::table('presensi', function (Blueprint $table) {
            if (!Schema::hasColumn('presensi', 'is_unlocked')) {
                $table->boolean('is_unlocked')->default(false)->after('keterangan');
            }
            if (!Schema::hasColumn('presensi', 'unlocked_at')) {
                $table->timestamp('unlocked_at')->nullable()->after('is_unlocked');
            }
            if (!Schema::hasColumn('presensi', 'unlocked_by')) {
                $table->string('unlocked_by')->nullable()->after('unlocked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            if (Schema::hasColumn('presensi', 'is_unlocked')) {
                $table->dropColumn(['is_unlocked', 'unlocked_at', 'unlocked_by']);
            }
        });
    }
};
