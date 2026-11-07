<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('siswa', 'password')) {
                    $table->string('password')->default(Hash::make('siswa123'))->after('foto_siswa');
                }
                if (!Schema::hasColumn('siswa', 'remember_token')) {
                    $table->rememberToken()->after('password');
                }
                if (!Schema::hasColumn('siswa', 'is_password_changed')) {
                    $table->boolean('is_password_changed')->default(false)->after('remember_token');
                }
            });

            // Set all existing students to default password hash if null or empty
            DB::table('siswa')->whereNull('password')->orWhere('password', '')->update([
                'password' => Hash::make('siswa123'),
                'is_password_changed' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('siswa', 'password')) $columns[] = 'password';
                if (Schema::hasColumn('siswa', 'remember_token')) $columns[] = 'remember_token';
                if (Schema::hasColumn('siswa', 'is_password_changed')) $columns[] = 'is_password_changed';
                if (!empty($columns)) $table->dropColumn($columns);
            });
        }
    }
};
