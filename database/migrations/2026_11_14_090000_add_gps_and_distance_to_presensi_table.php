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
            if (!Schema::hasColumn('presensi', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('status');
            }
            if (!Schema::hasColumn('presensi', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('presensi', 'distance_meter')) {
                $table->integer('distance_meter')->nullable()->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('presensi', 'latitude')) $cols[] = 'latitude';
            if (Schema::hasColumn('presensi', 'longitude')) $cols[] = 'longitude';
            if (Schema::hasColumn('presensi', 'distance_meter')) $cols[] = 'distance_meter';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
