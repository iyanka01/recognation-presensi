<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:init {--username=admin : Username untuk akun admin} {--password= : Password akun admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi atau reset akun Administrator dengan aman melalui CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = (string) $this->option('username');
        $password = (string) $this->option('password');

        if (empty($password)) {
            $password = $this->secret('Masukkan kata sandi baru untuk Administrator:');
            if (empty($password)) {
                $this->error('Kata sandi tidak boleh kosong.');
                return self::FAILURE;
            }
        }

        $admin = AdminUser::firstOrNew(['username' => $username]);
        if (!$admin->exists) {
            $admin->id = (string) Str::uuid();
            $admin->nama_lengkap = 'Administrator';
            $admin->role = 'admin';
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info("Akun administrator '{$username}' berhasil diperbarui / dibuat.");
        return self::SUCCESS;
    }
}
