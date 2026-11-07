<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Pastikan akun default Admin selalu ada di database
        $admin = AdminUser::firstOrNew(['username' => 'admin']);
        if (!$admin->exists) {
            $admin->id = (string) Str::uuid();
            $admin->nama_lengkap = 'Administrator';
            $admin->role = 'admin';
        }
        $admin->password = Hash::make('admin123');
        $admin->save();
    }
}
