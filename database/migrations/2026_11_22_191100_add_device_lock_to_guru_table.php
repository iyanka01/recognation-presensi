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
            $table->string('device_id', 120)->nullable()->after('foto_wajah')->index();
            $table->string('device_name', 150)->nullable()->after('device_id');
            $table->timestamp('device_locked_at')->nullable()->after('device_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'device_name', 'device_locked_at']);
        });
    }
};
