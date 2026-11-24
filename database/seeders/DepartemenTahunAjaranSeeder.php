<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use Illuminate\Database\Seeder;

class DepartemenTahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        $lembaga = PengaturanLembaga::first();
        $namaLembaga = $lembaga && $lembaga->nama_lembaga ? $lembaga->nama_lembaga : 'SMK LENTERA';
        $logoLembaga = $lembaga ? $lembaga->logo : null;

        // 1. Tahun Ajaran Aktif Default
        $ta = TahunAjaran::firstOrCreate(
            ['tahun_ajaran' => '2026/2027'],
            [
                'tanggal_mulai' => '2026-07-15',
                'tanggal_akhir' => '2027-06-20',
                'status' => 'Aktif',
            ]
        );

        // 2. Departemen Default (SMK)
        $dep = Departemen::firstOrCreate(
            ['kode_departemen' => 'SMK'],
            [
                'nama_departemen' => $namaLembaga,
                'tingkatan' => 'SMK',
                'logo' => $logoLembaga,
                'status' => 'Aktif',
            ]
        );

        // 3. Tautkan kelas lama yang masih null
        Kelas::whereNull('departemen_id')->update([
            'departemen_id' => $dep->id,
            'tahun_ajaran_id' => $ta->id,
        ]);
    }
}
