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
        if (Schema::hasTable('guru') && !Schema::hasColumn('guru', 'remember_token')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }

        if (Schema::hasTable('admin_users') && !Schema::hasColumn('admin_users', 'remember_token')) {
            Schema::table('admin_users', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('guru') && Schema::hasColumn('guru', 'remember_token')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }

        if (Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'remember_token')) {
            Schema::table('admin_users', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }
    }
};
